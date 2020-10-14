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
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
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

		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame('files', $fileSaved->folder);
		$this->assertSame('my_name_file.doc', $fileSaved->name);
		$this->assertSame('word', config($this->configFile . '.extensions.doc'));
		$this->assertSame('doc', $fileSaved->file_extension);
		$this->assertSame('public', $fileSaved->disk);
		$this->assertSame('local', $fileSaved->driver);
		$this->assertSame('A description for a file', $fileSaved->description);
		$this->assertSame('gallery', $fileSaved->group);


		//without file extension
		$file = UploadedFile::fake()->create('name');
		$fileSaved = $this->user1->addFile($file, "gallery", "my Name File", "A description for a file");

		//the extension of file must be txt
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
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
		$this->user1->setLogo($file, 'logo2');
		$response = $this->get($this->user1->logo->src);
		$response->assertStatus(200);
	}

	public function testSavePrivateFiles() {
		$file = UploadedFile::fake()->image('avatar.png', 100);
		$file2 = UploadedFile::fake()->create('document.pdf');
		$this->user1->disk('private')->addFile($file, 'probando', 'prueba');
		$this->user1->disk('private')->addFile($file2);
		foreach ($this->user1->files as $file) {
			$response = $this->get($file->forceSrc);
			$response->assertStatus(200);
			$this->assertStringContainsString('private/file', $file->forceSrc);
		}
	}

	public function testSaveOnNewFolder() {
		$file2 = UploadedFile::fake()->create('document.pdf');
		//change folder
		$fileSaved = $this->user1->folder('testing/files')->addFile($file2);

		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame('testing/files', $fileSaved->folder);
	}

	public function testSaveFileOnFtp() {

	}

	public function testSaveFilesWithSameName() {

	}
}