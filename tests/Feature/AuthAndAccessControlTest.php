<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthAndAccessControlTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/mahasiswa/dashboard');
        $response->assertRedirect('/login');

        $adminResponse = $this->get('/admin/dashboard');
        $adminResponse->assertRedirect('/login');
    }

    public function test_mahasiswa_cannot_access_admin_dashboard(): void
    {
        $mahasiswa = new User([
            'id' => 99,
            'name' => 'Mahasiswa Test',
            'email' => 'mahasiswa_test@test.com',
            'role' => User::ROLE_MAHASISWA,
        ]);

        $response = $this->actingAs($mahasiswa)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_mahasiswa_dashboard(): void
    {
        $admin = new User([
            'id' => 98,
            'name' => 'Admin Test',
            'email' => 'admin_test@test.com',
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->get('/mahasiswa/dashboard');
        $response->assertStatus(403);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sistem Pengajuan Magang');
    }

    public function test_register_page_renders_successfully(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Mahasiswa');
    }
}
