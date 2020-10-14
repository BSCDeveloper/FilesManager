<?php

namespace Fboseca\Filesmanager\Macros;


use Fboseca\Filesmanager\Managers\ZipFileManager;

/**
 * Convert files on zip file
 * Class ToZipFile
 * @package Fboseca\Filesmanager\Macros
 */
class ToZipFile {
	public function __invoke() {
		return function () {
			return new ZipFileManager($this);
		};
	}
}