<?php

namespace Fboseca\Filesmanager\Tests;


use Fboseca\Filesmanager\FilesManagerServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase {

	/**
	 *
	 */
	public function setUp(): void {
		parent::setUp();
	}

	protected function getPackageProviders($app) {
		return [
			FilesManagerServiceProvider::class,
		];
	}
}