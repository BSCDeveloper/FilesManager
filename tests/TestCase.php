<?php

namespace Fboseca\Filesmanager\Tests;


use Fboseca\Filesmanager\FilesManagerServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase {
	public $user;
	public $user2;

	/**
	 *
	 */
	public function setUp(): void {
		parent::setUp();
		$this->user = User::find(1);
		$this->user2 = User::find(2);
	}

	protected function getPackageProviders($app) {
		return [
			FilesManagerServiceProvider::class,
		];
	}
}