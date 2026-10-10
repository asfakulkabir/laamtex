<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsSiteInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_site_info_and_logo()
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'site_name' => 'My Store',
            'site_description' => 'Best store ever',
            'site_logo' => UploadedFile::fake()->image('logo.png', 100, 100),
            'primary_color' => '#e11d48',
        ]);

        $response->assertRedirect(route('admin.settings.edit'));
        $this->assertSame('My Store', Setting::getValue('site_name'));
        $this->assertSame('Best store ever', Setting::getValue('site_description'));
        $this->assertSame('#e11d48', Setting::getValue('primary_color'));

        $logo = Setting::getValue('site_logo');
        Storage::disk('public')->assertExists($logo);

        $this->assertSame('My Store', site_name());
        $this->assertSame('Best store ever', site_description());
        $this->assertStringContainsString('/storage/', site_logo());
        $this->assertSame('#e11d48', primary_color());
    }

    public function test_primary_color_is_rejected_when_not_hex()
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'site_name' => 'My Store',
            'primary_color' => 'not-a-color',
        ]);

        $response->assertSessionHasErrors('primary_color');
    }

    public function test_settings_page_renders_site_info()
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        Setting::setValue('site_name', 'Awesome Store');
        Setting::setValue('primary_color', '#e11d48');

        $response = $this->actingAs($admin)->get(route('admin.settings.edit'));
        $response->assertOk();
        $response->assertSee('Awesome Store');
        $response->assertSee('site_logo');
        $response->assertSee('#e11d48');
    }

    public function test_contact_phone_and_whatsapp_number_are_stored_separately()
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'site_name' => 'My Store',
            'contact_phone' => '+8801711111111',
            'whatsapp_number' => '+8801722222222',
        ]);

        $response->assertRedirect(route('admin.settings.edit'));
        $this->assertSame('+8801711111111', Setting::getValue('contact_phone'));
        $this->assertSame('+8801722222222', Setting::getValue('whatsapp_number'));

        $this->assertSame('+8801711111111', contact_phone());
        $this->assertSame('8801711111111', contact_phone_digits());
        $this->assertSame('+8801722222222', whatsapp_number());
        $this->assertSame('8801722222222', whatsapp_number_digits());
        $this->assertSame('https://wa.me/8801722222222', whatsapp_link());
    }

    public function test_settings_page_renders_both_phone_fields()
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $response = $this->actingAs($admin)->get(route('admin.settings.edit'));
        $response->assertOk();
        $response->assertSee('name="contact_phone"', false);
        $response->assertSee('name="whatsapp_number"', false);
    }

    public function test_footer_uses_contact_phone_for_calling_and_whatsapp_for_chat()
    {
        Setting::setValue('contact_phone', '+8801711111111');
        Setting::setValue('whatsapp_number', '+8801722222222');

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('tel:8801711111111', false);
        $response->assertSee('https://wa.me/8801722222222', false);
    }

    public function test_storefront_uses_primary_color_from_settings()
    {
        Setting::setValue('primary_color', '#e11d48');

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('--color-primary: #e11d48', false);
    }
}
