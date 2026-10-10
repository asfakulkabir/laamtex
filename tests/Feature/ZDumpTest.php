<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZDumpTest extends TestCase
{
    use RefreshDatabase;
    public function test_dump()
    {
        $a = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        file_put_contents('/tmp/pick.html', $this->actingAs($a)->get(route('admin.sliders.create'))->getContent());
        $this->assertTrue(true);
    }
}
