<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\RegisterProviders as FoundationRegisterProviders;
use Illuminate\Foundation\PackageManifest as FoundationPackageManifest;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\PackageManifest;

class RegisterProviders extends FoundationRegisterProviders
{
    /**
     * Bootstrap the given application.
     *
     * @param  Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->registerPackageManifest($app);
        parent::bootstrap($app);
    }

    protected function registerPackageManifest(Application $app)
    {
        $app->singleton(FoundationPackageManifest::class, function () use ($app) {

            $files = new Filesystem();

            $composer_paths = collect(get_option('active_plugins'))
                ->map(function ($plugin) {
                    return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($plugin);
                })
                ->merge([
                    $app->basePath(),
                    STYLESHEETPATH,
                    TEMPLATEPATH,
                    get_template_directory(),
                    get_stylesheet_directory(),
                ])
                ->map(function ($path) {
                    return "{$path}/vendor/composer/installed.json";
                })
                ->unique()
                ->filter(function ($path) use ($files) {
                    return $files->exists($path);
                })
                ->all();

            return new PackageManifest(
                $files,
                $composer_paths,
                $app->getCachedPackagesPath()
            );
        });
    }
}
