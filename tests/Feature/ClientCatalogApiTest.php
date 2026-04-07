<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsClientApiSchema;
use Tests\TestCase;

class ClientCatalogApiTest extends TestCase
{
    use BuildsClientApiSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useClientApiTestDatabase();
    }

    protected function createRole(string $name = 'customer'): Role
    {
        return Role::create(['name' => $name]);
    }

    protected function createUser(array $overrides = []): User
    {
        $role = $overrides['role'] ?? $this->createRole();
        unset($overrides['role']);

        return User::create(array_merge([
            'name' => 'Catalog User',
            'email' => 'catalog@example.com',
            'password' => Hash::make('123456'),
            'status' => 'active',
            'role_id' => $role->id,
        ], $overrides));
    }

    protected function createCategory(array $overrides = []): Category
    {
        static $index = 1;

        $category = Category::create(array_merge([
            'name' => 'Category ' . $index,
            'slug' => 'category-' . $index,
            'description' => 'Category description ' . $index,
            'image' => 'uploads/category-' . $index . '.jpg',
        ], $overrides));

        $index++;

        return $category;
    }

    protected function createProduct(Category $category, array $overrides = []): Product
    {
        static $index = 1;

        $product = Product::create(array_merge([
            'name' => 'Product ' . $index,
            'slug' => 'product-' . $index,
            'category_id' => $category->id,
            'description' => 'Description ' . $index,
            'price' => 100000 + ($index * 1000),
            'stock' => 10,
            'status' => 'in_stock',
            'unit' => 'bag',
        ], $overrides));

        $index++;

        return $product;
    }

    public function test_categories_returns_product_counts_and_normalized_images(): void
    {
        $dogFood = $this->createCategory([
            'name' => 'Dog Food',
            'slug' => 'dog-food',
            'image' => 'uploads/dog-food.jpg',
        ]);

        $catFood = $this->createCategory([
            'name' => 'Cat Food',
            'slug' => 'cat-food',
            'image' => null,
        ]);

        $this->createProduct($dogFood, ['slug' => 'dog-1']);
        $this->createProduct($dogFood, ['slug' => 'dog-2']);
        $this->createProduct($catFood, ['slug' => 'cat-1']);

        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonPath('data.0.slug', 'cat-food')
            ->assertJsonPath('data.1.slug', 'dog-food')
            ->assertJsonPath('data.1.product_count', 2)
            ->assertJsonPath('data.1.image', 'http://localhost/uploads/dog-food.jpg');
    }

    public function test_products_support_filters_sorting_and_rating_payload(): void
    {
        $category = $this->createCategory([
            'name' => 'Demo Food',
            'slug' => 'demo-food',
        ]);

        $otherCategory = $this->createCategory([
            'name' => 'Accessory',
            'slug' => 'accessory',
        ]);

        $user = $this->createUser();

        $matchingProduct = $this->createProduct($category, [
            'name' => 'Premium Pate',
            'slug' => 'premium-pate',
            'description' => 'Pate cho meo an ngon',
            'price' => 150000,
        ]);

        $cheaperProduct = $this->createProduct($category, [
            'name' => 'Economy Pate',
            'slug' => 'economy-pate',
            'description' => 'Pate tiet kiem',
            'price' => 90000,
        ]);

        $this->createProduct($otherCategory, [
            'name' => 'Leather Collar',
            'slug' => 'leather-collar',
            'description' => 'Phu kien cho cho',
            'price' => 70000,
        ]);

        ProductImage::create([
            'product_id' => $matchingProduct->id,
            'image' => 'uploads/premium-pate.jpg',
        ]);

        Review::create([
            'user_id' => $user->id,
            'product_id' => $matchingProduct->id,
            'rating' => 5,
            'comment' => 'Rat tot',
        ]);

        Review::create([
            'user_id' => $user->id,
            'product_id' => $cheaperProduct->id,
            'rating' => 3,
            'comment' => 'Tam on',
        ]);

        $response = $this->getJson('/api/products?keyword=pate&category=demo-food&min_price=100000&sort=rating&limit=10');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'premium-pate')
            ->assertJsonPath('data.0.primary_image', 'http://localhost/uploads/premium-pate.jpg')
            ->assertJsonPath('data.0.rating_average', 5)
            ->assertJsonPath('data.0.rating_count', 1);
    }

    public function test_product_detail_returns_images_reviews_and_related_products(): void
    {
        $category = $this->createCategory([
            'name' => 'Dog Food',
            'slug' => 'dog-food',
        ]);

        $user = $this->createUser();

        $mainProduct = $this->createProduct($category, [
            'name' => 'Royal Canin Mini Adult',
            'slug' => 'royal-canin-mini-adult',
            'description' => 'Dry food for small breed adult dogs',
            'price' => 320000,
        ]);

        $relatedProduct = $this->createProduct($category, [
            'name' => 'Royal Canin Puppy',
            'slug' => 'royal-canin-puppy',
            'description' => 'Dry food for puppies',
            'price' => 280000,
        ]);

        ProductImage::create([
            'product_id' => $mainProduct->id,
            'image' => 'uploads/product-main-a.jpg',
        ]);

        ProductImage::create([
            'product_id' => $mainProduct->id,
            'image' => 'uploads/product-main-b.jpg',
        ]);

        ProductImage::create([
            'product_id' => $relatedProduct->id,
            'image' => 'uploads/product-related.jpg',
        ]);

        Review::create([
            'user_id' => $user->id,
            'product_id' => $mainProduct->id,
            'rating' => 5,
            'comment' => 'Very good quality',
        ]);

        $response = $this->getJson('/api/products/royal-canin-mini-adult');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'royal-canin-mini-adult')
            ->assertJsonPath('data.category.slug', 'dog-food')
            ->assertJsonPath('data.primary_image', 'http://localhost/uploads/product-main-a.jpg')
            ->assertJsonPath('data.images.1', 'http://localhost/uploads/product-main-b.jpg')
            ->assertJsonPath('data.reviews.0.comment', 'Very good quality')
            ->assertJsonPath('related.0.slug', 'royal-canin-puppy');
    }
}
