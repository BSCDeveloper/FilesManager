<?php


namespace Fboseca\Filesmanager\Macros;

/**
 * To move many files
 * Class MoveFiles
 * @package Fboseca\Filesmanager\Macros
 */
class MoveFiles {
	public function __invoke() {
		return function ($folder, $options = []) {
			$this->each(function ($file) use ($options, $folder) {
				$file->move($folder, $options);
			});
			return $this;
		};
	}
}