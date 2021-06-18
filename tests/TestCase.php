<?php

namespace Fboseca\Filesmanager\Tests;


use Carbon\Carbon;
use Fboseca\Filesmanager\FilesManagerServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TestCase extends \Orchestra\Testbench\TestCase {
	use RefreshDatabase;

	public $configFile = 'filemanager';
	public $user1;
	public $user2;


	public function setUp(): void {
		parent::setUp();
		$this->loadLaravelMigrations();
		$this->getUsers();
	}

	protected function getPackageProviders($app) {
		return [
			FilesManagerServiceProvider::class,
		];
	}

	protected function getEnvironmentSetUp($app) {
		parent::getEnvironmentSetUp($app);
		$this->UpStorages();
		$this->UpConfig($app);
	}

	private function UpConfig($app) {
		//charge the config file
		if (!File::exists(config_path($this->configFile . ".php"))) {
			Artisan::call('vendor:publish', [
				'--provider' => "Fboseca\Filesmanager\FilesManagerServiceProvider",
				"--tag"      => "config"
			]);
		}

		//simulate storage link laravel
		$app['router']->get('/storage/{disk}/{route}', function ($disk, $route) use ($app) {
			if (\Storage::disk($disk)->exists($route)) {
				return \Storage::disk($disk)->get($route);
			}
			abort(401);
		})->where([ 'route' => '[\w\./]*' ]);

		//adds filesystems config
		$app['config']->set('filesystems.disks.private', [ "driver" => 'local' ]);

		// import the CreatePostsTable class from the migration
		include_once __DIR__ . '/../database/migrations/create_files_table.php.stub';

		// run the up() method of that migration class
		(new \CreateFilesTable())->up();
	}

	private function getUsers() {
		User::create([
			"name"              => 'Borja',
			"email"             => 'bor@g.es',
			"password"          => '123',
			"created_at"        => Carbon::now(),
			"updated_at"        => Carbon::now(),
			"email_verified_at" => Carbon::now(),
		]);
		User::create([
			"name"              => 'Pepe',
			"email"             => 'pepe@g.es',
			"password"          => '123',
			"created_at"        => Carbon::now(),
			"updated_at"        => Carbon::now(),
			"email_verified_at" => Carbon::now(),
		]);
		$this->user1 = User::find(1);
		$this->user2 = User::find(2);
	}

	/**
	 * Create a disk temporary
	 */
	private function UpStorages() {
		Storage::fake('temp', [
			'driver'     => 'local',
			'visibility' => 'private'
		]);
		Storage::fake('public', [
			'driver'     => 'local',
			'url'        => env('APP_URL') . '/storage/public',
			'visibility' => 'public'
		]);
		Storage::fake('private', [
			'driver'     => 'local',
			'url'        => env('APP_URL') . config('filemanager.url_link_private_files'),
			'visibility' => 'private'
		]);
	}
}