<?php namespace Mds\Support\Laravel;

use Mds\Collivery;
use Illuminate\Support\ServiceProvider;

class ColliveryServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('collivery', function( $app )
		{
			$config = $app->make('config')->get('collivery::config');
			$cache  = $app->make('cache');
			
			return new Collivery( $config, $cache );
		});
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package( 'mds/collivery', 'collivery', __DIR__ );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
