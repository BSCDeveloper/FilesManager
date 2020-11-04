<?php

namespace Fboseca\Filesmanager;

use Fboseca\Filesmanager\Macros\CopyFiles;
use Fboseca\Filesmanager\Macros\CopyFilesToModel;
use Fboseca\Filesmanager\Macros\RemoveFiles;
use Fboseca\Filesmanager\Macros\ToZipFile;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class FilesManagerServiceProvider extends ServiceProvider {
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register() {
		Collection::make($this->macros())
			->reject(function ($class, $macro) {
				Collection::hasMacro($macro);
			})
			->each(function ($class, $macro) {
				Collection::macro($macro, app($class)());
			});
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot() {

		$this->publishes([
			__DIR__ . '/../config/filemanager.php' => config_path('filemanager.php'),
		], 'config');

		$this->publishes([
			__DIR__ . '/../database/migrations/create_files_table.php.stub' => database_path('migrations/' . date('Y_m_d_His',
					time()) . '_create_files_table.php'),
		], 'migrations');

		$this->loadRoutesFrom(__DIR__ . '/routes.php');
	}

	private function macros(): array {
		return [
			'toZipFile'        => ToZipFile::class,
			'removeFiles'      => RemoveFiles::class,
			'copyFiles'        => CopyFiles::class,
			'copyFilesToModel' => CopyFilesToModel::class,
		];
	}
}
