<?php

namespace Tests\Feature;


use Fboseca\Filesmanager\Tests\InstallProviderConsole;
use Fboseca\Filesmanager\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallationTest extends TestCase {

	const FILE_CONFIG = 'filemanager.php';

	public function test_install_service_provider() {

		// Running the command
		if (File::exists(config_path(self::FILE_CONFIG))) {
			unlink(config_path(self::FILE_CONFIG));
		}

		$this->assertFalse(File::exists(config_path(self::FILE_CONFIG)));

		Artisan::call('vendor:publish', [
			'--provider' => "Fboseca\Filesmanager\FilesManagerServiceProvider"
		]);
		$this->assertTrue(File::exists(config_path(self::FILE_CONFIG)));

		Artisan::call('storage:link');
	}

	public function test_migrate() {
		$this->loadLaravelMigrations();
		Artisan::call('migrate');
	}
}
