<?php

namespace Fboseca\Filesmanager\Tests\Feature;


use Fboseca\Filesmanager\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CopyFilesTest extends TestCase {
	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * @group copy
	 */
	public function testCopyAFile() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$fileCopied = $fileSaved->copy();
		$this->assertSame(1, null);
		Storage::disk($fileCopied->disk)->assertExists($fileCopied->url);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame($fileSaved->folder, $fileCopied->folder);
		$this->assertSame($fileSaved->group, $fileCopied->group);
		$this->assertSame($fileSaved->type, $fileCopied->type);
		$this->assertSame($fileSaved->group, $fileCopied->group);
		$this->assertSame(2, $this->user1->files()->count());
	}

	public function testCopyWithSameName() {
		$file = UploadedFile::fake()->image('avatar.doc');
		$fileSaved = $this->user1->addFile($file, [
			"group"       => "gallery",
			"name"        => "my Name File",
			"description" => "A description for a file"
		]);
	}

	public function testCopyFileWithDistinctFolder() {

	}

	public function testCopyFileWithDistinctDisk() {
		//config('filemanager.url_link_private_files')
	}

	public function testCopyFileWithDistinctModel() {

	}

	public function testCopyFileWithDistinctFolderDiskAndModel() {

	}

	public function testCopyManyFiles() {

	}

	public function testCopyFilesInDistinctFolder() {

	}
}