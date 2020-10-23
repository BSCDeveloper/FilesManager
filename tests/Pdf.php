<?php

namespace Fboseca\Filesmanager\Tests;


use Fboseca\Filesmanager\Models\FileManager;
use Fboseca\Filesmanager\scopes\TypeFileScope;

class Pdf extends FileManager {
	public $type_file = 'pdf';

	protected static function boot() {
		parent::boot();
		static::addGlobalScope(new TypeFileScope());
	}
}