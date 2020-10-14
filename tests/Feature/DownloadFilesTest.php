<?php

namespace Fboseca\Filesmanager\Tests;


class DownloadFilesTest extends TestCase {
	public function setUp(): void {
		parent::setUp();
	}

	public function testDownloadFile() {
		$this->app['router']->get('myNameOfPathDownloadFiles/{has}', function ($has) {
			return \Redirect::route('download.file', $has);
		})->name('download.route');
		$this->app['config']->set('filemanager.symbolic_link_download_files', 'download.route');
	}
}