<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Role;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsClientApiSchema;
use Tests\TestCase;

class ClientAccountApiTest extends TestCase
{
    use BuildsClientApiSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useClientApiTestDatabase();
    }

    protected function createRole(string $name): Role
    {
        return Role::create(['name' => $name]);
    }

    protected function createCustomer(array $overrides = []): User
    {
        $role = $overrides['role'] ?? $this->createRole('customer');
        unset($overrides['role']);

        return User::create(array_merge([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => Hash::make('123456'),
            'status' => 'active',
            'phone_number' => '0912345678',
            'address' => 'Ho Chi Minh City',
            'role_id' => $role->id,
        ], $overrides));
    }

    protected function createAddress(User $user, array $overrides = []): ShippingAddress
    {
        return ShippingAddress::create(array_merge([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'phone' => '0912345678',
            'address' => '123 Nguyen Trai',
            'city' => 'Ho Chi Minh City',
            'default' => false,
        ], $overrides));
    }

    public function test_account_summary_returns_customer_profile_orders_and_addresses(): void
    {
        $customerRole = $this->createRole('CUSTOMER');
        $user = $this->createCustomer(['role' => $customerRole]);
        $address = $this->createAddress($user, ['default' => true]);

        Order::create([
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
            'total_price' => 250000,
            'status' => 'processing',
        ]);

        $response = $this->getJson('/api/account/summary?email=' . urlencode($user->email));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'user' => [
                    'email' => $user->email,
                    'status' => 'active',
                ],
                'addresses' => [
                    [
                        'id' => $address->id,
                        'default' => true,
                    ],
                ],
                'orders' => [
                    [
                        'status' => 'processing',
                    ],
                ],
            ]);
    }

    public function test_adding_an_address_marks_the_first_address_as_default(): void
    {
        $user = $this->createCustomer();

        $response = $this->postJson('/api/account/addresses', [
            'email' => $user->email,
            'full_name' => 'Nguyen Van A',
            'phone' => '0912345678',
            'address' => '456 Le Loi',
            'city' => 'Da Nang',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Them dia chi moi thanh cong.',
            ]);

        $this->assertDatabaseHas('shipping_addresses', [
            'user_id' => $user->id,
            'phone' => '0912345678',
            'default' => 1,
        ]);
    }

    public function test_setting_a_default_address_switches_the_previous_default_off(): void
    {
        $user = $this->createCustomer();
        $primary = $this->createAddress($user, ['default' => true]);
        $secondary = $this->createAddress($user, [
            'full_name' => 'Nguoi nhan khac',
            'default' => false,
        ]);

        $response = $this->putJson('/api/account/addresses/' . $secondary->id . '/default', [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Da cap nhat dia chi mac dinh.',
            ]);

        $this->assertSame(0, (int) $primary->fresh()->default);
        $this->assertSame(1, (int) $secondary->fresh()->default);
    }

    public function test_deleting_the_default_address_promotes_the_next_one(): void
    {
        $user = $this->createCustomer();
        $primary = $this->createAddress($user, ['default' => true]);
        $secondary = $this->createAddress($user, [
            'full_name' => 'Nguoi nhan thu hai',
            'default' => false,
        ]);

        $response = $this->deleteJson('/api/account/addresses/' . $primary->id, [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Xoa dia chi thanh cong.',
            ]);

        $this->assertDatabaseMissing('shipping_addresses', ['id' => $primary->id]);
        $this->assertSame(1, (int) $secondary->fresh()->default);
    }

    public function test_change_password_updates_the_customer_password(): void
    {
        $user = $this->createCustomer();

        $response = $this->postJson('/api/account/change-password', [
            'email' => $user->email,
            'current_password' => '123456',
            'new_password' => '654321',
            'confirm_new_password' => '654321',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Doi mat khau thanh cong.',
            ]);

        $this->assertTrue(Hash::check('654321', $user->fresh()->password));
    }

    public function test_profile_update_saves_basic_customer_information(): void
    {
        $user = $this->createCustomer();

        $response = $this->post('/api/account/profile', [
            'email' => $user->email,
            'name' => 'Nguyen Thi B',
            'phone_number' => '0987654321',
            'address' => '789 Tran Hung Dao',
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Cap nhat thong tin tai khoan thanh cong.',
                'user' => [
                    'name' => 'Nguyen Thi B',
                    'email' => $user->email,
                    'phone_number' => '0987654321',
                    'address' => '789 Tran Hung Dao',
                ],
            ]);

        $user->refresh();

        $this->assertSame('Nguyen Thi B', $user->name);
        $this->assertSame('0987654321', $user->phone_number);
        $this->assertSame('789 Tran Hung Dao', $user->address);
    }

    public function test_profile_update_uploads_avatar_to_frontend_assets_directory(): void
    {
        $user = $this->createCustomer();
        $avatar = UploadedFile::fake()->image('avatar.png');

        $response = $this->post('/api/account/profile', [
            'email' => $user->email,
            'name' => 'Avatar Customer',
            'phone_number' => '0912345678',
            'address' => '123 Upload Street',
            'avatar' => $avatar,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Cap nhat thong tin tai khoan thanh cong.',
                'user' => [
                    'name' => 'Avatar Customer',
                    'email' => $user->email,
                ],
            ]);

        $user->refresh();

        $frontendAvatarPath = dirname(base_path()) . DIRECTORY_SEPARATOR . 'sieuthithucung-frontend'
            . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $user->avatar);

        $this->assertNotNull($user->avatar);
        $this->assertStringStartsWith('assets/images/uploads/users/avatar_', $user->avatar);
        $this->assertFileExists($frontendAvatarPath);
        $this->assertStringContainsString('/assets/images/uploads/users/avatar_', $response->json('user.avatar'));

        unlink($frontendAvatarPath);
    }
}
