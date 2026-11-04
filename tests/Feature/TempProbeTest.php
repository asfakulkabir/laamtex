<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempProbeTest extends TestCase
{
    use RefreshDatabase;

    public function test_probe_statuses()
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        file_put_contents('/tmp/probe.log', 'ENV: '.app()->environment().' runningUnitTests: '.var_export(app()->runningUnitTests(), true).' runningInConsole: '.var_export(app()->runningInConsole(), true).PHP_EOL, FILE_APPEND);
        $this->withoutExceptionHandling();
        try {
            $r1 = $this->actingAs($admin)->post('/admin/products', ['name' => 'X']);
        } catch (\Throwable $e) {
            file_put_contents('/tmp/probe.log', 'EXCEPTION: '.get_class($e).' :: '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine()."
", FILE_APPEND);
            throw $e;
        }
        file_put_contents('/tmp/probe.log', 'POST /admin/products => '.$r1->status()."\n", FILE_APPEND);

        $r2 = $this->actingAs($admin)->get('/admin/products');
        file_put_contents('/tmp/probe.log', 'GET /admin/products => '.$r2->status()."\n", FILE_APPEND);

        $r3 = $this->post('/contact', ['name' => 'x', 'email' => 'a@b.com', 'message' => 'hello world']);
        file_put_contents('/tmp/probe.log', 'POST /contact => '.$r3->status()."\n", FILE_APPEND);

        $r4 = $this->put('/admin/products/1', ['name' => 'X']);
        file_put_contents('/tmp/probe.log', 'PUT /admin/products/1 => '.$r4->status()."\n", FILE_APPEND);

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $r5 = $this->actingAs($admin)->post('/admin/products', ['name' => 'X']);
        file_put_contents('/tmp/probe.log', 'POST /admin/products (no csrf) => '.$r5->status()."\n", FILE_APPEND);

        $this->assertTrue(true);
    }
}
