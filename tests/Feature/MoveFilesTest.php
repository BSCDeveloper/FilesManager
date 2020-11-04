<?php

namespace Fboseca\Filesmanager\Tests\Feature;


use Fboseca\Filesmanager\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MoveFilesTest extends TestCase {
	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * @group move
	 */
	public function testMoveFile() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$url = $fileSaved->url;
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame('files', $fileSaved->folder);
		$this->assertSame('public', $fileSaved->disk);

		$fileSaved->move('copies');
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		Storage::disk($fileSaved->disk)->assertMissing($url);
		$this->assertSame('copies', $fileSaved->folder);
		$this->assertSame('public', $fileSaved->disk);
		$this->assertSame(1, $this->user1->files()->count());
	}
}