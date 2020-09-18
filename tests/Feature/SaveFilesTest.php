<?php

namespace Fboseca\Filesmanager\Tests;


class SaveFilesTest extends TestCase {
	public $user;

	public function setUp(): void {
		parent::setUp();
		$this->user = User::find(1);
	}

	public function testSaveFile() {
		//without parameters
		//with parameters
	}

	public function test() {

	}
}