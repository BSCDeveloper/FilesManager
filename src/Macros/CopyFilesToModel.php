<?php

namespace Fboseca\Filesmanager\Macros;


class CopyFilesToModel {
	public function __invoke() {
		return function ($model, $options = []) {
			$newFiles = collect();
			$this->each(function ($file) use ($options, $newFiles, $model) {
				$newFiles->add($file->copyToModel($model, $options));
			});
			return $newFiles;
		};
	}

}