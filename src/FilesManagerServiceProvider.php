<?php

namespace Fboseca\Filesmanager;

use Fboseca\Filesmanager\Managers\ZipFileManager;
use Illuminate\Support\Collection;
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
		Collection::macro('toZipFile', function () {
			return new ZipFileManager($this);
		});
		$this->publishes([
			__DIR__ . '/config/config.php' => config_path('filemanager.php'),
		], 'config');

		$this->loadMigrationsFrom(__DIR__ . '/migrations');
		$this->loadRoutesFrom(__DIR__ . '/routes.php');
		/*
		$this->loadViewsFrom(__DIR__.'/views', 'todolist');
		$this->publishes([
			__DIR__.'/views' => resource_path('views/borsercen/todolist'),
		]);*/
	}
}
