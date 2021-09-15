
[![Packagist Version](https://img.shields.io/packagist/v/fboseca/files_manager)](https://packagist.org/packages/fboseca/files_manager)  ![Packagist License](https://img.shields.io/packagist/l/fboseca/files_manager) [![Laravel](https://img.shields.io/badge/Laravel-^6-orange.svg)](https://laravel.com/docs/5.8) ![Php](https://img.shields.io/badge/php->=7.1.3-orange.svg)  

# What It Does      
 This package allows you to keep track of the uploaded files, for this it associates each file to a Laravel model, getting a quick and easy manage.  
  
Once installed you can do stuff like this:  

```  
$user = User::find(1);  
  
//attach a file with a User model  
//thats will be save the file on bbdd and the storage app/public/files by default
$user->addFile($request->file('file'));  

//for download all images into a zip file
$user1->images->toZipFile()->download('allMyImages');

//for delete all images with date less now
$user1->images()->where('created_at', '<', Carbon:now())->removeFiles();
```  

And show the image files in the views:  

```  
//return all images attached to User  
@foreach ($user->images as $file )          
    <img src="{{$file->src}}"  width="100"/> 
@endforeach  

//return all images from gallery
@foreach ($user->images()->withGroup('gallery')->get() as $file )          
    <img src="{{$file->src}}"  width="100"/> 
@endforeach  
```  
  
## Contents
- [Installation](#installation)      
- [Configuration](#configuration)      
  - [Other config ](#other-config)      
  - [File extensions](#file-extensions)      
- [Usage](#usage)
  - [Adding a file](#adding-a-file)      
  - [Get files](#get-files)   
  - [Method append and prepend](#method-append-and-prepend)   
  - [Delete](#delete)   
  - [Download](#download)   
    - [Get download´s route](#get-downloads-route)   
  - [Copy](#copy)  
  - [Move](#move)  
  - [Save and get avatar](#save-and-get-avatar)         
  - [Get source from Images](#get-source-from-images)  
  - [Change disk and folder](#change-disk-and-folder) 
- [Collections](#collections)
  - [Delete Files](#delete-files)    
  - [Copy Files](#copy-files)    
  - [Move Files](#move-files)    
  - [Files to zip](#files-to-zip)  
- [Tips](#tips)      
  - [Method exists](#method-exists)               
  - [Scopes](#scopes)       
  - [Create a relationship for a type of file](#create-a-relationship-for-a-type-of-file)
- [Api](#api)        
  
## Installation    
This requires Laravel 6 or highter and Php 7.1.3 or highter.    
    
1) In order to install File manager, just run in terminal:     
    
```
composer require fboseca/files_manager 
``` 

2) Open your `config/app.php` and add the following to the `providers` array:    
    
```
'providers' => [
    ...,
    Fboseca\Filesmanager\FilesManagerServiceProvider::class,
] 
``` 

3) Run the command below to publish the package config file `config/filemanager.php`:    
    
```
php artisan vendor:publish 
``` 

4) Now we must create the table for files. The default name of the table is **files_manager** but if you want to change the name of the table you can do it in `config/filemanager.php` on the key ***table_file***.     
So when you choose the name run the command below:    
```
php artisan migrate 
``` 

5) File manager use a laravel storage link for public files saved in you own host, so run the command below:    
```
php php artisan storage:link 
```  
## Configuration 
Copy the property values below in the `config/filesystems.php`.  File manager use the Laravel`s disks for save files and these values will be used for get information for  save it.       
  
```               
'temp' => [            
    'driver' => 'local',        
    'root' => storage_path('app/temp'),        
    'visibility' => 'private'        
],              
                
'private' => [           
	'driver' => 'local',        
	'root' => storage_path('app/private'),        
	'url' => env('APP_URL') . config('filemanager.url_link_private_files'),        
	'visibility' => 'private'       
],       

'public' => [        
	'driver' => 'local',        
	'root' => storage_path('app/public'),        
	'url' => env('APP_URL') . '/storage',        
	'visibility' => 'public'  
],    
              
's3' => [           
	'driver' => 's3',        
	'key' => env('AWS_ACCESS_KEY_ID'),        
	'secret' => env('AWS_SECRET_ACCESS_KEY'),        
	'region' => env('AWS_DEFAULT_REGION'),        
	'bucket' => env('AWS_BUCKET'),        
	'url' => env('AWS_URL'),        
	'visibility' => 'public'  
], 
```  
  
> All disk must have a **visibility** field that can be *public* or > *private*.      

 You can modify any disk and add all what you want.      

| Field | Description|      
|--|--|      
| root | Folder when the files will be saved  |      
| url | Url for get the files, this url is used when we use the attributte *src* explained below |      
| visibility | Can be *public* or *private* and all files saved using this disk will be treated as public or private  |      
| driver | Can be all options that Laravel offers (local, s3, ftp, dropbox)  |    
    
#### Temporal disk
    
Is important have a temporary disk and you should not remove it from the configuration. If you change the name of temporary disk (**not recommended**) you must change the name in `config/filemanager.php` in the key *disk_temp*.

```
"disk_temp" => "{yourNameDisk}",           
```

#### Public disk
      
The local public disk by default  **should not be modified.** If you want add other public disk you can do it adding other disk.       
Note that all public files will be save under **storage_path('app/public/{your-custom-folder}').**      

```  
//disk by default        
	'public' => [            
	'driver' => 'local',        
	'root' => storage_path('app/public'),        
	'url' => env('APP_URL') . '/storage',        
	'visibility' => 'public',        
],      

//new disk created     
	'uploads' => [            
	'driver' => 'local',        
	'root' => storage_path('app/public/uploads'),        
	'url' => env('APP_URL') . '/storage/uploads',        
	'visibility' => 'public',        
] 
```  

Remember that FileManager use the symbolic link of Laravel for return  the path when the file is hosted in public disk, so it`s very important to indicate the url inside the disk configuration.  

```
'root' => storage_path('app/public/{yourRoutePath}'),
'url' => env('APP_URL') . '/storage/{yourRoutePath}', 
```

#### Private local disk  

For privates disk you must be sure that the root path musn´t be under *app/public*.

```
'private' => [    
	.....     
	'root' => storage_path('app/private'),      
],     
```

To get the private files path this package provides a route by default and you must use this route for all local private disk that you create.
 
```
'private' => [    
	.....     
	'url' => env('APP_URL') . config('filemanager.url_link_private_files'),      
],

'otherDiskPrivate' => [    
	.....     
	'url' => env('APP_URL') . config('filemanager.url_link_private_files'),      
],      
```
      
If you want to change the default url you must add this to file´s route of Laravel.

```  
Route::get('custom/files/{has}', function ($has) {  
   return \Redirect::route('file.manager.private', $has);  
});
```

And then change the config by default.

```  
"url_link_private_files" => "/custom/files/",
```

Note that  in the new route the **has** parameter is very important becouse the id of private file will be encrypted.
  
#### External disk

For external drives you must provide the path where the file is located.

```
's3' => [             
	....        
	'url' => env('AWS_URL'),          
	'visibility' => 'private' 
],
```  
  
### Other config 

The options that you have for config this package are:    

| Field | Description|Default    
|--|--|--|    
| folder_default | The folder when the files will be save under the disk  |files    
| disk_default | Disk by default |public    
| extension_default | If file don´t has a extension, this will be applied|txt    
| extensions | List by extension for indicates how file manager must catalog a file by her extension |  
  
### File extensions

When we save a file this will be saved with a type that we will indicate in the configuration inside the extensions key. This file configuration is `config/filemanager.php`. By default all files with image extensions are treated as *img* type, as shown in the example below, but you can modify it by the type you want.     
    
```  
"extensions" => [            
	"bmp" => "img",      
	"gif" => "img",      
	"jpeg" => "img",      
	"jpg" => "img",      
	"png" => "img",      
	"tiff" => "img",    
	"*" => "file",  
]
 ```  
  If the file extension is not appear in the list, FileManager will use the default key that is given by `*`:    
    
```  
 "extensions" => [ "*" => "file"]
 ```  
  
  In this case a file with extension *txt* will be treated like *file* type.   
  
>  Do not remove the `*` value from the extensions`s configuration.  
  
If the original file does not have an extension FileManager will apply   the default file extension, this can be configured in  **extension_default** key that by default is *txt*.    
    
```  
 "extension_default" => "txt"
 ```  
 
Cataloging files by type will be important for finding files of a certain type as discussed below.  
  
## Usage 

The first action we must to do is attach in the model that we want you to have a file log the trait **HasFiles**.  You can choose any model for attach files, the only thing you have to do is put the trait into a model.    
    
```  
class User extends Authenticatable {       
	use Notifiable, HasFiles;    
      .....   
}  
```  


Now the model *User* can attach files to self.    

### Adding a file 

To save a file into a model use the method **addFile** passing the file that you want to save.
This method accept a file as first parameter and options`s array as second parameter and 
return a FileManager class.  

```  
$user = User::find(1);    
$newFile = $user->addFile($request->file('file'));  
``` 

Any file that you add, ***never going to be overwrite***, it is a security system to prevent files from being replaced.
Automatically FileManager create a unique random name and save the file into the database and the disk 
and folder indicated in the configuration. 
It will take the default configuration of the file `config/filemanager.php`.
    
```  
"folder_default" => "files",    
"disk_default" => "public",  
```
  
#### Change a name
  
**In case that the name exists** in the disk and folder, FileManager automatically change de 
name to `name_(1).extension` to make sure the file will not be overwritten.  
 
```  
$newFile = $user->addFile($request->file('file'),[
    "group"       => "gallery",
    "name"        => "myNameFile",
    "description" => "A description for a file",
    "disk"        => "s3",
    "folder"      => "otherFolder"
]);
```  

> Note that it is not necessary to indicate the file extension because FileManager will apply the correct 
> extension to the file, and it will check the file type according to the configuration.

The extension will be applied automatically, see [File extensions](#file-extensions) document.

For security reasons, **the name of file will be modified if contains spaces or dots**.
For example, if the name is *my Name File.d.doc* will be changed for `my_name_file.doc`. 
 
>  only will be valid the text until the first dot 
and the spaces will be changed for `'_'`.

#### Add files from path
We can add files from path resource.
This method receives the file path as the first parameter and as optional parameters it has the same as 
the `addFile` method: *group, name and description*

```  
$newFile = $user->addFileFromPath($pathOfFile);
$newFile = $user->addFileFromPath($pathOfFile,[
    "group"       => "gallery",
    "name"        => "myNameFile",
    "description" => "A description for a file",
    "disk"        => "s3",
    "folder"      => "otherFolder"
]);
```  

#### Add files with content
We can add files with content. 
This method receives the file path as the first parameter 
and the extension of file as second parameter.
Too as optional parameters it has the same as 
the `addFile` method: *group, name and description*

```  
$newFile = $user->addFileWithContent('hello world', 'txt');
$newFile = $user->addFileWithContent('hello world', 'txt', [
    "group"       => "gallery",
    "name"        => "myNameFile",
    "description" => "A description for a file",
    "disk"        => "s3",
    "folder"      => "otherFolder"
]);
 ```  

### Get files 

We can access to the files of a model in many ways. The simple form is calling the relationships **files**. Files return a collection of the FileManager class.        
```  
 $user->files 
 ```  
If you want get only the image files you can invoke the relationships **images**.    
    
```  
@foreach ($user->images as $file )        
	<img src="{{$file->src}}"  width="100"/>      
@endforeach 

@foreach ($user->files as $file )        
	<a href="{{$file->src}}" target="_blank">{{$file->name}}</a>      
@endforeach 
```  
By default only images is configured for get a type of file of the model, but if you want add more type of relationship see the [Create a relationship for a type of file](#create-a-relationship-for-a-type-of-file).  
  

### Method append and prepend

To add new content to file use method *append* (at the end of the document ) and *prepend* (at the start of the document).

```  
$file1 = $user1->addFile($request->file('file'));
$file1->append('End of document');
$file1->prepend('Start of document');
```  


### Delete 

To delete a file only need use the method **delete** of model. This method delete the file from database and host.      

```  
$file = $user->files()->find(1); 
$file->delete();
```  

So, if you want delete all images from user see [Delete Files](#delete-files).

### Download 

To download a file use the method **download**. This method accept a one parameter for choose the name for download the file, by default is her name. The name no need to provide an extension.      

```  
//in your controller
return $file->download();     
return $file->download('name-of-file');  
```  

#### Get download´s route
For download file in html, FileManager provide an attribute and route for download the file.  
For public files we use the attribute *downloadSrc*, but if we want to download public and private files we 
will use *forceDownloadSrc* 

```  
//in your view
<a href="{{$file->downloadSrc}}">download</a>  //only for public files    
<a href="{{$file->forceDownloadSrc}}">download</a> //for all public and privates files
```  
By default the route is `download/file/{has}`, and FileManager puts it into configuration automatically. 
**If we want to change the default download path**, we must add this code in our Laravel route file:      

```  
Route::get('you/route/{has}', function ($has) {        
	return \Redirect::route('download.file', $has);      
})->name('{yourNameOfRoute}'); 
```  

After this, **we must change the config**. In file `config/filemanager.php` in *symbolic_link_download_files* change the default value by the name of you route.    
    
```  
"symbolic_link_download_files" => "youNameOfRoute"
```  

Example:  

```  
//in route.php 
Route::get('myNameOfPathDownloadFiles/{has}', function ($has) {            
	return \Redirect::route('download.file', $has);      
})->name('download.route');   
          
//in config/filemanager.php  
"symbolic_link_download_files" => "download.route"
```  

### Copy 

The method **copy** will make a copy of the same file with same name. It will be saved in the same folder and disk as the original file. 
This method accepts options`s array as parameter.    

``` 
$file = $user->files()->find(1);  
$file2 = $file->copy();  //copy exactly the flie 
//copy a file in distinct folder, disk, with distinct name, group and description
$file2 = $file->copy([
    "folder"      => 'fileCopied', //change folder
    "disk"        => 'private', //change disk
    "name"        => 'avatar', //change name
    "group"       => 'gallery', //change group
    "description" => 'A description of file', //change description
]);  
```

If the file exists in the folder and disk specified, FileManager will change the name to `nameOfFile_(1).extension` for prevent overwrite.

#### Copy to model
 
We can copy a file into a other model that implement the *HasFile* trait.
For this, use the method **copyToModel** passing the model to copy the file 
as a first parameter and array options as second parameter.

``` 
$file = $user->files()->find(1);  
$file2 = $file->copyToModel($user2);  //copy exactly the flie attaching to $user2
$file2 = $file->copyToModel($user2,[
    "folder"      => 'fileCopied', //change folder
    "disk"        => 'private', //change disk
    "name"        => 'avatar', //change name
    "group"       => 'gallery', //change group
    "description" => 'A description of file', //change description
]);  
```

>This method return the file copied.

### Move 
  
The method **move** will move this file to other folder. This method need the folder as first parameter 
and accepts options`s array as second parameter.

``` 
$file = $user->files()->find(1); 
$file2 = $file->move("other_folder");  //move the file to **other_folder folder**
$file2 = $file->move('other_folder',[  
	 "disk"        => 'private', //change disk 
	 "name"        => 'avatar', //change name 
	 "group"       => 'gallery', //change group 
	 "description" => 'A description of file', //change description
 ]); 
 ```  
  
If the file exists in the folder and disk specified, FileManager will change the name to `nameOfFile_(1).extension` for prevent overwrite.  
  
#### Move to model  
 
We can move a file into a other model that implement the *HasFile* trait. For this, use the 
method **moveToModel** passing the model where you want move the file as a first parameter and 
options`s array  as second parameter.  
  
``` 
$file = $user->files()->find(1); 
$file2 = $file->moveToModel($user2);  //move the flie attaching to $user2  
$file2 = $file->moveToModel($user2,[  
	 "folder"      => 'fileCopied', //change folder 
	 "disk"        => 'private', //change disk 
	 "name"        => 'avatar', //change name 
	 "group"       => 'gallery', //change group 
	 "description" => 'A description of file', //change description
 ]); 
 ```  
  
>This method return the file moved.   
  
### Save and get avatar  

FileManager provide a method for save a avatar of each model. This is a special method and only accept one image, 
so if we pass a image to save as a avatar, the last image will be deleted.   
For this use the method **setAvatar** and accepts options`s array as second parameter.       
  
```  
$user->setAvatar($request->file('file')); 
$fileSaved = $user->setAvatar($file, [
    "name"   => "logo2",
    "folder" => "avatars",
    "disk"   => "s3",
	"description" => "A description for a file"
]);
```  

For get a instance of avatar use the attribute **avatar**, that return a FileManager model.    

```  
<img src="{{$user->avatar->src}}"  width="100"/>
```  

>Be careful, the avatar should not be saved to a disk with private visibility

### Get source from Images

For images files FileManager provide a special attributes for get the src of image. The attribute **src** return 
the route for public files and the attribute **forceSrc** returns the path for any kind of file visibility.    
   
```  
<img src="{{$file->src}}"  width="100"/>
<img src="{{$file->forceSrc}}"  width="100"/>
```  

 - The route for public files is generated automatically by Laravel.     
 - For private local files the route by default is `private/file/`, so if    
   you want to change this route see the documentation [Private local disk    
 ](#private-local-disk)    
 - For private files for amazon this attribute return    
   a temporary url.     
 - For privates files from other drivers like ftp or    
   dropbox return the url in the configuration of disk.    
    
> Remember that in the file `config/filesystems.php` in the options of the disks you must indicate always the url options.  

```  
//filesystem.php 
'private' => [           
	....
	'url' => config('filemanager.symbolic_link_private'),          
	'visibility' => 'private'         
],                                 

'public' => [            
	....
	'url' => env('APP_URL') . '/storage',          
	'visibility' => 'public'    
],              

's3' => [                 
	....
	'url' => env('AWS_URL'),          
	'visibility' => 'public'    
],  
```  

### Change disk and folder    
#### Globally
 The disk and folder by default are configured in `config/filemanager.php` and affect to all models by default.    

```  
"folder_default" => "files",    
"disk_default" => "public",  
```  
If you want change a folder or a disk for a specific model you can do it overwriting the method **fileCustomVariables** into the model.      
    
```  
class User extends Authenticatable {        
	use Notifiable, HasFiles;    

	public function fileCustomVariables() {      
		$this->FILE_FOLDER_DEFAULT = "/users/$this->id/documents";      
		$this->FILE_DISK_DEFAULT = 's3';      
	}    
}  
```  
In this case when a file is attached to a User model it will be saved in the disk *s3* and the folder */user/id-of-user/documents*. 
This will happen with all files attached to User model.     


#### For this session
If we want change the disk or folder only for this session, we can use the method *folder* and *disk*,
then all files attached after this, will be saved in the new folder and disk specified.

```  
//disk and folder default from filemanager.php or Model class 
$user->addFile($request->file('file')); //saved in disk and folder default          

//now we change the disk and folder    
$user->disk('s3')->folder("/users/$user->id/documents");
$user->addFile( $request->file('otherFile')); //saved in disk s3 and folder user/1/documents
$user->addFile( $request->file('newFile')); //saved in disk s3 and folder user/1/documents
 
```  

#### Once
Also, we can change the disk and folder only for a one file.

```  
//change the disk and folder for user
$user->disk('s3')->folder("/users/$user->id/documents");

$user->addFile( $request->file('otherFile')); //saved in disk s3 and folder user/1/documents

$user->addFile( $request->file('otherFile'),[
    "folder" => 'copies',
    "disk"   => "private"
]); //saved in disk private and folder copies

$user->addFile( $request->file('otherFile')); //saved in disk s3 and folder user/1/documents
```  

#### Get folder and disk
If you want to know what is the actual disk and folder only call this methods:      
  
```  
$user->getFolder(); //files    
$user->getDisk(); //public   
$user->disk('s3')->folder("/users/$user->id/documents");   
$user->getFolder(); // users/1/documents   
$user->getDisk(); // s3  
```  

## Collections
  ### Delete Files 
  To delete all images from user use method `removeFiles` from collection.
  
  ```  
  $user->images->removeFiles(); //remove files from bbdd and platform
  $user->images()->delete(); //only remove files from bbdd
  ```  
  
  > Remember, if you use mass delete of Eloquent only files will be deleted from database.

  ### Copy Files   
  To copy many files use method `copyFiles` or `copyFilesToModel`. 
  This method can only be used in collections of FilesManager.
  All files will be copied with the same information as the original file. 
  To change any parameter use the option´s array.
  If the file to be copied already exists on the indicated disk and folder, 
  the name will be changed to avoid overwriting.
  
  ``` 
  $file = $user->files()->find(1);  
  $filesCopied = $user->files->copyFiles();  //copy all files to the same folder and disk
  //all files will be copied with this options.
  $filesCopied = $user->files->copyFiles([
        "folder"      => 'fileCopied', 
        "disk"        => 'private', 
        "name"        => 'avatar', 
        "group"       => 'gallery', 
        "description" => 'A description of file', 
  ]);  
  
  //copy files from user1 to user2
  $filesCopied = $user->files->copyFilesToModel($user2);
  $filesCopied = $user->files->copyFilesToModel( $user2, [
        "folder"      => 'fileCopied', 
        "disk"        => 'private', 
        "name"        => 'avatar', 
        "group"       => 'gallery', 
        "description" => 'A description of file', 
  ]); 
  ```  
  
  >Both methods return a collection with the files copied
 
  ### Move Files   
  
To move many files use method `moveFiles` or `moveFilesToModel`. This method can only be used in collections of FilesManager. The method `moveFiles` needs a folder as first parameter.  
  
``` 
$file = $user->files()->find(1); 
$filesCopied = $user->files->moveFiles("other_folder");  //move all files to 'other_folder'
//all files will be moved with options.  
$filesCopied = $user->files->moveFiles("other_folder",[       
	 "disk"        => 'private',   
	 "name"        => 'avatar',   
	 "group"       => 'gallery',   
	 "description" => 'A description of file' 
 ]);   

//move files from user1 to user2  
$filesCopied = $user->files->moveFilesToModel($user2);  
$filesCopied = $user->files->moveFilesToModel($user2, [  
	 "folder"      => 'fileCopied',      
	 "disk"        => 'private',   
	 "name"        => 'avatar',   
	 "group"       => 'gallery',   
	 "description" => 'A description of file', 
]); 
```

>Both methods return a collection with the files moved

### Files to zip 

Is possible add many files to zip for save or download. Create a zip file this return a *ZipFileManager* class:    

```  
$user1 = User::find(1);    
$zip = $user1->images->toZipFile();      
$zip->save(); 
```  
In this case all images of User1 will be added to zip file and after this file will be saved associed to the model.  
  
#### Method save  
  
This method save the file with extension `.zip` and associates it with a model.   
|Parameter| Description | Default |    
|--|--|--|    
| name | The name of file without extension. (*optional*)|random string    
| group | The group to which the file belongs.  | void    
| description |  A description for a file (*optional*)|void     
    
```  
$fileZip = $zip->save('myImages', 'forDownloads', 'All my images'); 
```  

The method save return a instance of FileManager of the file saved. For change the disk and folder  you must  call to method **disk** and **folder** of ZipFileManager.    
    
```  
$fileZip = $zip->disk('s3')->folder("path/to/folder")->save(); 
$fileZip = $zip->disk('s3')->folder("path/to/folder")->save('myImages', 'forDownloads', 'Desc'); 
```  
#### Adding files  

You can add other files to the zip file. You need use the method **addFiles**, and this method accept a collection of FileManager.    

```  
$user1 = User::find(1);     
$zip = $user1->images->toZipFile();//images user1 to zip   

//adding a many files
$user2 = User::find(2);      
$ImagesUser2 = $user2->images;//get images user2      

$zip->addFiles($ImagesUser2); //merge images user1 with images user2

//adding a new file
$newFile = $user1->addFile($file);
$zip->addFile($newFile);
```  

Now we have in the zip file the images of User1 and User2 and if we use the method save this zip file will be associated with User1 because it is the one that created the zip. 
To change the user to which we want to associate the created zip file we will use the method **model**.      

```  
$zip->model($user2)->save();//saved in the folder and disk of User2 

// attach file to User2 in disk and folder specified     
$zip->model($user2)->disk('s3')->folder("path/to/folder")->save();       
$zip->disk('s3')->folder("path/to/folder")->model($user2)->save();  
```  
Keep in mind that when the model changes, the file will be saved in its default folder and disk, but if we modify before the folder or disk where we want to save it, then folder and disk specified will prevail over the default route.  

#### Download Zip
  
For download the zip file we can use the method **download**. This method accept two parameters:    

|Parameter| Description | Default |    
|--|--|--|    
| name | The name of file for download without extension. (*optional*)|random string    
| deleteFile| For delete the temporal file after download. This is useful to prevent the system memory from becoming saturated  (*optional*)| true      

``` 
return $zip->download('myImages');//this will delete the temporary file    
return $zip->download('myImages',false);//this will not delete the temporary file  
```  

> If the zip file is not deleted after downloading, it will remain on  
> the temporary disk taking up space.  

## Tips  
  
### Method exists   

You can check if a file exist in the folder and disk of model with method **existsFile**:      

```  
$user->existsFile("{name-of-file-with-extension}");
$user->disk('private')->folder("/users/1/documents")->existsFile("{name-of-file-with-extension}");
```  
  
### Scopes   

```  
$user->files()->withGroup('gallery')->get(); //all files con group gallery 
$user->images()->withNotGroup('gallery')->get(); //all images that do not belong to the gallery group 
$user->images()->withType('img')->get(); //all images of type img 
$user->files()->withNotType('img')->get(); //all files that haven`t a img type 
```  

### Create a relationship for a type of file   

This package allow to create you own custom FileManager`s model for a kind of file, like relationship images which is already implemented in the package:     
    
 1. Creating a new class    
    
Create a class that extends of `Fboseca\Filesmanager\FileManager`, select the type of file that you 
want to attach to this model and **apply the global scoped TypeFileScope.**    

```  
use Fboseca\Filesmanager\Models\FileManager; 
use Fboseca\Filesmanager\scopes\TypeFileScope;        
   
class Docs extends FileManager {              
     public $type_file = 'word';      
         
     protected static function boot() {      
         parent::boot();      
         static::addGlobalScope(new TypeFileScope());      
      }      
} 
```  

In this case the class *Doc* is for files that we consider word type. 
Remember that the type of the files is configured in `config/filemanager.php`, see [File extensions](#file-extensions).    

```    
 "extensions" => [      
	 .....    
	 "doc" => "word",      
	 "docx" => "word",    
	 ......    
```  

In this example, all files with extension *doc* or *docx* are considered word type.  
   
 2. Usage    
    
When we have already created the model we can use it in our main model to associate and manage files. 
We must add the relationship to principal model ( in this case we use User model ).    

``` 
class User extends Authenticatable {    
	use Notifiable, HasFiles;    
          
	public function words() {      
		return $this->morphMany(Docs::class, 'filesable');      
	}    
 }  

```  

As you can see the relationship between the model and our FileManager class is morphological.     
Now we can use this relation for get all files with *word* types and you can use all methods for Laravel`s relationship.     

``` 
$user->words; //return a collection of Docs 
$user->words()->find(2);   
$user->words()->delete();   
$user->words()->toZipFile();   
```  
  
This is a good idea if you want attach a kind of file with others tables of the database or apply any functions.

### Api

#### Options 

| Option ||     
|--|--|    
| name | The full name of file.     
| group | The group to which the file belongs      
| description | The description of file    
| folder | The folder where the file is hosted     
| url |The combination of folder and name    
| type | The type of file is configured in `config/filemanager.php` | mime_type | The mime_type of file    
| file_name | Only the name without extension    
| file_extension | Only the real extension of file    
| size | The size of file in bytes    
| public | Is a file public or not    
| disk | The disk where the file is hosted    
| driver | The type of Laravel´s driver which was used to save the file    
| created_at | Date of creation of file    
| updated_at | Date of update of file  

#### Attributes

| Attributes ||       
|--|--|      
| getContent | return the content of file.       
| src | return url for access to file (public only). Is used for img tag.
| forceSrc | return url for access to file (all visibility).  
| downloadSrc | return the url for download the file (public only).  
| forceDownloadSrc | return the url for download the file (all visibility).

#### Methods

| Method |Parameter| |         
|--|--|--|        
| delete| | Remove a file from database and storage.   
| copy| options | Copy a file.
| copyToModel| options | Copy a file and attached to model.
| move| |   
| moveToModel| |   
| append| text | Append text to the content of file.         
| prepend |text| Prepend text to the content of file.    
| download |name (optional)| return download response of the file. If name isn`t given, the default name is the name of file saved on database. 
  
#### Collection methods
    
| Method |Parameter| |         
|--|--|--|        
| removeFiles| | Remove all files from database and storage. 
| copyFiles| options | Copy all files. 
| copyFilesToModel| options | Copy all files.
| moveFiles| options | Move all files to other folder. 
| moveFilesToModel| options | Move all files to other folder. 
| toZipFile|| Copy all files into a new zip file