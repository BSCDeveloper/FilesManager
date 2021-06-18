<?php

namespace Fboseca\Filesmanager\Managers;

use Fboseca\Filesmanager\Models\FileManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ZipArchive;

class ZipFileManager {

	private $files; //list of files
	private $model; //model to use
	private $folder; //folder to save
	private $disk; //disk to save
	private $name; //name of file
	private $routeTemp; //route temporary

	/**
	 * ZipFileManager constructor.
	 * @param $files
	 */
	public function __construct(Collection $files) {
		$this->files = $files;
		if ($this->isZipeable()) {
			//by default get a first model from files
			$this->model = $files->first()->filesable;
			$this->init();
		}
	}

	/**
	 * Init the class and add a files
	 */
	public function init() {
		//create a temporary file
		$nameTemp = $this->generateName() . ".zip";
		$this->routeTemp = FileManager::createTempFile($nameTemp);
		$this->name = $nameTemp;

		//open the file temporary as zipArchive
		$zip = new ZipArchive();
		$zip->open($this->routeTemp, ZipArchive::OVERWRITE);

		//add files from the list
		foreach ($this->files as $file) {
			$zip->addFromString($file->name, $file->getContent);
		}

		$zip->close();
	}

	/**
	 * Download the file
	 * @param string $name
	 * @param bool   $deleteFileTempAfterSend
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 */
	public function download($name = '', $deleteFileTempAfterSend = true) {
		if ($this->isZipeable()) {
			$name = $name ?: $this->name;
			return response()->download($this->routeTemp, $this->nameWitExtension($name))->deleteFileAfterSend($deleteFileTempAfterSend);
		}
	}

	/**
	 * Save the file in database and platform
	 * @param string $name
	 * @param string $group
	 * @param string $description
	 * @return |null
	 */
	public function save($name = "", $group = '', $description = '') {
		if ($this->isZipeable()) {
			$name = $name ?: $this->name;
			if ($this->folder) {
				$this->model->folder($this->folder);
			}
			if ($this->disk) {
				$this->model->disk($this->disk);
			}
			//save a file zip
			return $this->model->addFileFromPath($this->routeTemp, [
				"group"       => $group,
				"description" => $description,
				"name"        => $this->nameWitExtension($name)
			]);
		} else {
			return null;
		}
	}

	/**
	 * Remove the temporary file
	 */
	public function close() {
		FileManager::removeTempFile($this->name);
	}

	/**
	 * Change folder to save the file
	 * @param $folder
	 * @return $this
	 */
	public function folder($folder) {
		$this->folder = $folder;
		return $this;
	}

	/**
	 * Change the disk to save file
	 * @param $disk
	 * @return $this
	 */
	public function disk($disk) {
		$this->disk = $disk;
		return $this;
	}

	/**
	 * Change the model to attach this zip file
	 * @param $model
	 * @return $this
	 */
	public function model($model) {
		$this->model = $model;
		return $this;
	}

	/**
	 * Add more files to
	 * @param Collection $files
	 * @return $this
	 */
	public function addFiles(Collection $files) {
		if ($files->count()) {
			$this->files = $this->files->merge($files);
			$this->close(); //destroy last file
			$this->init(); //create a new zip file
		}
		return $this;
	}

	/**
	 * Add one file to zip file
	 * @param $file
	 * @return $this
	 */
	public function addFile($file) {
		return $this->addFiles(collect([ $file ]));
	}

	/**
	 * @return Collection
	 */
	public function getFiles(): Collection {
		return $this->files;
	}

	/**
	 * return the name file with finish '.zip'
	 * @param $name
	 * @return string
	 */
	private function nameWitExtension($name): string {
		return Str::finish($name, ".zip");
	}

	/**
	 * Generate a random name
	 * @return string
	 */
	private function generateName(): string {
		return Str::random(10);
	}

	/**
	 * return true if zip file has files inside
	 * @return bool
	 */
	private function isZipeable(): bool {
		return $this->files->count();
	}


}