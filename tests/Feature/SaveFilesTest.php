<?php

namespace Fboseca\Filesmanager\Tests;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SaveFilesTest extends TestCase {


	public function setUp(): void {
		parent::setUp();
	}

	public function testSaveFile() {
		$file = UploadedFile::fake()->image('avatar.jpg');

		//without parameters
		$fileSaved = $this->user1->addFile($file);

		//file exists
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		//file parameters are correct
		$this->assertSame('files', $fileSaved->folder);
		$this->assertSame('img', config($this->configFile . '.extensions.jpg'));
		$this->assertSame('jpg', $fileSaved->file_extension);
		$this->assertSame('public', $fileSaved->disk);
		$this->assertSame('local', $fileSaved->driver);
		$this->assertSame('', $fileSaved->description);
		$this->assertSame('', $fileSaved->group);

		//with parameters
		$file = UploadedFile::fake()->image('avatar.doc');
		$fileSaved = $this->user1->addFile($file, "gallery", "my Name File", "A description for a file");

		//file exists
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		//file parameters are correct
		$this->assertSame('files', $fileSaved->folder);
		$this->assertSame('my_name_file.doc', $fileSaved->name);
		$this->assertSame('word', config($this->configFile . '.extensions.doc'));
		$this->assertSame('doc', $fileSaved->file_extension);
		$this->assertSame('public', $fileSaved->disk);
		$this->assertSame('local', $fileSaved->driver);
		$this->assertSame('A description for a file', $fileSaved->description);
		$this->assertSame('gallery', $fileSaved->group);
		//without extension

		$file = UploadedFile::fake()->create('name');
		$fileSaved = $this->user1->addFile($file, "gallery", "my Name File", "A description for a file");

		//file exists
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		//file parameters are correct
		$this->assertSame('files', $fileSaved->folder);
		$this->assertSame('my_name_file.txt', $fileSaved->name);
		$this->assertSame('file', config($this->configFile . '.extensions.*'));
		$this->assertSame('txt', $fileSaved->file_extension);
		$this->assertSame('public', $fileSaved->disk);
		$this->assertSame('local', $fileSaved->driver);
		$this->assertSame('A description for a file', $fileSaved->description);
		$this->assertSame('gallery', $fileSaved->group);
	}

	public function testSaveLogo() {
		//save logo
		$file = UploadedFile::fake()->image('avatar.png', 100);
		$fileSaved = $this->user1->setLogo($file, 'logo2');
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
	}

	public function testPrivateFiles() {
		$file = UploadedFile::fake()->image('avatar.png', 100);
		$fileSaved = $this->user1->disk('private')->addFile($file, 'probando');
		echo $fileSaved->disk;
		echo $fileSaved->driver;
		$this->assertSame('kljlkj', $fileSaved->forceSrc);
		echo $fileSaved->forceSrc;
		echo $fileSaved->src;
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
	}

	public function testSaveOnNewFolder() {
		//change folder

	}

	public function testDeleteFile() {

	}

	public function testCopyFile() {

	}

	public function testDownloadFile() {

	}

	public function testSaveFtp() {

	}
}