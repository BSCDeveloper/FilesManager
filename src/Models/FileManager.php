<?php

namespace Fboseca\Filesmanager\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManager extends Model {
	const ENVIRONMENT_PUBLIC = "public";
	const ENVIRONMENT_PRIVATE = "private";
	const PLATFORM_S3 = "s3";
	const PLATFORM_LOCAL = "local";
	const LIMIT_MAX_NAME = 150;

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
		$this->table = config('filemanager.table_files');
	}

	protected $guarded = [ 'id' ];
	public $timestamps = true;
	protected $dates = [ 'created_at', 'updated_at' ];

	protected $fillable = [
		"name", "url", "group", "description", "type", "mime_type",
		"file_name", "file_extension", "folder", "public", "size",
		'created_at', 'updated_at', 'disk', 'driver'
	];

	protected static function boot() {
		parent::boot();
		static::deleted(function (FileManager $fileDeleted) {
			$fileDeleted->removeFileFromPlatform();
		});
	}

	public function filesable() {
		return $this->morphTo();
	}

	/*
	|--------------------------------------------------------------------------
	| public methods
	|--------------------------------------------------------------------------
	|
	*/

	/**
	 * Copy this file
	 * @param array $options
	 * @return mixed
	 */
	public function copy($options = []) {
		return $this->copyThis(null, $options);
	}

	/**
	 * Copy this file into new model
	 * @param       $model
	 * @param array $options
	 * @return mixed
	 */
	public function copyToModel($model, $options = []) {
		return $this->copyThis($model, $options);
	}

	/**
	 * Move a file to other folder
	 * @param       $folder
	 * @param array $options
	 */
	public function move($folder, $options = []) {
		$newDisk = !empty($options['disk']) ? $options['disk'] : $this->disk;
		//copy the file to the new location
		$path = Storage::disk($this->disk)->path($this->url);
		$file = new UploadedFile($path, $this->name);
		$newFile = self::createFile($file, $newDisk, $folder, $this->public, $options);
		//remove file from last location
		$this->removeFileFromPlatform();
		//update information of file
		$this->fill($newFile->toArray());
		$this->save();
		//refresh data
		$this->fresh();
	}

	/**
	 * Move a file to other folder and model
	 * @param       $model
	 * @param array $options
	 */
	public function moveToModel($model, $options = []) {
		//get folder and disk
		$folder = !empty($options['folder']) ? $options['folder'] : $model->getFolder();
		$disk = !empty($options['disk']) ? $options['disk'] : $model->getDisk();
		//change the filesable for the new model
		$this->filesable()->associate($model)->save();
		//move this file to the other location
		$options["disk"] = $disk;
		$this->move($folder, $options);
	}

	/**
	 * Download a file
	 * @param string $name
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	public function download($name = '') {
		//if file exist
		if (Storage::disk($this->disk)->exists($this->url)) {
			$name = $name ? self::secureName($name) . '.' . $this->file_extension : $this->name;
			$headers = [
				'Content-Type'        => $this->mime_type,
				'Content-Description' => 'File Transfer',
				'Content-Disposition' => "attachment; filename={$name}",
				'filename'            => $name
			];
			return response(Storage::disk($this->disk)->get($this->url), 200, $headers);
		}
	}

	/**
	 * Put a new text append file
	 * @param array|string $text
	 * @return FileManager|void
	 */
	public function append($text) {
		Storage::disk($this->disk)->append($this->url, $text);
		$this->size = Storage::disk($this->disk)->size($this->url);
		$this->save();
	}

	/**
	 * Put a new text prepend the file
	 * @param $text
	 */
	public function prepend($text) {
		Storage::disk($this->disk)->prepend($this->url, $text);
		$this->size = Storage::disk($this->disk)->size($this->url);
		$this->save();
	}

	/**
	 * Remove file where is hosted
	 * @return bool
	 */
	public function removeFileFromPlatform() {
		return Storage::disk($this->disk)->delete($this->url);
	}

	/*
		|--------------------------------------------------------------------------
		| private methods
		|--------------------------------------------------------------------------
		|
		*/
	/**
	 * For copy a file into a model. If model is null copy the file in this model
	 * @param $model
	 * @param $options
	 * @return mixed
	 */
	private function copyThis($model, $options) {
		$model = $model ?: $this->filesable;
		if (!empty($options["folder"])) {
			$folder = $options["folder"];
		} else {
			$folder = $model ? $model->getFolder() : $this->folder;
		}

		if (!empty($options["disk"])) {
			$disk = $options["disk"];
		} else {
			$disk = $model ? $model->getDisk() : $this->disk;
		}

		$model->folder($folder);
		$model->disk($disk);

		$name = !empty($options["name"]) ? $options["name"] : $this->file_name;
		$group = !empty($options["group"]) ? $options["group"] : $this->group;
		$description = !empty($options["description"]) ? $options["description"] : $this->description;

		return $model->addFileWithContent($this->getContent, $this->file_extension, [
			"name"        => $name,
			"group"       => $group,
			"description" => $description
		]);
	}

	/*
	|--------------------------------------------------------------------------
	| attributes
	|--------------------------------------------------------------------------
	|
	*/
	/**
	 * @return string url to get route of public file
	 */
	public function getSrcAttribute() {
		if ($this->public) {
			return Storage::disk($this->disk)->url($this->url);
		}
	}

	/**
	 * @return string url to get route of file
	 */
	public function getForceSrcAttribute() {
		if ($this->public) {
			return $this->src;
		} else {
			switch ($this->driver) {
				case self::PLATFORM_S3:
					return Storage::disk($this->disk)->temporaryUrl(
						$this->url, now()->addMinutes(60));
				break;
				case self::PLATFORM_LOCAL:
					return Storage::disk($this->disk)->url(self::encryptId($this->id));
				break;
				default:
					return $this->src;
				break;
			}
		}
	}

	/**
	 * @return string url for download public file
	 */
	public function getDownloadSrcAttribute() {
		if ($this->public) {
			return $this->getForceDownloadSrcAttribute();
		} else {
			return '';
		}
	}

	/**
	 * @return string url for download file
	 */
	public function getForceDownloadSrcAttribute() {
		return route(config('filemanager.symbolic_link_download_files'), self::encryptId($this->id));
	}

	/**
	 * Get the content of file
	 * @return string
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	public function getGetContentAttribute() {
		return Storage::disk($this->disk)->get($this->url);
	}


	/*
		|--------------------------------------------------------------------------
		| scopes
		|--------------------------------------------------------------------------
		|
		*/

	public function scopeWithGroup($query, $group) {
		return $query->where('group', $group);
	}

	public function scopeWithNotGroup($query, $group) {
		return $query->where('group', '<>', $group);
	}

	public function scopeWithType($query, $group) {
		return $query->where('type', $group);
	}

	public function scopeWithNotType($query, $group) {
		return $query->where('type', '<>', $group);
	}

	/*
	|--------------------------------------------------------------------------
	| static methods
	|--------------------------------------------------------------------------
	|
	*/

	static public function encryptId($id): string {
		return \Crypt::encrypt($id);
	}

	static public function decryptId($id): string {
		return \Crypt::decrypt($id);
	}

	/**
	 * Make a name secure for save
	 * @param string $name
	 * @return string
	 */
	static public function secureName(string $name): string {
		$n = explode('.', $name)[0];
		$n = Str::slug($n, '_');
		return Str::limit($n, self::LIMIT_MAX_NAME);
	}

	/**
	 * Check if file exist in the source
	 * @param $folder
	 * @param $disk
	 * @param $fullName
	 * @return bool
	 */
	static public function checkIfFileExist($folder, $disk, $fullName): bool {
		return Storage::disk($disk)->exists(Str::finish($folder, '/') . $fullName);
	}

	/**
	 * Return a name that is not in use in the source
	 * @param string $name
	 * @param string $extension
	 * @param        $folder
	 * @param        $disk
	 * @return string
	 */
	static public function getNameNotInUse(string $name, string $extension, $folder, $disk): string {
		$newName = $name;
		$fullName = "$newName.$extension"; //name will be saved
		$contador = 1;
		while (self::checkIfFileExist($folder, $disk, $fullName)) {
			$newName = $name . "_($contador)";
			$fullName = "$newName.$extension";
			$contador++;
		};

		return $newName; //name or name_(1)....
	}

	/**
	 * Generate a new random name
	 * @return string
	 */
	static public function generateName(): string {
		return self::secureName(Str::random(9));
	}

	/**
	 * @param UploadedFile $file
	 * @return string extension of file
	 */
	static public function getExtensionFile(UploadedFile $file): string {
		return $file->getClientOriginalExtension() ?: config('filemanager.extension_default');
	}

	/**
	 * Create a temporary file on the disk temporary
	 * @param        $nameTemp
	 * @param string $content
	 * @return mixed
	 */
	static public function createTempFile($nameTemp, $content = '') {
		$storageTemp = Storage::disk(config('filemanager.disk_temp'));
		$storageTemp->put($nameTemp, $content);
		return $storageTemp->path($nameTemp);
	}

	/**
	 * Remove the temporary file
	 * @param $nameTemp
	 */
	static public function removeTempFile($nameTemp) {
		$storageTemp = Storage::disk(config('filemanager.disk_temp'));
		$storageTemp->delete($nameTemp);
	}

	/**
	 * Copy the file in the platform
	 * @param $disk
	 * @param $folder
	 * @param $file
	 * @param $name
	 * @return string
	 */
	static public function uploadFileOnPlatform($disk, $folder, $file, $name): string {
		return Storage::disk($disk)->putFileAs($folder, $file, $name);
	}

	/**
	 * Create a new file in database and upload it to the platform
	 * @param       $file
	 * @param       $disk
	 * @param       $folder
	 * @param       $environment
	 * @param array $options
	 * @return FileManager
	 */
	static public function createFile($file, $disk, $folder, $environment, $options = []) {
		$name = !empty($options['name']) ? $options['name'] : '';
		$description = !empty($options['description']) ? $options['description'] : '';
		$group = !empty($options['group']) ? $options['group'] : '';

		//get info from file
		$dataFile = self::prepareFileData($file, $name, $folder, $disk);

		$dataFile["description"] = $description;
		$dataFile["group"] = $group;
		$dataFile["public"] = $environment;

		//get the url from file located
		$dataFile["url"] = self::uploadFileOnPlatform($disk, $folder, $file, $dataFile["name"]);

		$fileManager = new FileManager();
		$fileManager->fill($dataFile);
		return $fileManager;
	}

	/**
	 * Prepare all data that needs to create a new file
	 * @param UploadedFile $file
	 * @param              $name
	 * @param              $folder
	 * @param              $disk
	 * @return array
	 */
	static public function prepareFileData(UploadedFile $file, $name, $folder, $disk) {
		if (!$name) {
			$name = self::generateName();
		} else {
			$name = self::secureName($name);
		}

		$extension = self::getExtensionFile($file);

		$name = self::getNameNotInUse($name, $extension, $folder, $disk);

		$type = self::getTypeFile($extension);

		return [
			"name"           => $name . '.' . $extension,
			"file_name"      => $name,
			"file_extension" => $extension,
			"mime_type"      => $file->getMimeType(),
			"type"           => $type,
			"folder"         => $folder,
			"size"           => $file->getSize(),
			"disk"           => $disk,
			"driver"         => self::getDataConfigDisk($disk, 'driver'),
		];
	}

	/**
	 * Returns the file type of the configuration according to its extension
	 * @param $extension
	 * @return string
	 */
	static public function getTypeFile($extension): string {
		if (!empty(config("filemanager.extensions.$extension"))) {
			return config("filemanager.extensions.$extension");
		} else {
			return config('filemanager.extensions.*', "file");
		}
	}

	/**
	 * Get data config of the disk in use
	 * @param $disk
	 * @param $field
	 * @return string
	 */
	static public function getDataConfigDisk($disk, $field): string {
		return config("filesystems.disks.$disk.$field", '');
	}
}