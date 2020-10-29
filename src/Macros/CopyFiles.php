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
			$this->each(function ($file) use ($options) {
				$file->copy($options);
			});
			return $this;
		};
	}
}