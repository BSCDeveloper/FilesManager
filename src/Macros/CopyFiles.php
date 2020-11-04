<?php

namespace Fboseca\Filesmanager\Macros;

/**
 * For copy many files
 * Class CopyFiles
 * @package Fboseca\Filesmanager\Macros
 */
class CopyFiles {
	public function __invoke() {
		return function ($options = []) {
			$newFiles = collect();
			$this->each(function ($file) use ($options, $newFiles) {
				$newFiles->add($file->copy($options));
			});
			return $newFiles;
		};
	}
}