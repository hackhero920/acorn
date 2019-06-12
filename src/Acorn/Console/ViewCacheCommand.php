<?php

namespace Roots\Acorn\Console;

use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ViewCacheCommand extends Command
{
    /**
     * Compile all of the application's Blade templates
     *
     * ## EXAMPLES
     *
     *     wp acorn config:cache
     *
     * @when before_run_command
     *
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        $this->call('view:clear');

        $this->paths()->each(function ($path) {
            $this->compileViews($this->bladeFilesIn([$path]));
        });

        $this->success('Blade templates cached successfully!');
    }

    /**
     * Compile the given view files.
     *
     * @param  \Illuminate\Support\Collection  $views
     * @return void
     */
    protected function compileViews(Collection $views)
    {
        $compiler = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();

        $views->map(function (SplFileInfo $file) use ($compiler) {
            $compiler->compile($file->getRealPath());
        });
    }

    /**
     * Get the Blade files in the given path.
     *
     * @param  array  $paths
     * @return \Illuminate\Support\Collection
     */
    protected function bladeFilesIn(array $paths)
    {
        return collect(
            Finder::create()
                ->in($paths)
                ->exclude('vendor')
                ->name('*.blade.php')
                ->files()
        );
    }

    /**
     * Get all of the possible view paths.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function paths()
    {
        $finder = $this->app['view.finder'];

        return collect($finder->getPaths())->merge(
            collect($finder->getHints())->flatten()
        );
    }
}
