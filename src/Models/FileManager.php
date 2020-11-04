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

	//public
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

	public function copy($options = []) {
		return $this->copyThis(null, $options);
	}

	public function copyToModel($model, $options = []) {
		return $this->copyThis($model, $options);
	}

	public function move($folder, $disk = null, $options = []) {
		$disk = $disk ?: $this->disk;
		//copy the file to the new location
		$path = Storage::disk($disk)->path($this->url);
		$file = new UploadedFile($path, $this->name);
		$newFile = self::createFile($file, $disk, $folder, $this->public, $options);
		//remove file from last location
		$this->removeFileFromPlatform();
		//update information of file
		$this->fill($newFile->toArray());
		$this->save();
		//refresh data
		$this->fresh();
	}

	public function filesable() {
		return $this->morphTo();
	}

	public function download($name = '') {
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

	public function append($text) {
		Storage::disk($this->disk)->append($this->url, $text);
		$this->size = Storage::disk($this->disk)->size($this->url);
		$this->save();
	}

	public function prepend($text) {
		Storage::disk($this->disk)->prepend($this->url, $text);
		$this->size = Storage::disk($this->disk)->size($this->url);
		$this->save();
	}

	//attributes

	public function getSrcAttribute() {
		if ($this->public) {
			return Storage::disk($this->disk)->url($this->url);
		}
	}

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

	public function getDownloadSrcAttribute() {
		if ($this->public) {
			return $this->getForceDownloadSrcAttribute();
		} else {
			return '';
		}
	}

	public function getForceDownloadSrcAttribute() {
		return route(config('filemanager.symbolic_link_download_files'), self::encryptId($this->id));
	}

	public function getGetContentAttribute() {
		return Storage::disk($this->disk)->get($this->url);
	}

	//privates

	/**
	 * Remove file where is hosted
	 * @return bool
	 */
	public function removeFileFromPlatform() {
		return Storage::disk($this->disk)->delete($this->url);
	}

	//scopes

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

	//statics

	static public function encryptId($id): string {
		return \Crypt::encrypt($id);
	}

	static public function decryptId($id): string {
		return \Crypt::decrypt($id);
	}

	static public function secureName(string $name): string {
		$n = explode('.', $name)[0];
		$n = Str::slug($n, '_');
		return Str::limit($n, self::LIMIT_MAX_NAME);
	}

	static public function checkIfFileExist($folder, $disk, $fullName): bool {
		return Storage::disk($disk)->exists(Str::finish($folder, '/') . $fullName);
	}

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

	static public function generateName(): string {
		return self::secureName(Str::random(9));
	}

	static public function getExtensionFile(UploadedFile $file): string {
		return $file->getClientOriginalExtension() ?: config('filemanager.extension_default');
	}

	static public function createTempFile($nameTemp, $content = '') {
		$storageTemp = Storage::disk(config('filemanager.disk_temp'));
		$storageTemp->put($nameTemp, $content);
		return $storageTemp->path($nameTemp);
	}

	static public function removeTempFile($nameTemp) {
		$storageTemp = Storage::disk(config('filemanager.disk_temp'));
		$storageTemp->delete($nameTemp);
	}

	static public function uploadFileOnPlatform($disk, $folder, $file, $name): string {
		return Storage::disk($disk)->putFileAs($folder, $file, $name);
	}

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

	static public function getTypeFile($extension): string {
		if (!empty(config("filemanager.extensions.$extension"))) {
			return config("filemanager.extensions.$extension");
		} else {
			return config('filemanager.extensions.*', "file");
		}
	}

	static public function getDataConfigDisk($disk, $field): string {
		return config("filesystems.disks.$disk.$field", '');
	}
}