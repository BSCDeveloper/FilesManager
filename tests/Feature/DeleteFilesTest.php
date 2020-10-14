<?php

namespace Fboseca\Filesmanager\Tests\Feature;


use Fboseca\Filesmanager\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DeleteFilesTest extends TestCase {
	public function setUp(): void {
		parent::setUp();
	}

	public function testDeleteFile() {
		$file = UploadedFile::fake()->image('avatar.png', 100);
		$this->user1->addFile($file);
		$this->user1->addFile($file);
		$this->user1->addFile($file);
		$this->assertSame(3, $this->user1->files()->count());
		foreach ($this->user1->files as $file) {
			$url = $file->url;
			Storage::disk($file->disk)->assertExists($url);
			$file->delete();
			Storage::disk($file->disk)->assertMissing($url);
		}

		$this->assertSame(0, $this->user1->files()->count());
	}

	public function testDeleteAllFiles() {
		$file = UploadedFile::fake()->image('avatar.png', 100);
		$this->user1->addFile($file);
		$this->user1->addFile($file);
		$this->user1->addFile($file);
		$this->assertSame(3, $this->user1->files()->count());
		$url = [];
		foreach ($this->user1->files as $file) {
			$url[] = $file->url;
			Storage::disk($file->disk)->assertExists($file->url);
		}
		$this->user1->files->removeFiles();

		foreach ($url as $file) {
			Storage::disk('public')->assertMissing($file);
		}

		$this->assertSame(0, $this->user1->files()->count());
	}
}