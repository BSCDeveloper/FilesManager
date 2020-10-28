<?php

namespace Fboseca\Filesmanager\Tests;


use Illuminate\Http\UploadedFile;

class GetFilesTest extends TestCase {

	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * @group getters
	 */
	public function testGetSrcImages() {
		$file = UploadedFile::fake()->image('avatar.png', 100);
		$file1 = $this->user1->addFile($file);
		$file2 = $this->user1->disk('private')->addFile($file);

		$response = $this->get($file1->src);
		$response->assertStatus(200);

		$response = $this->get($file2->forceSrc);
		$response->assertStatus(200);
		$this->assertStringContainsString('private/file', $file2->forceSrc);
	}

	/**
	 * @group getters
	 */
	public function testGetContentOfFile() {
		$file = UploadedFile::fake()->createWithContent('document.pdf', 'hello world');
		$file1 = $this->user1->addFile($file);
		$this->assertStringContainsString($file1->getContent, 'hello world');
	}

	/**
	 * @group getters
	 */
	public function testModifyContentOfFile() {
		$file = UploadedFile::fake()->createWithContent('document.pdf', 'hello world');
		$file1 = $this->user1->addFile($file);

		//append
		$before = $file1->size;
		$file1->append(' Appended text');
		$this->assertGreaterThan($before, $file1->size);
		$this->assertStringContainsString('Appended text', $file1->getContent);

		//prepend
		$before = $file1->size;
		$file1->prepend('Prepend text ');
		$this->assertGreaterThan($before, $file1->size);
		$this->assertStringContainsString('Prepend text', $file1->getContent);
	}

	/**
	 * @group getters
	 */
	public function testExistFile() {
		$file = UploadedFile::fake()->createWithContent('document.pdf', 'hello world');
		$this->user1->addFile($file, [
			"name" => "name"
		]);
		$this->assertTrue($this->user1->existsFile('name.pdf'));
	}

	/**
	 * @group getters
	 */
	public function testGetFolderAndDisk() {
		$this->assertSame('files', $this->user1->getFolder());
		$this->assertSame('public', $this->user1->getDisk());
		$this->user1->disk('s3')->folder("/users/1/documents");
		$this->assertSame('/users/1/documents', $this->user1->getFolder());
		$this->assertSame('s3', $this->user1->getDisk());
	}

	/**
	 * @group getters
	 */
	public function testScopesFiles() {
		$file = UploadedFile::fake()->create('document.pdf');
		$image = UploadedFile::fake()->image('avatar.jpg');
		$this->user1->addFile($file, [
			"group" => "upload",
			"name"  => "name"
		]);
		$this->user1->addFile($file, [
			"name" => "name"
		]);
		$this->user1->addFile($image, [
			"group" => "gallery",
			"name"  => "avatar"
		]);
		$this->user1->addFile($image, [
			"group" => "gallery",
			"name"  => "avatar"
		]);
		$this->assertSame(1, $this->user1->files()->withGroup('upload')->count());
		$this->assertSame(2, $this->user1->files()->withGroup('gallery')->count());
		$this->assertSame(3, $this->user1->files()->withNotGroup('upload')->count());
		$this->assertSame(2, $this->user1->files()->withType('pdf')->count());
		$this->assertSame(2, $this->user1->files()->withNotGroup('upload')->withType('img')->count());
		$this->assertSame(2, $this->user1->files()->withNotType('img')->count());
		$this->assertSame(1, $this->user1->files()->withNotType('img')->withNotGroup('upload')->count());
	}

	/**
	 * @group getters
	 */
	public function testGetFilesWithScopeGlobal() {
		$file = UploadedFile::fake()->create('document.pdf');
		$this->user1->addFile($file, [
			"group" => "upload",
			"name"  => "name"
		]);
		$this->assertSame(1, $this->user1->pdfs()->count());
		$this->assertSame(1, $this->user1->pdfs()->withGroup('upload')->count());
		$this->assertSame(0, $this->user1->pdfs()->withGroup('gallery')->count());
	}
}