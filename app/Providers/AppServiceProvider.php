<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The site can be served from a subdirectory (e.g. http://localhost/laamtex)
        // and the project root doubles as the document root. Laravel's asset()
        // helper follows the request's base path automatically, but Storage::url()
        // uses the fixed APP_URL and would emit links missing that subdirectory,
        // so uploaded files 404. Point the public disk at the current request root.
        if (! $this->app->runningInConsole() && $this->app->has('request')) {
            $root = rtrim(request()->root(), '/');

            if ($root !== '') {
                config(['filesystems.disks.public.url' => $root.'/storage']);
            }
        }
    }
}
