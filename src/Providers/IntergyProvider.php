<?php namespace Intergy\Providers;

use Config;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class IntergyProvider extends ServiceProvider
{
    /**
     * Indicates that we should not load this singleton until requested.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('intergy', function ($app)
        {
            $client = new \App\Services\Repositories\IntergyRepository();

            return $client;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['intergy'];
    }

}