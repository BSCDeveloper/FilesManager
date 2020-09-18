<?php

namespace Fboseca\Filesmanager;

use Fboseca\Filesmanager\Console\InstallProviderConsole;
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
			__DIR__ . '/../config/filemanager.php' => config_path('filemanager.php'),
		], 'config');

		$this->publishes([
			__DIR__ . '/../database/migrations/create_files_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_files_table.php'),
		], 'migrations');

		$this->loadRoutesFrom(__DIR__ . '/routes.php');
	}
}
