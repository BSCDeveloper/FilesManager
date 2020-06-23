<?php

use Fboseca\Filesmanager\Models\FileManager;


/*Route::get('custom/files/{has}', function ($has) {
	return \Redirect::route('file.manager.private', $has);
});

Route::get('custom/download/{has}', function ($has) {
	return \Redirect::route('file.manager.private', $has);
});*/

/**
 * Route for get private files
 */
Route::get('private/file/{has}', function ($has) {
	$file = FileManager::find(FileManager::decryptId($has));
	if ($file && \Storage::disk($file->disk)->exists($file->url)) {
		return response()->file(\Storage::disk($file->disk)->path($file->url));
	}
	abort(404);
})->name('file.manager.private');

/**
 * Route for downloads files
 */
Route::get('download/file/{has}', function ($has) {
	$file = FileManager::find(FileManager::decryptId($has));
	if ($file) {
		return $file->download();
	}
	abort(404);
})->name('download.file');