<?php

namespace Tests\Feature;

use App\Mail\ActivationMail;
use App\Mail\ResetPasswordMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\BuildsClientApiSchema;
use Tests\TestCase;

class ClientAuthApiTest extends TestCase
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

    protected function createUser(array $overrides = []): User
    {
        $role = $overrides['role'] ?? $this->createRole('customer');
        unset($overrides['role']);

        return User::create(array_merge([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('123456'),
            'status' => 'active',
            'phone_number' => '0912345678',
            'address' => 'Ho Chi Minh City',
            'role_id' => $role->id,
        ], $overrides));
    }

    public function test_register_creates_pending_customer_and_sends_activation_email(): void
    {
        $this->createRole('customer');
        Mail::fake();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'status' => 'activation_sent',
            ]);

        $user = User::where('email', 'new@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('pending', $user->status);
        $this->assertNotNull($user->activation_token);

        Mail::assertSent(ActivationMail::class, function (ActivationMail $mail) use ($user) {
            return $mail->hasTo('new@example.com') && $mail->token === $user->activation_token;
        });
    }

    public function test_login_rejects_pending_accounts(): void
    {
        $customerRole = $this->createRole('customer');

        $this->createUser([
            'email' => 'pending@example.com',
            'status' => 'pending',
            'role' => $customerRole,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'pending@example.com',
            'password' => '123456',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Tai khoan chua kich hoat. Vui long kiem tra email de kich hoat tai khoan.',
            ]);
    }

    public function test_login_rejects_non_customer_accounts(): void
    {
        $adminRole = $this->createRole('admin');

        $this->createUser([
            'email' => 'admin@example.com',
            'role' => $adminRole,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => '123456',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Chi tai khoan khach hang moi co the dang nhap tai day.',
            ]);
    }

    public function test_login_allows_active_customer_accounts(): void
    {
        $customerRole = $this->createRole('CUSTOMER');

        $this->createUser([
            'email' => 'active@example.com',
            'role' => $customerRole,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'active@example.com',
            'password' => '123456',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Dang nhap thanh cong.',
                'user' => [
                    'email' => 'active@example.com',
                    'role' => 'customer',
                    'status' => 'active',
                ],
            ]);

        $this->assertSame(
            'http://127.0.0.1:5500/pages/account.html?login=success',
            $response->json('redirect_url')
        );
    }

    public function test_forgot_password_creates_reset_token_and_sends_email(): void
    {
        $customerRole = $this->createRole('customer');
        $user = $this->createUser([
            'email' => 'forgot@example.com',
            'role' => $customerRole,
        ]);

        Mail::fake();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Da gui lien ket dat lai mat khau ve email cua ban. Vui long kiem tra email.',
            ]);

        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        $this->assertNotNull($record);
        $this->assertSame(64, strlen($record->token));

        Mail::assertSent(ResetPasswordMail::class, function (ResetPasswordMail $mail) use ($user) {
            return $mail->hasTo($user->email) && str_contains($mail->resetUrl, '/api/auth/reset-password/');
        });
    }

    public function test_reset_password_updates_credentials_and_clears_reset_token(): void
    {
        $customerRole = $this->createRole('customer');
        $user = $this->createUser([
            'email' => 'reset@example.com',
            'role' => $customerRole,
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', 'plain-reset-token'),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => 'plain-reset-token',
            'password' => '654321',
            'password_confirmation' => '654321',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Dat lai mat khau thanh cong. Moi ban dang nhap lai.',
                'redirect_url' => 'http://127.0.0.1:5500/pages/login.html?reset=success',
            ]);

        $this->assertTrue(Hash::check('654321', $user->fresh()->password));
        $this->assertNull(DB::table('password_reset_tokens')->where('email', $user->email)->first());
    }
}
