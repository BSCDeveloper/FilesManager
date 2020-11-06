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

	/**
	 * @group move
	 */
	public function testMoveFileOtherDisk() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$url = $fileSaved->url;
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame('files', $fileSaved->folder);
		$this->assertSame('public', $fileSaved->disk);

		$fileSaved->move('copies', [
			"disk" => 'private'
		]);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		Storage::disk($fileSaved->disk)->assertMissing($url);
		$this->assertSame('copies', $fileSaved->folder);
		$this->assertSame('private', $fileSaved->disk);
		$this->assertSame(1, $this->user1->files()->count());
	}

	/**
	 * @group move
	 */
	public function testMoveFileWithOptions() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$url = $fileSaved->url;
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame('files', $fileSaved->folder);
		$this->assertSame('public', $fileSaved->disk);

		$fileSaved->move('copies', [
			"group"       => "gallery",
			"name"        => "name",
			"description" => "A description for a file",
			"disk"        => 'private'
		]);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		Storage::disk($fileSaved->disk)->assertMissing($url);
		$this->assertSame('copies', $fileSaved->folder);
		$this->assertSame('name.jpg', $fileSaved->name);
		$this->assertSame('gallery', $fileSaved->group);
		$this->assertSame('A description for a file', $fileSaved->description);
		$this->assertSame('private', $fileSaved->disk);
		$this->assertSame(1, $this->user1->files()->count());
	}

	/**
	 * @group move
	 */
	public function testMoveFileWithSameNameInOtherFolder() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file, [
			"folder" => 'copies',
			"name"   => "name"
		]);
		$url = $fileSaved->url;
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame('copies', $fileSaved->folder);
		$this->assertSame('public', $fileSaved->disk);

		$fileSaved->move('copies', [
			"name" => "name",
		]);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		Storage::disk($fileSaved->disk)->assertMissing($url);
		$this->assertSame('copies', $fileSaved->folder);
		$this->assertSame('name_(1).jpg', $fileSaved->name);
		$this->assertSame('public', $fileSaved->disk);
		$this->assertSame(1, $this->user1->files()->count());
	}

	/**
	 * @group move
	 */
	public function testMoveToModel() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$this->assertSame(1, $this->user1->files()->count());

		$fileSaved->moveToModel($this->user2);

		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame(0, $this->user1->files()->count());
		$this->assertSame(1, $this->user2->files()->count());
	}

	public function testMoveToModelWithOptions() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$this->assertSame(1, $this->user1->files()->count());

		$fileSaved->moveToModel($this->user2, [
			"folder" => "moves",
			"disk"   => "private",
			"name"   => "avatar",
		]);

		$this->assertSame('avatar.jpg', $fileSaved->name);
		$this->assertSame('private', $fileSaved->disk);
		$this->assertSame('moves', $fileSaved->folder);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame(0, $this->user1->files()->count());
		$this->assertSame(1, $this->user2->files()->count());
	}


	public function testMoveManyFiles() {

	}

	public function testMoveFilesWithOptions() {

	}

	public function testMoveFilesWithSameName() {

	}

}