<?php

namespace IchikawaYoshihiro\ReverseScaffoldGenerator;

use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ReverseScaffoldCommand::class,
            ]);
        }
    }
}
