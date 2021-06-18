<?php

namespace Fboseca\Filesmanager\Traits;

use Fboseca\Filesmanager\Models\FileManager;
use Fboseca\Filesmanager\Models\ImageFile;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;

trait HasFiles {

	public $FILE_FOLDER_DEFAULT = '';
	public $FILE_DISK_DEFAULT = '';

	/*
	|--------------------------------------------------------------------------
	| variables
	|--------------------------------------------------------------------------
	|
	*/
	private $FOLDER = ''; //folder to save files
	private $DISK = ''; //disk to save files
	private $ENVIRONMENT = ''; //true is public environment and false is private
	private $customVariablesStarted = false; //for initialize variables only once
	private $GROUP_LOGO = 'logo';

	/*
	|--------------------------------------------------------------------------
	| public methods
	|--------------------------------------------------------------------------
	|
	*/

	/**
	 * For overwrite, this funcion is used for change the folder and disk
	 * defaults.
	 */
	public function fileCustomVariables() {
		//overwritten for the user
	}

	/**
	 * Attach file to model
	 * @param UploadedFile $file
	 * @param array        $options
	 * @return FileManager|UploadedFile
	 */
	public function addFile(UploadedFile $file, $options = []) {

		//init variables
		$this->initVariables();
		$file = FileManager::createFile(
			$file,
			!empty($options["disk"]) ? $options["disk"] : $this->DISK,
			!empty($options["folder"]) ? $options["folder"] : $this->FOLDER,
			$this->ENVIRONMENT,
			$options
		);
		$this->files()->save($file);
		return $file;
	}

	/**
	 * Attach a file from a path
	 * @param        $path path of a file
	 * @param array  $options
	 * @return FileManager|UploadedFile
	 */
	public function addFileFromPath($path, $options = []) {
		$file = new UploadedFile($path, basename($path));
		return $this->addFile($file, $options);
	}

	/**
	 * Attach a file by passing it content
	 * @param        $content content from file
	 * @param        $extension
	 * @param array  $options
	 * @return FileManager|UploadedFile
	 */
	public function addFileWithContent($content, $extension, $options = []) {
		$nameFileTemp = FileManager::generateName() . ".$extension";
		$source = FileManager::createTempFile($nameFileTemp, $content);
		$file = $this->addFileFromPath($source, $options);
		FileManager::removeTempFile($nameFileTemp);
		return $file;
	}

	/**
	 * Attach a file and save it like logo of model. Delete logo before if exists
	 * @param UploadedFile $file
	 * @param array        $options
	 * @return FileManager|UploadedFile
	 */
	public function setLogo(UploadedFile $file, $options = []) {
		if ($this->logo) {
			$this->logo->delete();
		}
		$options["group"] = $this->GROUP_LOGO;
		return $this->addFile($file, $options);
	}

	/**
	 * Initialize variables
	 */
	public function initVariables() {
		$this->initCustomVariables();
		//get environment of disk or default config
		$environment = config("filesystems.disks.$this->DISK.visibility");
		$this->ENVIRONMENT = $environment == FileManager::ENVIRONMENT_PUBLIC;
	}
	//endregion

	/*
	|--------------------------------------------------------------------------
	| getters
	|--------------------------------------------------------------------------
	|
	*/
	/**
	 * Check if a file exists on the folder and disk of model
	 * @param $name
	 * @return bool
	 */
	public function existsFile($name): bool {
		return FileManager::checkIfFileExist($this->FOLDER, $this->DISK, $name);
	}

	/**
	 * Return the actual folder
	 * @return string
	 */
	public function getFolder() {
		$this->initCustomVariables();
		return $this->FOLDER;
	}

	/**
	 * Return the actual disk
	 * @return string
	 */
	public function getDisk() {
		$this->initCustomVariables();
		return $this->DISK;
	}

	/**
	 * Change the disk of model
	 * @param $disk
	 * @return $this
	 */
	public function disk($disk) {
		$this->initCustomVariables();
		$this->DISK = $disk;
		return $this;
	}

	/**
	 * Change the folder of model
	 * @param $folder
	 * @return $this
	 */
	public function folder($folder) {
		$this->initCustomVariables();
		$this->FOLDER = $folder;
		return $this;
	}

	/*
	|--------------------------------------------------------------------------
	| private methods
	|--------------------------------------------------------------------------
	|
	*/
	/**
	 * To make a real path to folder
	 * @param $url
	 * @return string
	 */
	private function secureFolder($url): string {
		return trim($url, '/');
	}

	/**
	 * Initialize the variables folder and disk
	 */
	private function initCustomVariables() {
		if (!$this->customVariablesStarted) {
			//overwrite default folder and disk by user
			$this->fileCustomVariables();
			$this->FOLDER = $this->secureFolder($this->FILE_FOLDER_DEFAULT ?: config('filemanager.folder_default'));
			$this->DISK = $this->FILE_DISK_DEFAULT ?: config('filemanager.disk_default');
			$this->customVariablesStarted = true;
		}
	}


	/*
	|--------------------------------------------------------------------------
	| magic methods
	|--------------------------------------------------------------------------
	|
	*/
	/**
	 * Get all files of model
	 * @return MorphMany
	 */
	public function files() {
		return $this->morphMany(FileManager::class, 'filesable');
	}

	/**
	 * Get all images of model
	 * @return mixed
	 */
	public function images() {
		return $this->morphMany(ImageFile::class, 'filesable');
	}

	/**
	 * To get logo of model
	 * @return mixed
	 */
	public function getLogoAttribute() {
		return $this->files()->withGroup($this->GROUP_LOGO)->first();
	}
}