<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    public function categories()
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $this->normalizeImagePath($category->image),
                    'product_count' => $category->products_count,
                ];
            })
            ->values();

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function products(Request $request)
    {
        $perPage = min(max((int) $request->query('limit', 12), 1), 24);
        $sort = (string) $request->query('sort', 'latest');

        $query = Product::query()
            ->with(['category:id,name,slug', 'images:id,product_id,image'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->query('keyword'));

            $query->where(function ($builder) use ($keyword) {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category')) {
            $category = (string) $request->query('category');

            $query->whereHas('category', function ($builder) use ($category) {
                if (is_numeric($category)) {
                    $builder->where('id', (int) $category);

                    return;
                }

                $builder->where('slug', $category);
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->query('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->query('max_price'));
        }

        switch ($sort) {
            case 'default':
                $query->orderByDesc('created_at')
                    ->orderByDesc('id');
                break;
            case 'price_asc':
                $query->orderBy('price');
                break;
            case 'price_desc':
                $query->orderByDesc('price');
                break;
            case 'name_asc':
                $query->orderBy('name');
                break;
            case 'rating':
                $query->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('reviews_count');
                break;
            case 'latest':
            default:
                $query->orderByDesc('created_at')
                    ->orderByDesc('id');
                break;
        }

        $products = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'data' => $products->getCollection()
                ->map(fn (Product $product) => $this->mapProductCard($product))
                ->values(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $product = Product::query()
            ->with(['category:id,name,slug', 'images:id,product_id,image', 'reviews:id,product_id,rating,comment,created_at'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Product::query()
            ->with(['category:id,name,slug', 'images:id,product_id,image'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('created_at')
            ->limit(4)
            ->get()
            ->map(fn (Product $item) => $this->mapProductCard($item))
            ->values();

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'short_description' => Str::limit(strip_tags((string) $product->description), 160),
                'price' => (float) $product->price,
                'stock' => $product->stock,
                'status' => $product->status,
                'unit' => $product->unit,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
                'images' => $product->images
                    ->map(fn ($image) => $this->normalizeImagePath($image->image))
                    ->filter()
                    ->values(),
                'primary_image' => $this->extractPrimaryImage($product),
                'rating_average' => round((float) ($product->reviews_avg_rating ?? 0), 1),
                'rating_count' => $product->reviews_count,
                'reviews' => $product->reviews
                    ->map(function ($review) {
                        return [
                            'rating' => (int) $review->rating,
                            'comment' => $review->comment,
                            'created_at' => optional($review->created_at)->toDateString(),
                        ];
                    })
                    ->values(),
            ],
            'related' => $related,
        ]);
    }

    protected function mapProductCard(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => (float) $product->price,
            'stock' => $product->stock,
            'status' => $product->status,
            'unit' => $product->unit,
            'description' => Str::limit(strip_tags((string) $product->description), 110),
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'primary_image' => $this->extractPrimaryImage($product),
            'rating_average' => round((float) ($product->reviews_avg_rating ?? 0), 1),
            'rating_count' => $product->reviews_count,
        ];
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

        $normalizedPath = trim($path);

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
