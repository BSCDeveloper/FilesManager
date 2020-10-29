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
		Storage::disk($fileCopied->disk)->assertExists($fileCopied->url);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame($fileSaved->folder, $fileCopied->folder);
		$this->assertSame($fileSaved->group, $fileCopied->group);
		$this->assertSame($fileSaved->disk, $fileCopied->disk);
		$this->assertSame($fileSaved->type, $fileCopied->type);
		$this->assertSame(2, $this->user1->files()->count());
	}

	/**
	 * @group copy
	 */
	public function testCopyWithSameName() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file, [
			"group"       => "gallery",
			"name"        => "name",
			"description" => "A description for a file"
		]);

		$fileCopied = $fileSaved->copy();
		Storage::disk($fileCopied->disk)->assertExists($fileCopied->url);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame('name.jpg', $fileSaved->name);
		$this->assertSame('name_(1).jpg', $fileCopied->name);
		$this->assertSame($fileSaved->folder, $fileCopied->folder);
		$this->assertSame($fileSaved->group, $fileCopied->group);
		$this->assertSame($fileSaved->type, $fileCopied->type);
		$this->assertSame($fileSaved->disk, $fileCopied->disk);
		$this->assertSame($fileSaved->description, $fileCopied->description);
		$this->assertSame(2, $this->user1->files()->count());
	}

	/**
	 * @group copy
	 */
	public function testCopyFileWithDistinctFolder() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$fileCopied = $fileSaved->copy([
			"folder" => 'filesCopied'
		]);

		Storage::disk($fileCopied->disk)->assertExists($fileCopied->url);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame('files', $fileSaved->folder);
		$this->assertSame('filesCopied', $fileCopied->folder);
		$this->assertSame($fileSaved->group, $fileCopied->group);
		$this->assertSame($fileSaved->disk, $fileCopied->disk);
		$this->assertSame($fileSaved->type, $fileCopied->type);
		$this->assertSame(2, $this->user1->files()->count());
	}

	/**
	 * @group copy
	 */
	public function testCopyFileWithDistinctDisk() {
		//config('filemanager.url_link_private_files')
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$fileCopied = $fileSaved->copy([
			"disk" => 'private'
		]);

		Storage::disk($fileCopied->disk)->assertExists($fileCopied->url);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame($fileCopied->folder, $fileSaved->folder);
		$this->assertSame($fileSaved->group, $fileCopied->group);
		$this->assertSame('public', $fileSaved->disk);
		$this->assertSame('private', $fileCopied->disk);
		$this->assertSame($fileSaved->type, $fileCopied->type);
		$this->assertSame(2, $this->user1->files()->count());
	}

	/**
	 * @group copy
	 */
	public function testCopyFileWithDistinctModel() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$fileCopied = $fileSaved->copyToModel($this->user2);

		Storage::disk($fileCopied->disk)->assertExists($fileCopied->url);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame($fileCopied->folder, $fileSaved->folder);
		$this->assertSame($fileSaved->group, $fileCopied->group);
		$this->assertSame($fileCopied->disk, $fileSaved->disk);
		$this->assertSame($fileSaved->type, $fileCopied->type);
		$this->assertSame(1, $this->user1->files()->count());
		$this->assertSame(1, $this->user2->files()->count());
	}

	/**
	 * @group copy
	 */
	public function testCopyFileWithAllDistinct() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$fileCopied = $fileSaved->copyToModel($this->user2, [
			"folder"      => 'fileCopied',
			"disk"        => 'private',
			"name"        => 'avatar',
			"group"       => 'gallery',
			"description" => 'A description of file',
		]);

		Storage::disk($fileCopied->disk)->assertExists($fileCopied->url);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame('files', $fileSaved->folder);
		$this->assertSame('public', $fileSaved->disk);
		$this->assertSame('', $fileSaved->group);
		$this->assertSame('', $fileSaved->description);

		$this->assertSame('fileCopied', $fileCopied->folder);
		$this->assertSame('private', $fileCopied->disk);
		$this->assertSame('gallery', $fileCopied->group);
		$this->assertSame('A description of file', $fileCopied->description);

		$this->assertSame($fileSaved->type, $fileCopied->type);
		$this->assertSame(1, $this->user1->files()->count());
		$this->assertSame(1, $this->user2->files()->count());
	}

	public function testCopyManyFiles() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$this->user1->addFile($file);
		$this->user1->addFile($file);
		$this->user1->files->copyFiles();
		$this->assertSame(4, $this->user1->files()->count());
	}

	public function testCopyFilesInDistinctFolder() {

	}
}