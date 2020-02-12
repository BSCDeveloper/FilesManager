<?php

namespace Bscdeveloper\Filesmanager;

use Illuminate\Support\ServiceProvider;

class FilesManagerServiceProvider extends ServiceProvider {
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register() {
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot() {
		$this->loadMigrationsFrom(__DIR__ . '/migrations');

		$this->publishes([
			__DIR__ . '/config/config.php' => config_path('filemanager.php'),
		], 'config');
		/*$this->loadRoutesFrom(__DIR__.'/routes.php');
		$this->loadViewsFrom(__DIR__.'/views', 'todolist');
		$this->publishes([
			__DIR__.'/views' => resource_path('views/borsercen/todolist'),
		]);*/
	}
}
