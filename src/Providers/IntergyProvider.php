<?php namespace Intergy\Providers;

use Config;
use Illuminate\Contracts\Container\Container as Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class IntergyProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig($this->app);
    }

    /**
     * Setup the config.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     *
     * @return void
     */
    protected function setupConfig(Application $app)
    {
        $source = config_path('intergy.php');

        $this->mergeConfigFrom($source, 'intergy');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $config = $this->app['config']->get('intergy');
        $this->app->singleton('intergy', function ($app) use($config)
        {
            //$client = new \App\Services\Repositories\IntergyRepository();
            $client = new \Intergy\IntergyService( $config );

            $patientStorage = new \Intergy\Storage\PatientStorage( $config );
            $client->setPatientStorage( $patientStorage );

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