<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BarcodeController extends Controller
{
    /* ===================== إدارة (لوحة تحكم) ===================== */

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);

        $query = Barcode::query()->latest('id');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('target_url', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active')   $query->where('is_active', 1);
            if ($request->status === 'inactive') $query->where('is_active', 0);
        }

        $barcodes = $query->paginate($perPage)->withQueryString();

        return view('admin.barcodes.index', compact('barcodes'));
    }

    public function create()
    {
        return view('admin.barcodes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'       => ['required', 'string', 'max:64', 'unique:barcodes,code'],
            'title'      => ['nullable', 'string', 'max:255'],
            'target_url' => ['required', 'url', 'max:2048'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $data['code'] = strtoupper(preg_replace('/[^A-Z0-9\-\._]/i', '', $data['code']));
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        Barcode::create($data);

        return redirect()
            ->route('admin.barcodes.index')
            ->with('success', 'تم إنشاء الكود بنجاح.');
    }

    public function edit(Barcode $barcode)
    {
        return view('admin.barcodes.edit', compact('barcode'));
    }

    public function update(Request $request, Barcode $barcode)
    {
        $data = $request->validate([
            'code'       => ['required', 'string', 'max:64', 'unique:barcodes,code,' . $barcode->id],
            'title'      => ['nullable', 'string', 'max:255'],
            'target_url' => ['required', 'url', 'max:2048'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $data['code'] = strtoupper(preg_replace('/[^A-Z0-9\-\._]/i', '', $data['code']));
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $barcode->update($data);

        return redirect()
            ->route('admin.barcodes.index')
            ->with('success', 'تم تحديث الكود بنجاح.');
    }

    public function destroy(Barcode $barcode)
    {
        $barcode->delete();

        return redirect()
            ->route('admin.barcodes.index')
            ->with('success', 'تم حذف الكود.');
    }

    public function toggle(Barcode $barcode)
    {
        $barcode->is_active = !$barcode->is_active;
        $barcode->save();

        return redirect()
            ->route('admin.barcodes.index')
            ->with('success', 'تم تحديث حالة الكود.');
    }

    /* ===================== عام (Public) ===================== */

    /**
     * تحويل /b/{code} إلى target_url + تسجيل الزيارة.
     */
    public function go(Request $request, string $code)
    {
        $barcode = Barcode::where('code', $code)->firstOrFail();

        if (!$barcode->is_active) {
            abort(410, 'هذا الكود غير مُفعل حالياً.');
        }

        DB::table('barcodes')
            ->where('id', $barcode->id)
            ->update([
                'hits'            => DB::raw('hits + 1'),
                'last_hit_at'     => now(),
                'last_ip'         => $request->ip(),
                'last_user_agent' => Str::limit((string) $request->userAgent(), 255),
            ]);

        return redirect()->away($barcode->target_url);
    }

    /**
     * إخراج صورة QR: /b/{code}.png
     */
    public function qr(Request $request, string $code)
    {
        $barcode = Barcode::where('code', $code)->firstOrFail();

        $text = $this->publicUrl($barcode->code, $request);
        $size = (int) $request->query('s', 400);
        $size = max(64, min($size, 1000));

        try {
            if (class_exists(\SimpleSoftwareIO\QrCode\Generator::class)) {
                $qr = new \SimpleSoftwareIO\QrCode\Generator;
                $png = $qr->format('png')->size($size)->margin(1)->errorCorrection('M')->generate($text);

                return response($png, 200)
                    ->header('Content-Type', 'image/png')
                    ->header('Cache-Control', 'public, max-age=2592000');
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        $external = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
            'data'   => $text,
            'size'   => "{$size}x{$size}",
            'format' => 'png',
            'qzone'  => 1,
        ]);

        return redirect()->away($external, 302);
    }

    private function publicUrl(string $code, Request $request): string
    {
        try {
            return route('barcode.go', ['code' => $code], true);
        } catch (\Throwable $e) {
            return url('/b/' . $code);
        }
    }
}
