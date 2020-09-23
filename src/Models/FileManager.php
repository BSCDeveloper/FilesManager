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


	//public

	/**
	 * For delete a file
	 * @return bool|null
	 * @throws \Exception
	 */
	public function delete() {
		$this->removeFileFromPlatform();
		return parent::delete();
	}

	public function copy($folder = null, $disk = null, $model = null) {
		$model = $model ?: $this->filesable;
		$name = $this->file_name;
		$group = $this->group;
		$description = $this->description;

		if ($folder) {
			$model->folder($folder);
		}

		if ($disk) {
			$model->disk($disk);
		}

		$model->addFileFromContent($this->getContent, $this->file_extension, $group, $name, $description);
	}

	public function move($folder) {

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
						$this->url, now()->addMinutes(5));
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
	private function removeFileFromPlatform() {
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
		return self::secureName(md5(Str::random(32)));
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

	static public function createFile($file, $disk, $folder, $environment, $group, $name, $description) {
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