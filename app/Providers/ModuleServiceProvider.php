<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $modulesPath = app_path('Modules');

        if (is_dir($modulesPath)) {
            $modules = array_filter(scandir($modulesPath), function ($item) use ($modulesPath) {
                return $item !== '.' && $item !== '..' && is_dir($modulesPath . '/' . $item);
            });

            foreach ($modules as $module) {
                // 1. Load Routes
                if (file_exists($modulesPath . '/' . $module . '/Routes/web.php')) {
                    $this->loadRoutesFrom($modulesPath . '/' . $module . '/Routes/web.php');
                }
                if (file_exists($modulesPath . '/' . $module . '/Routes/api.php')) {
                    $this->loadRoutesFrom($modulesPath . '/' . $module . '/Routes/api.php');
                }

                // 2. Load Views
                if (is_dir($modulesPath . '/' . $module . '/Resources/Views')) {
                    $this->loadViewsFrom($modulesPath . '/' . $module . '/Resources/Views', strtolower($module));
                }

                // 3. Load Migrations (Optional)
                if (is_dir($modulesPath . '/' . $module . '/Database/Migrations')) {
                    $this->loadMigrationsFrom($modulesPath . '/' . $module . '/Database/Migrations');
                }
            }
        }
    }
}
