<?php

namespace Fboseca\Filesmanager\Managers;

use Fboseca\Filesmanager\Models\FileManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ZipArchive;

class ZipFileManager {

	private $files;
	private $model;
	private $name;
	private $routeTemp;

	/**
	 * ZipFileManager constructor.
	 * @param $files
	 */
	public function __construct(Collection $files) {
		$this->files = $files;
		if ($this->isZipeable()) {
			$this->model = $files->first()->filesable;
			$this->init();
		}
	}

	public function init() {
		$nameTemp = $this->generateName() . ".zip";
		$this->routeTemp = FileManager::createTempFile($nameTemp);
		$this->name = $nameTemp;

		// Initializing PHP class
		$zip = new ZipArchive();
		$zip->open($this->routeTemp, ZipArchive::OVERWRITE);

		foreach ($this->files as $file) {
			$zip->addFromString($file->name, $file->getContent);
		}

		$zip->close();
	}

	public function download($name = '', $deleteFileTempAfterSend = true) {
		if ($this->isZipeable()) {
			$name = $name ?: $this->name;
			return response()->download($this->routeTemp, $this->nameWitExtension($name))->deleteFileAfterSend($deleteFileTempAfterSend);
		}
	}

	public function save($name = "", $group = '', $description = '') {
		if ($this->isZipeable()) {
			$name = $name ?: $this->name;
			return $this->model->addFileFromSource($this->routeTemp, $group, $this->nameWitExtension($name, false), $description);
		} else {
			return null;
		}
	}

	public function close() {
		FileManager::removeTempFile($this->name);
	}

	public function folder($folder) {
		$this->model->folder($folder);
		return $this;
	}

	public function disk($disk) {
		$this->model->disk($disk);
		return $this;
	}

	public function model($model) {
		$this->model = $model;
		return $this;
	}

	public function addFiles(Collection $files) {
		if ($files->count()) {
			$this->files = $this->files->merge($files);
			$this->close();
			$this->init();
		}
		return $this;
	}

	private function nameWitExtension($name, $extension = true): string {
		$aux = str_replace('.zip', '', $name);
		if ($extension) {
			$aux .= ".zip";
		}
		return $aux;
	}

	private function generateName(): string {
		return Str::random(10);
	}

	private function isZipeable(): bool {
		return $this->files->count();
	}
}