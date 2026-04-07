<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class EngagementController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);
        $blogData = $this->loadBlogPosts();

        return response()->json([
            'authenticated' => (bool) $user,
            'current_user' => $user ? $this->mapUser($user) : null,
            'wishlist_count' => $user ? $this->wishlistCount($user) : 0,
            'blog_posts' => $blogData['posts'],
            'blog_message' => $this->buildBlogMessage($blogData),
            'contact_history' => $user ? $this->loadContactHistory($user) : [],
        ]);
    }

    public function reviews(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (!$user) {
            return response()->json([
                'message' => 'Vui long dang nhap de xem san pham da mua.',
            ], 401);
        }

        return response()->json([
            'current_user' => $this->mapUser($user),
            'wishlist_count' => $this->wishlistCount($user),
            'purchased_products' => $this->loadPurchasedProducts($user),
        ]);
    }

    public function wishlist(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (!$user) {
            return response()->json([
                'message' => 'Vui long dang nhap de xem wishlist.',
            ], 401);
        }

        return response()->json([
            'current_user' => $this->mapUser($user),
            'wishlist_count' => $this->wishlistCount($user),
            'wishlist_items' => $this->loadWishlistProducts($user),
        ]);
    }

    public function blogPost(Request $request, int $id): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);
        $post = $this->findBlogPostById($id);

        if (!$post) {
            return response()->json([
                'message' => 'Khong tim thay bai viet.',
            ], 404);
        }

        return response()->json([
            'authenticated' => (bool) $user,
            'current_user' => $user ? $this->mapUser($user) : null,
            'wishlist_count' => $user ? $this->wishlistCount($user) : 0,
            'post' => $post,
            'recent_posts' => $this->loadBlogPosts($id)['posts'],
        ]);
    }

    public function storeReview(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (!$user) {
            return response()->json([
                'message' => 'Vui long dang nhap truoc khi gui danh gia.',
            ], 401);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!$this->hasPurchasedProduct($user, (int) $validated['product_id'])) {
            return response()->json([
                'message' => 'Ban chi co the danh gia san pham da mua.',
            ], 422);
        }

        $product = Product::query()->find($validated['product_id']);
        if (!$product) {
            return response()->json([
                'message' => 'Khong tim thay san pham.',
            ], 404);
        }

        Review::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $product->id,
            ],
            [
                'rating' => (int) $validated['rating'],
                'comment' => trim((string) ($validated['comment'] ?? '')) ?: null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Gui danh gia thanh cong.',
        ]);
    }

    public function storeWishlist(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (!$user) {
            return response()->json([
                'message' => 'Vui long dang nhap truoc khi luu wishlist.',
            ], 401);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        $product = Product::query()->find($validated['product_id']);
        if (!$product) {
            return response()->json([
                'message' => 'Khong tim thay san pham.',
            ], 404);
        }

        $wishlist = Wishlist::query()->firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => $wishlist->wasRecentlyCreated ? 'Da them san pham vao wishlist.' : 'San pham da co trong wishlist.',
            'wishlist_count' => $this->wishlistCount($user),
        ]);
    }

    public function storeContact(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (!$user) {
            return response()->json([
                'message' => 'Vui long dang nhap truoc khi gui lien he.',
            ], 401);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1500'],
        ]);

        Contact::query()->create([
            'full_name' => $user->name,
            'phone_number' => $user->phone_number,
            'email' => $user->email,
            'message' => trim((string) $validated['message']),
            'is_replied' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gui lien he thanh cong.',
        ]);
    }

    protected function resolveCustomerFromRequest(Request $request): ?User
    {
        $email = (string) ($request->query('email', $request->input('email')) ?? '');

        return $this->resolveCustomerByEmail($email);
    }

    protected function resolveCustomerByEmail(string $email): ?User
    {
        $normalizedEmail = trim($email);
        if ($normalizedEmail === '') {
            return null;
        }

        $user = User::query()
            ->with('role')
            ->where('email', $normalizedEmail)
            ->where('status', 'active')
            ->first();

        if (!$user || !$user->role || strtolower((string) $user->role->name) !== 'customer') {
            return null;
        }

        return $user;
    }

    protected function mapUser(User $user): array
    {
        return [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'phone_number' => (string) ($user->phone_number ?? ''),
            'address' => (string) ($user->address ?? ''),
            'avatar' => $user->avatar_url,
        ];
    }

    protected function hasPurchasedProduct(User $user, int $productId): bool
    {
        return OrderItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->whereHas('order', function ($query) {
                $query->whereIn('status', ['completed', 'hoan thanh']);
            })
            ->exists();
    }

    protected function wishlistCount(User $user): int
    {
        return (int) Wishlist::query()->where('user_id', $user->id)->count();
    }

    protected function loadContactHistory(User $user): array
    {
        return Contact::query()
            ->where('email', $user->email)
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(function (Contact $contact) {
                return [
                    'id' => (int) $contact->id,
                    'full_name' => (string) $contact->full_name,
                    'message' => (string) $contact->message,
                    'is_replied' => (bool) $contact->is_replied,
                    'created_at' => optional($contact->created_at)?->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    protected function loadPurchasedProducts(User $user): array
    {
        $purchaseStats = OrderItem::query()
            ->selectRaw('product_id, SUM(quantity) as purchased_quantity, MAX(created_at) as last_purchased_at')
            ->where('user_id', $user->id)
            ->whereNotNull('product_id')
            ->whereHas('order', function ($query) {
                $query->whereIn('status', ['completed', 'hoan thanh']);
            })
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $productIds = $purchaseStats->keys()->filter()->map(fn ($id) => (int) $id)->values();
        if ($productIds->isEmpty()) {
            return [];
        }

        $myReviews = Review::query()
            ->where('user_id', $user->id)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        $commentsByProduct = Review::query()
            ->with('user:id,name')
            ->whereIn('product_id', $productIds)
            ->latest('id')
            ->get()
            ->groupBy('product_id');

        $wishlistProductIds = Wishlist::query()
            ->where('user_id', $user->id)
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return Product::query()
            ->with(['category:id,name,slug', 'images:id,product_id,image'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->whereIn('id', $productIds)
            ->get()
            ->map(function (Product $product) use ($purchaseStats, $myReviews, $commentsByProduct, $wishlistProductIds) {
                $stat = $purchaseStats->get($product->id);
                $myReview = $myReviews->get($product->id);
                $comments = ($commentsByProduct->get($product->id) ?? collect())
                    ->take(6)
                    ->map(function (Review $review) {
                        return [
                            'id' => (int) $review->id,
                            'user_name' => (string) optional($review->user)->name,
                            'rating' => (int) $review->rating,
                            'comment' => (string) ($review->comment ?? ''),
                            'created_at' => optional($review->created_at)?->toIso8601String(),
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'id' => (int) $product->id,
                    'name' => (string) $product->name,
                    'slug' => (string) $product->slug,
                    'description' => Str::limit(strip_tags((string) $product->description), 140),
                    'price' => (float) $product->price,
                    'category_name' => (string) optional($product->category)->name,
                    'image' => $this->extractPrimaryImage($product),
                    'purchased_quantity' => (int) ($stat->purchased_quantity ?? 0),
                    'last_purchased_at' => $stat->last_purchased_at,
                    'avg_rating' => round((float) ($product->reviews_avg_rating ?? 0), 1),
                    'review_count' => (int) ($product->reviews_count ?? 0),
                    'already_in_wishlist' => in_array((int) $product->id, $wishlistProductIds, true),
                    'my_review' => $myReview ? [
                        'id' => (int) $myReview->id,
                        'rating' => (int) $myReview->rating,
                        'comment' => (string) ($myReview->comment ?? ''),
                        'updated_at' => optional($myReview->updated_at)?->toIso8601String(),
                    ] : null,
                    'comments' => $comments,
                ];
            })
            ->sortByDesc('last_purchased_at')
            ->values()
            ->all();
    }

    protected function loadWishlistProducts(User $user): array
    {
        $wishlistRows = Wishlist::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();

        $productIds = $wishlistRows->pluck('product_id')->map(fn ($id) => (int) $id)->values();
        if ($productIds->isEmpty()) {
            return [];
        }

        $wishlistedAt = $wishlistRows->keyBy('product_id');

        return Product::query()
            ->with(['category:id,name,slug', 'images:id,product_id,image'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->whereIn('id', $productIds)
            ->get()
            ->map(function (Product $product) use ($wishlistedAt) {
                $wishlistItem = $wishlistedAt->get($product->id);

                return [
                    'id' => (int) $product->id,
                    'name' => (string) $product->name,
                    'slug' => (string) $product->slug,
                    'description' => Str::limit(strip_tags((string) $product->description), 140),
                    'price' => (float) $product->price,
                    'category_name' => (string) optional($product->category)->name,
                    'image' => $this->extractPrimaryImage($product),
                    'avg_rating' => round((float) ($product->reviews_avg_rating ?? 0), 1),
                    'review_count' => (int) ($product->reviews_count ?? 0),
                    'wishlisted_at' => optional($wishlistItem?->created_at)?->toIso8601String(),
                ];
            })
            ->sortByDesc('wishlisted_at')
            ->values()
            ->all();
    }

    protected function loadBlogPosts(?int $excludeId = null): array
    {
        $candidates = ['blog_posts', 'blogs', 'posts', 'articles', 'news', 'tin_tuc', 'tin_tucs'];
        $accents = ['sun', 'sky', 'mint'];

        foreach ($candidates as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            try {
                $columns = Schema::getColumnListing($table);
                $idColumn = $this->firstExistingColumn($columns, ['id']);
                $titleColumn = $this->firstExistingColumn($columns, ['title', 'name']);

                if (!$idColumn || !$titleColumn) {
                    continue;
                }

                $summaryColumn = $this->firstExistingColumn($columns, ['summary', 'excerpt', 'description', 'content', 'body']) ?: $titleColumn;
                $contentColumn = $this->firstExistingColumn($columns, ['content', 'body', 'description', 'summary']) ?: $summaryColumn;
                $categoryColumn = $this->firstExistingColumn($columns, ['category', 'category_name', 'topic']);
                $imageColumn = $this->firstExistingColumn($columns, ['image', 'thumbnail', 'cover_image']);
                $publishedColumn = $this->firstExistingColumn($columns, ['published_at', 'created_at', 'updated_at']);

                $query = Schema::hasColumn($table, $publishedColumn ?: 'id')
                    ? Schema::hasColumn($table, $publishedColumn ?: 'id')
                    : true;

                $builder = \DB::table($table);

                if ($publishedColumn) {
                    $builder->orderByDesc($publishedColumn);
                } else {
                    $builder->orderByDesc($idColumn);
                }

                if ($excludeId) {
                    $builder->where($idColumn, '!=', $excludeId);
                }

                $posts = $builder
                    ->limit($excludeId ? 3 : 12)
                    ->get()
                    ->map(function ($row, $index) use ($idColumn, $titleColumn, $summaryColumn, $contentColumn, $categoryColumn, $imageColumn, $publishedColumn, $accents) {
                        $titleText = trim((string) data_get($row, $titleColumn, ''));
                        $summaryText = trim(strip_tags((string) data_get($row, $summaryColumn, '')));
                        $contentText = trim(strip_tags((string) data_get($row, $contentColumn, '')));
                        $readingText = $contentText !== '' ? $contentText : ($summaryText !== '' ? $summaryText : $titleText);

                        return [
                            'id' => (int) data_get($row, $idColumn),
                            'category' => trim((string) data_get($row, $categoryColumn, '')) ?: 'Blog PETSAIGON',
                            'title' => $titleText,
                            'summary' => Str::limit($summaryText !== '' ? $summaryText : $titleText, 150),
                            'content' => $contentText !== '' ? $contentText : ($summaryText !== '' ? $summaryText : $titleText),
                            'image' => $this->normalizeImagePath($imageColumn ? data_get($row, $imageColumn) : null),
                            'published_at' => $publishedColumn ? data_get($row, $publishedColumn) : null,
                            'read_time' => max(1, (int) ceil(str_word_count($readingText) / 180)) . ' phut doc',
                            'accent' => $accents[$index % count($accents)],
                        ];
                    })
                    ->filter(fn ($post) => $post['title'] !== '')
                    ->values()
                    ->all();

                return [
                    'table' => $table,
                    'posts' => $posts,
                ];
            } catch (\Throwable $exception) {
                continue;
            }
        }

        return [
            'table' => null,
            'posts' => [],
        ];
    }

    protected function findBlogPostById(int $id): ?array
    {
        $candidates = ['blog_posts', 'blogs', 'posts', 'articles', 'news', 'tin_tuc', 'tin_tucs'];

        foreach ($candidates as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            try {
                $columns = Schema::getColumnListing($table);
                $idColumn = $this->firstExistingColumn($columns, ['id']);
                $titleColumn = $this->firstExistingColumn($columns, ['title', 'name']);

                if (!$idColumn || !$titleColumn) {
                    continue;
                }

                $summaryColumn = $this->firstExistingColumn($columns, ['summary', 'excerpt', 'description', 'content', 'body']) ?: $titleColumn;
                $contentColumn = $this->firstExistingColumn($columns, ['content', 'body', 'description', 'summary']) ?: $summaryColumn;
                $categoryColumn = $this->firstExistingColumn($columns, ['category', 'category_name', 'topic']);
                $imageColumn = $this->firstExistingColumn($columns, ['image', 'thumbnail', 'cover_image']);
                $publishedColumn = $this->firstExistingColumn($columns, ['published_at', 'created_at', 'updated_at']);

                $row = \DB::table($table)->where($idColumn, $id)->first();
                if (!$row) {
                    continue;
                }

                $titleText = trim((string) data_get($row, $titleColumn, ''));
                $summaryText = trim(strip_tags((string) data_get($row, $summaryColumn, '')));
                $contentText = trim(strip_tags((string) data_get($row, $contentColumn, '')));
                $readingText = $contentText !== '' ? $contentText : ($summaryText !== '' ? $summaryText : $titleText);

                return [
                    'id' => (int) data_get($row, $idColumn),
                    'category' => trim((string) data_get($row, $categoryColumn, '')) ?: 'Blog PETSAIGON',
                    'title' => $titleText,
                    'summary' => $summaryText !== '' ? $summaryText : $titleText,
                    'content' => $contentText !== '' ? $contentText : ($summaryText !== '' ? $summaryText : $titleText),
                    'image' => $this->normalizeImagePath($imageColumn ? data_get($row, $imageColumn) : null),
                    'published_at' => $publishedColumn ? data_get($row, $publishedColumn) : null,
                    'read_time' => max(1, (int) ceil(str_word_count($readingText) / 180)) . ' phut doc',
                ];
            } catch (\Throwable $exception) {
                continue;
            }
        }

        return null;
    }

    protected function buildBlogMessage(array $blogData): ?string
    {
        if (!empty($blogData['posts'])) {
            return null;
        }

        return 'Chua co bai viet nao de hien thi.';
    }

    protected function firstExistingColumn(array $columns, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function extractPrimaryImage(Product $product): ?string
    {
        $path = optional($product->images->first())->image;

        return $this->normalizeImagePath($path);
    }

    protected function normalizeImagePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalizedPath = trim((string) $path);

        if (Str::startsWith($normalizedPath, ['http://', 'https://'])) {
            return $normalizedPath;
        }

        if (Str::startsWith($normalizedPath, ['assets/', '/assets/'])) {
            $frontendUrl = rtrim((string) env('FRONTEND_URL', ''), '/');

            return $frontendUrl !== ''
                ? $frontendUrl . '/' . ltrim($normalizedPath, '/')
                : URL::to('/' . ltrim($normalizedPath, '/'));
        }

        if (Str::startsWith($normalizedPath, ['/storage/', '/images/', '/uploads/'])) {
            return URL::to($normalizedPath);
        }

        if (Str::startsWith($normalizedPath, ['storage/', 'images/', 'uploads/'])) {
            return URL::to('/' . $normalizedPath);
        }

        return URL::to('/storage/' . ltrim($normalizedPath, '/'));
    }
}
