<?php

namespace Fboseca\Filesmanager\Tests;


use Illuminate\Http\UploadedFile;

class DownloadFilesTest extends TestCase {
	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * @group download
	 */
	public function testDownloadAFile() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$response = $fileSaved->download();
		$res = $this->createTestResponse($response);
		$res->assertHeader('Content-Type', $fileSaved->mime_type);
		$res->assertHeader('filename', $fileSaved->name);
		$res->assertOk();
	}

	/**
	 * @group download
	 */
	public function testDownloadFileWithName() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$response = $fileSaved->download('testing');
		$res = $this->createTestResponse($response);
		$res->assertHeader('Content-Type', $fileSaved->mime_type);
		$res->assertHeader('filename', 'testing.jpg');
		$res->assertOk();
	}

	/**
	 * @group download
	 */
	public function testDownloadSrc() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->addFile($file);
		$this->assertStringContainsString('download/file', $fileSaved->downloadSrc);
		$response = $this->get($fileSaved->downloadSrc);

		$res = $this->createTestResponse($response);
		$res->assertHeader('Content-Type', $fileSaved->mime_type);
		$res->assertHeader('filename', $fileSaved->name);
		$res->assertOk();
	}

	/**
	 * @group download
	 */
	public function testDownloadForceSrc() {
		$file = UploadedFile::fake()->image('avatar.jpg');
		$fileSaved = $this->user1->disk('private')->addFile($file);
		$this->assertSame('', $fileSaved->downloadSrc);
		$this->assertStringContainsString('download/file', $fileSaved->forceDownloadSrc);

		$response = $this->get($fileSaved->forceDownloadSrc);
		$res = $this->createTestResponse($response);
		$res->assertHeader('Content-Type', $fileSaved->mime_type);
		$res->assertHeader('filename', $fileSaved->name);
		$res->assertOk();
	}
}