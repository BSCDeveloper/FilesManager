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
	private $customVariablesStarted = false;
	private $GROUP_LOGO = 'logo';
	//endregion

	//region public
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
	}

	/**
	 * Attach file to model
	 * @param UploadedFile $file
	 * @param string       $group
	 * @param string       $name
	 * @param string       $description
	 * @return FileManager|UploadedFile
	 */
	public function addFile(UploadedFile $file, $group = '', $name = "", $description = '') {
		//init variables
		$this->initVariables();
		$file = FileManager::createFile(
			$file,
			$this->DISK,
			$this->FOLDER,
			$this->ENVIRONMENT,
			$group ?: '',
			$name ?: '',
			$description ?: ''
		);
		$this->files()->save($file);
		return $file;
	}

	/**
	 * Attach a file from a path
	 * @param        $path path of a file
	 * @param string $group
	 * @param string $name
	 * @param string $description
	 * @return FileManager|UploadedFile
	 */
	public function addFileFromPath($path, $group = '', $name = "", $description = '') {
		$file = new UploadedFile($path, basename($path));
		return $this->addFile($file, $group, $name, $description);
	}

	/**
	 * Attach a file by passing it content
	 * @param        $content content from file
	 * @param        $extension
	 * @param string $group
	 * @param string $name
	 * @param string $description
	 * @return FileManager|UploadedFile
	 */
	public function addFileWithContent($content, $extension, $group = '', $name = "", $description = '') {
		$nameFileTemp = FileManager::generateName() . ".$extension";
		$source = FileManager::createTempFile($nameFileTemp, $content);
		$file = $this->addFileFromPath($source, $group, $name, $description);
		FileManager::removeTempFile($nameFileTemp);
		return $file;
	}

	/**
	 * Attach a file and save it like logo of model. Delete logo before if exists
	 * @param UploadedFile $file
	 * @param string       $name
	 * @param string       $description
	 * @return FileManager|UploadedFile
	 */
	public function setLogo(UploadedFile $file, $name = "", $description = '') {
		if ($this->logo) {
			$this->logo->delete();
		}
		$group = $this->GROUP_LOGO;
		return $this->addFile($file, $group, $name, $description);
	}

	//endregion

	//region getters
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

	//region setters

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

	//region private

	private function secureFolder($url): string {
		return trim($url, '/');
	}

	private function initCustomVariables() {
		if (!$this->customVariablesStarted) {
			$this->fileCustomVariables(); //overwrite default folder and disk

			$this->FOLDER = $this->secureFolder($this->FILE_FOLDER_DEFAULT ?: config('filemanager.folder_default'));
			$this->DISK = $this->FILE_DISK_DEFAULT ?: config('filemanager.disk_default');
			$this->customVariablesStarted = true;
		}
	}

	public function initVariables() {
		$this->initCustomVariables();
		$this->CONFIG_DISK = config("filesystems.disks.$this->DISK");
		//get environment of disk or default config
		$environment = config("filesystems.disks.$this->DISK.visibility");
		$this->ENVIRONMENT = $environment == FileManager::ENVIRONMENT_PUBLIC;
	}
	//endregion

	//region relathionship

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
	//endregion
}