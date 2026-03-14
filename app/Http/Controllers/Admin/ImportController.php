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
            'section' => 'required|in:products,users,clients,discounts,categories', // تم إضافة قسم الأقسام
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

        return view('admin.imports.preview', [
            'headers' => $rows[0] ?? [],
            'rows' => array_slice($rows, 1, 5),
            'section' => $request->section,
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
                            continue 2;
                        }

                        // إذا اسم القسم موجود، نحاول نربطه بـ category_id
                        $categoryId = null;
                        if (!empty($data['category_name'])) {
                            $category = Category::where('name_ar', $data['category_name'])->first();
                            if ($category) {
                                $categoryId = $category->id;
                            }
                        }

                        Product::create([
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
                            $parentCategory = Category::where('name_ar', $data['parent_name'])->first();
                            if ($parentCategory) {
                                $parentId = $parentCategory->id;
                            }
                        }

                        Category::create([
                            'name_ar' => $data['name_ar'],
                            'slug' => Str::slug($data['name_ar']),
                            'parent_id' => $parentId,
                        ]);
                        $count++;
                    } else {
                        $skipped++;
                    }
                    break;
                // --- نهاية المنطق الجديد ---

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
}