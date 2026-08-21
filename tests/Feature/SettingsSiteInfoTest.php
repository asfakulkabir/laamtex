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

        $admin = User::factory()->create(['is_admin' => true]);

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
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'site_name' => 'My Store',
            'primary_color' => 'not-a-color',
        ]);

        $response->assertSessionHasErrors('primary_color');
    }

    public function test_settings_page_renders_site_info()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Setting::setValue('site_name', 'Awesome Store');
        Setting::setValue('primary_color', '#e11d48');

        $response = $this->actingAs($admin)->get(route('admin.settings.edit'));
        $response->assertOk();
        $response->assertSee('Awesome Store');
        $response->assertSee('site_logo');
        $response->assertSee('#e11d48');
    }

    public function test_storefront_uses_primary_color_from_settings()
    {
        Setting::setValue('primary_color', '#e11d48');

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('--color-primary: #e11d48', false);
    }
}
