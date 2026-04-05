<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Services\ReviewModerationService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductReviewController extends Controller
{
    public function __construct(private ReviewModerationService $moderationService)
    {
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $moderation = $this->moderationService->moderate($validated['comment'] ?? null);

        try {
            $review = $this->upsertReview($product->id, (int) Auth::id(), $validated, $moderation);
        } catch (QueryException $e) {
            if (! $this->isMissingDefaultIdError($e)) {
                throw $e;
            }

            $this->repairProductReviewsIdColumn();
            $review = $this->upsertReview($product->id, (int) Auth::id(), $validated, $moderation);
        }

        // احسب واحفظ المتوسط/العدد في المنتج إذا الأعمدة موجودة
        $avg   = round($product->reviews()->where('status','approved')->avg('rating') ?? 0, 2);
        $count = (int) $product->reviews()->where('status','approved')->count();

        if (Schema::hasColumn('products', 'average_rating') && Schema::hasColumn('products', 'reviews_count')) {
            $product->forceFill([
                'average_rating' => $avg,
                'reviews_count'  => $count,
            ])->save();
        }

        $status = (string) ($review->status ?? 'approved');
        $isVisible = $status === 'approved';

        $message = 'تم حفظ تقييمك بنجاح.';
        if ($status === 'pending') {
            $message = 'تم استلام تعليقك وسيتم مراجعته من الإدارة قبل النشر.';
        } elseif ($status === 'rejected') {
            $message = 'لم يتم نشر تعليقك تلقائيًا بسبب مخالفات في المحتوى.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'review'  => [
                'id'               => $review->id,
                'user_name'        => $review->user->name ?? 'أنتِ',
                'rating'           => (int) $review->rating,
                'comment'          => $review->comment,
                'created_at_human' => $review->created_at->diffForHumans(),
                'status'           => $status,
                'visible'          => $isVisible,
            ],
            'stats' => [
                'avg'   => $avg,
                'count' => $count,
            ],
        ]);
    }

    private function upsertReview(int $productId, int $userId, array $validated, array $moderation): ProductReview
    {
        $payload = [
            'rating'     => $validated['rating'],
            'comment'    => $validated['comment'] ?? null,
            'status'     => $moderation['status'] ?? 'approved',
        ];

        if (Schema::hasColumn('product_reviews', 'moderation_score')) {
            $payload['moderation_score'] = (int) ($moderation['score'] ?? 0);
        }

        if (Schema::hasColumn('product_reviews', 'moderation_flags')) {
            $payload['moderation_flags'] = $moderation['flags'] ?? [];
        }

        return ProductReview::updateOrCreate(
            [
                'product_id' => $productId,
                'user_id'    => $userId,
            ],
            $payload
        );
    }

    private function isMissingDefaultIdError(QueryException $e): bool
    {
        $errorInfo = $e->errorInfo;
        $code = (int) ($errorInfo[1] ?? 0);
        $message = strtolower((string) ($errorInfo[2] ?? $e->getMessage()));

        return $code === 1364
            && str_contains($message, 'field')
            && str_contains($message, "id")
            && str_contains($message, 'default value');
    }

    private function repairProductReviewsIdColumn(): void
    {
        if (! Schema::hasTable('product_reviews') || ! Schema::hasColumn('product_reviews', 'id')) {
            return;
        }

        $databaseName = DB::getDatabaseName();

        $idColumn = DB::selectOne(
            'SELECT COLUMN_KEY, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$databaseName, 'product_reviews', 'id']
        );

        if (! $idColumn) {
            return;
        }

        if (($idColumn->COLUMN_KEY ?? '') !== 'PRI') {
            $primaryKey = DB::selectOne(
                'SELECT COUNT(*) AS aggregate FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = ?',
                [$databaseName, 'product_reviews', 'PRIMARY KEY']
            );

            if ((int) ($primaryKey->aggregate ?? 0) === 0) {
                DB::statement('ALTER TABLE `product_reviews` ADD PRIMARY KEY (`id`)');
            }
        }

        $extra = strtolower((string) ($idColumn->EXTRA ?? ''));
        if (! str_contains($extra, 'auto_increment')) {
            $nextId = ((int) DB::table('product_reviews')->max('id')) + 1;
            DB::statement('ALTER TABLE `product_reviews` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            DB::statement('ALTER TABLE `product_reviews` AUTO_INCREMENT = ' . max($nextId, 1));
        }
    }

    public function destroy(Product $product, ProductReview $review)
    {
        // صاحب التعليق أو أدمن
        if ($review->user_id !== Auth::id() && !Auth::user()?->can('delete-reviews') &&
            !(method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('super-admin'))) {
            abort(403);
        }

        if ($review->product_id !== $product->id) {
            abort(404);
        }

        $review->delete();

        // أعِد حساب المتوسط/العدد
        $avg   = round($product->reviews()->where('status','approved')->avg('rating') ?? 0, 2);
        $count = (int) $product->reviews()->where('status','approved')->count();

        if (Schema::hasColumn('products', 'average_rating') && Schema::hasColumn('products', 'reviews_count')) {
            $product->forceFill([
                'average_rating' => $avg,
                'reviews_count'  => $count,
            ])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حذف تعليقك.',
            'stats'   => ['avg' => $avg, 'count' => $count],
        ]);
    }
}
