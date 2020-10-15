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

		$fileSaved = $this->user1->addFile($file);
		Storage::disk($fileSaved->disk)->assertExists($fileSaved->url);
		$this->assertSame('files', $fileSaved->folder);
		$this->assertSame('img', config($this->configFile . '.extensions.jpg'));
		$this->assertSame('jpg', $fileSaved->file_extension);
		$this->assertSame('public', $fileSaved->disk);
		$this->assertSame('local', $fileSaved->driver);
		$this->assertSame('', $fileSaved->description);
		$this->assertSame('', $fileSaved->group);
	}

	public function testSaveFileWithParameters() {
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
	}

	public function testSaveFileWithoutExtension() {
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

	public function testSaveFilesWithSameName() {
		$file = UploadedFile::fake()->create('name.pdf');
		$file1 = $this->user1->addFile($file, null, "my Name File");
		$file2 = $this->user1->addFile($file, 'copies', "my Name File");

		$this->assertSame('my_name_file.pdf', $file1->name);
		$this->assertSame('', $file1->group);
		$this->assertSame('my_name_file_(1).pdf', $file2->name);
		$this->assertSame('copies', $file2->group);
	}

	public function testSaveLogo() {
		//save logo
		$file = UploadedFile::fake()->image('avatar.png', 100);
		$this->user1->setLogo($file, 'logo2');
		$response = $this->get($this->user1->logo->src);
		$response->assertStatus(200);
	}

	public function testSaveOnNewFolder() {
		$file2 = UploadedFile::fake()->create('document.pdf');
		//change folder
		$fileSaved = $this->user1->folder('testing/files')->addFile($file2);

		Storage::disk($fileSaved->disk)->assertExists('testing/files/' . $fileSaved->name);
		$this->assertSame('testing/files', $fileSaved->folder);
	}

	public function testSaveOnNewDisk() {
		$file2 = UploadedFile::fake()->create('document.pdf');
		//change folder
		$fileSaved = $this->user1->disk('private')->addFile($file2);

		Storage::disk('private')->assertExists($fileSaved->url);
	}

	public function testSaveOnNewDiskAndFolder() {
		$file2 = UploadedFile::fake()->create('document.pdf');
		//change folder
		$fileSaved = $this->user1->disk('private')->folder('testing/files')->addFile($file2);

		Storage::disk('private')->assertExists('testing/files/' . $fileSaved->name);
		$this->assertSame('testing/files', $fileSaved->folder);
	}

	public function testSavePrivateFiles() {
		$file = UploadedFile::fake()->image('avatar.png', 100);
		$file2 = UploadedFile::fake()->create('document.pdf');
		$this->user1->disk('private')->addFile($file, 'testing', 'test');
		$this->user1->disk('private')->addFile($file2);
		foreach ($this->user1->files as $file) {
			$response = $this->get($file->forceSrc);
			$response->assertStatus(200);
			$this->assertStringContainsString('private/file', $file->forceSrc);
		}
	}

	public function testSaveFileFromPath() {
		$file = UploadedFile::fake()->image('avatar.png', 100);
		$file2 = $this->user1->addFileFromPath($file->path(), 'testing', 'test');
		Storage::disk($file2->disk)->assertExists($file2->url);
		$this->assertSame('test.tmp', $file2->name);
		$this->assertSame('testing', $file2->group);
	}

	public function testSaveFileWithContent() {
		$file = UploadedFile::fake()->image('avatar.png', 100);
		$file2 = $this->user1->addFileWithContent($file->getContent(), 'png', 'testing', 'test');
		Storage::disk($file2->disk)->assertExists($file2->url);
		$this->assertSame('test.png', $file2->name);
		$this->assertSame('testing', $file2->group);

		$file3 = $this->user1->addFileWithContent('hello world', 'txt', 'testing', 'test');
		Storage::disk($file3->disk)->assertExists($file3->url);
		$this->assertSame('test.txt', $file3->name);
		$this->assertSame('testing', $file3->group);
		$this->assertStringContainsString($file3->getContent, 'hello world');
	}
}