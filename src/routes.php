<?php

use Fboseca\Filesmanager\Models\FileManager;

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


Route::get('/', function () {
	return 'holi';
});

Route::get('/storage/{disk}/{route}', function ($disk, $route) {
	return response()->file(\Storage::disk($disk)->path($route));
})->where([ 'route' => '[\w\./]*' ]);