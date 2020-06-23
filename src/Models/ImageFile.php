<?php

namespace Fboseca\Filesmanager\Models;

use Fboseca\Filesmanager\scopes\TypeFileScope;

class ImageFile extends FileManager {

	public $type_file = 'img';

	/**
	 * ImageFile constructor.
	 * @param array $attributes
	 */
	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
	}


	protected static function boot() {
		parent::boot();
		static::addGlobalScope(new TypeFileScope());
	}
}