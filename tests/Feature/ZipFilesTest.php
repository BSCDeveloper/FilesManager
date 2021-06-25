<?php

namespace Fboseca\Filesmanager\Tests;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ZipFilesTest extends TestCase {
	public $file;

	public function setUp(): void {
		parent::setUp();
	}

	private function initUser1() {
		$this->file = UploadedFile::fake()->create('document.pdf');
		$this->user1->addFile($this->file);
		$this->user1->addFile($this->file);
		$this->user1->addFile($this->file);
	}

	private function initUser2() {
		$file = UploadedFile::fake()->create('document2.pdf');
		$this->user2->addFile($file);
		$this->user2->addFile($file);
		$this->user2->addFile($file);
	}

	/**
	 * @group zip
	 */
	public function testFilesToZip() {
		$this->initUser1();
		$this->assertSame(3, $this->user1->files()->count());

		//save zip
		$zip = $this->user1->files->toZipFile();
		$this->assertSame(3, $zip->getFiles()->count());
		$fileZip = $zip->save();
		$this->assertSame(4, $this->user1->files()->count());
		Storage::disk($fileZip->disk)->assertExists($fileZip->url);

		//save with name group and description
		$fileZip = $zip->save('myZipFiles', 'zipes', 'Description');
		$this->assertSame('myzipfiles.zip', $fileZip->name);
		$this->assertSame('Description', $fileZip->description);
		$this->assertSame('zipes', $fileZip->group);
		$this->assertSame(5, $this->user1->files()->count());
		Storage::disk($fileZip->disk)->assertExists($fileZip->url);

		//add more files
		$newFile = $this->user1->addFile($this->file);
		$this->assertSame(3, $zip->getFiles()->count());
		$zip->addFiles($this->user1->files()->withNotType('pdf')->get());
		$zip->addFile($newFile);
		$this->assertSame(6, $zip->getFiles()->count());
		$fileZip2 = $zip->save();
		$this->assertSame(7, $this->user1->files()->count());
		Storage::disk($fileZip2->disk)->assertExists($fileZip2->url);
		$this->assertGreaterThan($fileZip2->size, $fileZip->size);
	}

	/**
	 * @group zip
	 */
	public function testFileChangeFolderDiskModel() {
		$this->initUser1();
		$this->initUser2();
		$zip = $this->user1->files->toZipFile();
		//save zip on distinct model, folder and disk
		$fileZip = $zip->model($this->user2)->disk('private')->folder("new/folder")->save();
		Storage::disk($fileZip->disk)->assertExists($fileZip->url);
		$this->assertSame(3, $this->user1->files()->count());
		$this->assertSame(4, $this->user2->files()->count());
		$this->assertSame('private', $fileZip->disk);
		$this->assertSame('new/folder', $fileZip->folder);

		//add new files
		$this->assertSame(3, $zip->getFiles()->count());
		$zip->addFiles($this->user2->files);
		$this->assertSame(7, $zip->getFiles()->count());
		//save zip with name, group and description
		$fileZip = $zip->save('myImages', 'forDownloads', 'Desc');
		$this->assertSame('myimages.zip', $fileZip->name);
		$this->assertSame('Desc', $fileZip->description);
		$this->assertSame('forDownloads', $fileZip->group);
		$this->assertSame('private', $fileZip->disk);
		$this->assertSame('new/folder', $fileZip->folder);
		$this->assertSame(3, $this->user1->files()->count());
		$this->assertSame(5, $this->user2->files()->count());
	}

	/**
	 * @group zip
	 */
	public function testDownloadZip() {
		$this->initUser1();
		$zip = $this->user1->files->toZipFile();
		$response = $zip->download('test');

		$this->assertStringContainsString('filename=test.zip', $response);
		$this->assertStringContainsString('200 OK', $response);
	}
}