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

	/**
	 * @group move
	 */
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

	/**
	 * @group move
	 */
	public function testMoveManyFiles() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$this->user1->addFile($file);
		$this->user1->addFile($file);
		$urlBefore = collect();
		foreach ($this->user1->images as $file) {
			Storage::disk($file->disk)->assertExists($file->url);
			$this->assertSame('files', $file->folder);
			$this->assertSame('public', $file->disk);
			$urlBefore->push($file->url);
		}

		$imagesMoved = $this->user1->images->moveFiles('copies');
		foreach ($imagesMoved as $file) {
			Storage::disk($file->disk)->assertExists($file->url);
			$this->assertSame('copies', $file->folder);
		}
		$this->assertSame(2, $this->user1->files()->count());
		foreach ($urlBefore as $url) {
			Storage::disk($file->disk)->assertMissing($url);
		}
	}

	/**
	 * @group move
	 */
	public function testMoveFilesWithOptions() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$this->user1->addFile($file);
		$this->user1->addFile($file);
		$urlBefore = collect();
		foreach ($this->user1->images as $file) {
			Storage::disk($file->disk)->assertExists($file->url);
			$this->assertSame('files', $file->folder);
			$this->assertSame('public', $file->disk);
			$urlBefore->push($file->url);
		}

		$imagesMoved = $this->user1->images->moveFiles('copies', [
			"group"       => "gallery",
			"description" => "A description for a file",
			"disk"        => 'private'
		]);
		foreach ($imagesMoved as $file) {
			Storage::disk($file->disk)->assertExists($file->url);
			$this->assertSame('copies', $file->folder);
			$this->assertSame('gallery', $file->group);
			$this->assertSame("A description for a file", $file->description);
			$this->assertSame("private", $file->disk);
		}
		$this->assertSame(2, $this->user1->files()->count());
		foreach ($urlBefore as $url) {
			Storage::disk($file->disk)->assertMissing($url);
		}
	}

	/**
	 * @group move
	 */
	public function testMoveFilesWithSameName() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$this->user1->addFile($file);
		$this->user1->addFile($file);
		$urlBefore = collect();
		foreach ($this->user1->images as $file) {
			Storage::disk($file->disk)->assertExists($file->url);
			$this->assertSame('files', $file->folder);
			$this->assertSame('public', $file->disk);
			$urlBefore->push($file->url);
		}

		$imagesMoved = $this->user1->images->moveFiles('copies', [
			"name" => "image",
		]);
		$number = 0;
		foreach ($imagesMoved as $file) {
			Storage::disk($file->disk)->assertExists($file->url);
			$duplicated = $number ? "_($number)" : '';
			$this->assertSame("image$duplicated.jpg", $file->name);
			$number++;
		}
		$this->assertSame(2, $this->user1->files()->count());
		foreach ($urlBefore as $url) {
			Storage::disk($file->disk)->assertMissing($url);
		}
	}

	/**
	 * @group move
	 */
	public function testMoveManyFilesToModelWithOptions() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$this->user1->addFile($file);
		$this->user1->addFile($file);
		$this->assertSame(2, $this->user1->files()->count());

		$files = $this->user1->files->moveFilesToModel($this->user2, [
			"folder"      => "moves",
			"disk"        => "private",
			"name"        => "avatar",
			"group"       => 'gallery',
			"description" => 'A description of file'
		]);

		$this->assertSame(0, $this->user1->files()->count());
		$this->assertSame(2, $this->user2->files()->count());

		$number = 0;
		foreach ($files as $fileMoved) {
			$duplicated = $number ? "_($number)" : '';
			$this->assertSame("avatar$duplicated.jpg", $fileMoved->name);
			$this->assertSame('private', $fileMoved->disk);
			$this->assertSame('moves', $fileMoved->folder);
			$this->assertSame('gallery', $fileMoved->group);
			$this->assertSame('A description of file', $fileMoved->description);
			Storage::disk($fileMoved->disk)->assertExists($fileMoved->url);
			$number++;
		}
	}
}