<?php

namespace Fboseca\Filesmanager\Macros;


/**
 * To delete files from bbdd and platform
 * Class RemoveFiles
 * @package Fboseca\Filesmanager\Macros
 */
class RemoveFiles {
	public function __invoke() {
		return function () {
			$this->each(function ($file) {
				$file->delete();
			});
			return $this;
		};
	}
}