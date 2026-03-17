<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Product;
use App\Models\User;
use App\Models\Customer;
use App\Models\DiscountCode;
use App\Models\Category;
use App\Models\PrimaryCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.imports.index');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'section' => 'required|in:products,users,clients,discounts,categories,brands', // تم إضافة قسم الأقسام والبراندات
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        $filename = uniqid() . '.' . $request->file('file')->getClientOriginalExtension();
        $relativePath = 'temp/' . $filename;
        $fullPath = storage_path('app/' . $relativePath);

        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        // move uploaded file to temp folder
        $request->file('file')->move(storage_path('app/temp'), $filename);

        // load the spreadsheet file
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $sectionLabels = [
            'products' => 'منتجات',
            'categories' => 'براندات',
            'brands' => 'فئات',
            'users' => 'مستخدمين',
            'clients' => 'عملاء',
            'discounts' => 'أكواد خصم',
        ];

        return view('admin.imports.preview', [
            'headers' => $rows[0] ?? [],
            'rows' => array_slice($rows, 1),
            'section' => $request->section,
            'sectionLabel' => $sectionLabels[$request->section] ?? $request->section,
            'path' => $relativePath,
        ]);
    }

    public function import(Request $request)
    {
        $map = $request->input('map');
        $section = $request->input('section');
        $path = $request->input('path');

        $spreadsheet = IOFactory::load(storage_path("app/{$path}"));
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if ($request->has('ignore_header')) {
            array_shift($rows);
        }

        $count = 0;
        $skipped = 0;
        $duplicates = [];

        foreach ($rows as $row) {
            $data = [];
            foreach ($map as $index => $field) {
                if ($field !== 'ignore') {
                    $data[$field] = $row[$index] ?? null;
                }
            }

            switch ($section) {
                case 'products':
                    if (!empty($data['name_ar'])) {
                        if (!empty($data['sku']) && Product::where('sku', $data['sku'])->exists()) {
                            $duplicates[] = $data['sku'];
                            $skipped++;
                            continue 2; // <== هنا التعديل الصحيح
                        }

                        // إذا اسم القسم موجود، نحاول نربطه بـ category_id
                        $categoryId = null;
                        if (!empty($data['category_name'])) {
                            $category = Category::where('name_ar', $data['category_name'])->first();
                            if ($category) {
                                $categoryId = $category->id;
                            }
                        }

                        $product = Product::create([
                            'name_ar'        => $data['name_ar'],
                            'name_en'        => $data['name_en'] ?? $data['name_ar'],
                            'name_ku'        => $data['name_ku'] ?? $data['name_ar'],
                            'sku'            => $data['sku'] ?? null,
                            'price'          => $data['price'] ?? 0,
                            'description_ar' => $data['description_ar'] ?? $data['description'] ?? '',
                            'description_en' => $data['description_en'] ?? $data['description'] ?? '',
                            'description_ku' => $data['description_ku'] ?? $data['description'] ?? '',
                            'stock_quantity' => $data['stock_quantity'] ?? 0,
                            'category_id'    => $categoryId
                        ]);

                        // ربط البراندات (Primary Categories) إذا تم تمرير اسم/أسماء براندات.
                        $brandNamesRaw = $data['brand_name'] ?? $data['brand_names'] ?? null;
                        if (!empty($brandNamesRaw)) {
                            $brandNames = preg_split('/[,،|]+/u', (string) $brandNamesRaw);
                            $brandNames = array_values(array_filter(array_map(static fn($v) => trim((string) $v), $brandNames)));

                            if (!empty($brandNames)) {
                                $brandIds = PrimaryCategory::query()
                                    ->where(function ($q) use ($brandNames) {
                                        foreach ($brandNames as $brandName) {
                                            $q->orWhere('name_ar', $brandName)
                                              ->orWhere('name_en', $brandName)
                                              ->orWhere('slug', Str::slug($brandName));
                                        }
                                    })
                                    ->pluck('id')
                                    ->all();

                                if (!empty($brandIds)) {
                                    $product->primaryCategories()->syncWithoutDetaching($brandIds);
                                }
                            }
                        }

                        $count++;
                    } else {
                        $skipped++;
                    }
                    break;

                // --- بداية المنطق الجديد لاستيراد الأقسام ---
                case 'categories':
                    if (!empty($data['name_ar'])) {
                        // التحقق من عدم تكرار اسم القسم
                        if (Category::where('name_ar', $data['name_ar'])->exists()) {
                            $duplicates[] = $data['name_ar'];
                            $skipped++;
                            continue 2;
                        }

                        $parentId = null;
                        // البحث عن معرّف القسم الأب إذا تم توفير اسمه
                        if (!empty($data['parent_name'])) {
                            $parentCategory = Category::query()
                                ->where('name_ar', $data['parent_name'])
                                ->orWhere('name_en', $data['parent_name'])
                                ->first();
                            if ($parentCategory) {
                                $parentId = $parentCategory->id;
                            }
                        }

                        Category::create([
                            'name_ar' => $data['name_ar'],
                            'name_en' => $data['name_en'] ?? null,
                            'slug' => Str::slug($data['name_ar']),
                            'parent_id' => $parentId,
                        ]);
                        $count++;
                    } else {
                        $skipped++;
                    }
                    break;
                // --- نهاية المنطق الجديد ---

                case 'brands':
                    if (!empty($data['name_ar'])) {
                        // تجنب التكرار بالاسم العربي (وممكن يمر slug مباشر بنفس القيمة)
                        if (PrimaryCategory::where('name_ar', $data['name_ar'])->exists()) {
                            $duplicates[] = $data['name_ar'];
                            $skipped++;
                            continue 2;
                        }

                        $parentId = null;
                        if (!empty($data['parent_name'])) {
                            $parent = PrimaryCategory::query()
                                ->where('name_ar', $data['parent_name'])
                                ->orWhere('name_en', $data['parent_name'])
                                ->orWhere('slug', Str::slug($data['parent_name']))
                                ->first();

                            if ($parent) {
                                $parentId = $parent->id;
                            }
                        }

                        PrimaryCategory::create([
                            'name_ar'   => $data['name_ar'],
                            'name_en'   => $data['name_en'] ?? null,
                            'slug'      => !empty($data['slug']) ? Str::slug((string) $data['slug']) : Str::slug((string) $data['name_ar']),
                            'parent_id' => $parentId,
                            'is_active' => true,
                        ]);

                        $count++;
                    } else {
                        $skipped++;
                    }
                    break;

                case 'users':
                    if (!empty($data['email']) && !empty($data['name'])) {
                        $data['password'] = bcrypt($data['password'] ?? '123456');
                        User::create($data);
                        $count++;
                    } else {
                        $skipped++;
                    }
                    break;

                case 'clients':
                    if (!empty($data['name'])) {
                        Customer::create($data);
                        $count++;
                    } else {
                        $skipped++;
                    }
                    break;

                case 'discounts':
                    if (!empty($data['code'])) {
                        DiscountCode::create($data);
                        $count++;
                    } else {
                        $skipped++;
                    }
                    break;
            }
        }

        return redirect()->route('admin.imports.index')->with([
            'success' => "✅ تم استيراد {$count} صف، وتجاهل {$skipped} صف ناقص.",
            'duplicates' => $duplicates
        ]);
    }

    public function importQuantityForm()
    {
        return view('admin.imports.import_quantity');
    }

    public function importQuantity(Request $request)
    {
        \Log::info('importQuantity: started', $request->all());
        // الخطوة 1: إذا كان الطلب يحتوي على ملف، اعرض المعاينة لتحديد الأعمدة
        if ($request->hasFile('file')) {
            \Log::info('importQuantity: hasFile', [$request->file('file')->getClientOriginalName()]);
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls'
            ]);
            try {
                \Log::info('importQuantity: before load spreadsheet');
                $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
                $headers = $rows[0] ?? [];
                $previewRows = array_slice($rows, 1);

                // حفظ الملف مؤقتاً
                $filename = uniqid() . '.' . $request->file('file')->getClientOriginalExtension();
                $relativePath = 'temp/' . $filename;
                $fullPath = storage_path('app/' . $relativePath);
                if (!\Storage::exists('temp')) {
                    \Storage::makeDirectory('temp');
                }
                \Log::info('importQuantity: before move file', [$fullPath]);
                $request->file('file')->move(storage_path('app/temp'), $filename);

                \Log::info('importQuantity: show preview', ['headers' => $headers, 'rows' => $previewRows]);
                return view('admin.imports.import_quantity_preview', [
                    'headers' => $headers,
                    'rows' => $previewRows,
                    'path' => $relativePath,
                ]);
            } catch (\Exception $e) {
                \Log::error('importQuantity: error in preview', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return redirect()->back()->with('error', 'تعذر قراءة الملف: ' . $e->getMessage());
            }
        }

        // الخطوة 2: معالجة الملف بعد تحديد الأعمدة
        \Log::info('importQuantity: step2', $request->all());
        $request->validate([
            'path' => 'required',
            'sku_col' => 'required|integer',
            'qty_col' => 'required|integer',
        ]);
        try {
            \Log::info('importQuantity: before load spreadsheet step2', [$request->input('path')]);
            $spreadsheet = IOFactory::load(storage_path('app/' . $request->input('path')));
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            array_shift($rows); // إزالة الهيدر

            $skuCol = (int)$request->input('sku_col');
            $qtyCol = (int)$request->input('qty_col');

            \Log::info('importQuantity: start update loop', ['skuCol' => $skuCol, 'qtyCol' => $qtyCol, 'rowsCount' => count($rows)]);
            $updated = 0;
            $notFoundCount = 0;
            $notFoundSkus = [];

            foreach ($rows as $row) {
                $sku = trim($row[$skuCol] ?? '');
                $quantity = $row[$qtyCol] ?? null;

                if ($sku !== '' && is_numeric($quantity)) {
                    $product = Product::where('sku', $sku)->first();
                    if ($product) {
                        $product->update(['stock_quantity' => (int)$quantity]);
                        $updated++;
                    } else {
                        $notFoundCount++;
                        $notFoundSkus[] = $sku;
                    }
                }
            }

            \Log::info('importQuantity: finished update', ['updated' => $updated, 'notFoundCount' => $notFoundCount]);
            if ($updated === 0 && $notFoundCount === 0) {
                return redirect()->back()->with('error', 'لم يتم العثور على بيانات صالحة في الملف. تأكد من تحديد الأعمدة بشكل صحيح.');
            }

            $msg = "✅ تم تحديث كمية {$updated} منتج بنجاح. (تجاهل {$notFoundCount} منتج غير موجود)";
            if ($notFoundCount > 0) {
                $msg .= "<br>الأكواد غير الموجودة: " . implode(', ', $notFoundSkus);
            }
            \Log::info('importQuantity: success', ['msg' => $msg]);
            return redirect()->route('admin.products.import_quantity')->with('success', $msg);
        } catch (\Exception $e) {
            \Log::error('importQuantity: error in update', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'حدث خطأ أثناء معالجة الملف: ' . $e->getMessage());
        }
    }
}