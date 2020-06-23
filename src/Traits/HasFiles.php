<?php

namespace Fboseca\Filesmanager\Traits;

use Fboseca\Filesmanager\Models\FileManager;
use Fboseca\Filesmanager\Models\ImageFile;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;

trait HasFiles {

	public $FILE_FOLDER_DEFAULT = '';
	public $FILE_DISK_DEFAULT = '';

	//region variables
	/*
	|--------------------------------------------------------------------------
	| variables
	|--------------------------------------------------------------------------
	|
	*/
	private $FOLDER = '';
	private $ENVIRONMENT = '';
	private $DISK = '';
	private $CONFIG_DISK = '';
	private $customVariables = false;
	//endregion

	//region public
	/*
	|--------------------------------------------------------------------------
	| public methods
	|--------------------------------------------------------------------------
	|
	*/

	public function fileCustomVariables() {
	}

	public function addFile(UploadedFile $file, $group = '', $name = "", $description = '') {
		//init variables
		$this->initVariables();
		$file = FileManager::createFile(
			$file,
			$this->DISK,
			$this->FOLDER,
			$this->ENVIRONMENT,
			$group,
			$name,
			$description
		);
		$this->files()->save($file);
		return $file;
	}

	public function addFileFromSource($source, $group = '', $name = "", $description = '') {
		$file = new UploadedFile($source, basename($source));
		return $this->addFile($file, $group, $name, $description);
	}

	public function addFileFromContent($content, $extension, $group = '', $name = "", $description = '') {
		$nameFileTemp = FileManager::generateName() . ".$extension";
		$source = FileManager::createTempFile($nameFileTemp, $content);
		$file = $this->addFileFromSource($source, $group, $name, $description);
		FileManager::removeTempFile($nameFileTemp);
		return $file;
	}

	public function setLogo(UploadedFile $file, $name = "", $description = '') {
		if ($this->logo) {
			$this->logo->delete();
		}
		$group = 'logo';
		return $this->addFile($file, $group, $name, $description);
	}

	//endregion

	//getters
	public function exists($name): bool {
		return FileManager::checkIfFileExist($this->FOLDER, $this->DISK, $name);
	}

	public function getFolder() {
		$this->initCustomVariables();
		return $this->FOLDER;
	}

	public function getDisk() {
		$this->initCustomVariables();
		return $this->DISK;
	}

	//setters
	public function disk($disk) {
		$this->initCustomVariables();
		$this->FILE_DISK_DEFAULT = $disk;
		return $this;
	}

	public function folder($folder) {
		$this->initCustomVariables();
		$this->FILE_FOLDER_DEFAULT = $folder;
		return $this;
	}

	//region private
	/*
	|--------------------------------------------------------------------------
	| private methods
	|--------------------------------------------------------------------------
	|
	*/

	private function secureFolder($url): string {
		return trim($url, '/');
	}

	private function initCustomVariables() {
		if (!$this->customVariables) {
			$this->fileCustomVariables();
			$this->customVariables = true;
		}
	}

	private function initVariables() {
		$this->initCustomVariables();

		$this->FOLDER = $this->secureFolder($this->FILE_FOLDER_DEFAULT ?: config('filemanager.folder_default'));
		$this->DISK = $this->FILE_DISK_DEFAULT ?: config('filemanager.disk_default');
		$this->CONFIG_DISK = config("filesystems.disks.$this->DISK");
		//get environment of disk or default config
		$environment = config("filesystems.disks.$this->DISK.visibility");
		$this->ENVIRONMENT = $environment == FileManager::ENVIRONMENT_PUBLIC;
	}

	//endregion

	/** Relationship **/
	/**
	 * Get all files of model
	 * @return MorphMany
	 */
	public function files() {
		return $this->morphMany(FileManager::class, 'filesable');
	}

	public function images() {
		return $this->morphMany(ImageFile::class, 'filesable');
	}

	public function getLogoAttribute() {
		return $this->files()->ofGroup('logo')->first();
	}
}