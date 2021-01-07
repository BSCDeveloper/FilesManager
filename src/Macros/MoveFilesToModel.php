<?php


namespace Fboseca\Filesmanager\Macros;

/**
 * Class MoveFilesToModel
 * @package Fboseca\Filesmanager\Macros
 */
class MoveFilesToModel {
	public function __invoke() {
		return function ($model, $options = []) {
			$this->each(function ($file) use ($options, $model) {
				$file->moveToModel($model, $options);
			});
			return $this;
		};
	}
}