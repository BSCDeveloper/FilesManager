<?php
return [
	/*
	|--------------------------------------------------------------------------
	| name of table for save files
	|--------------------------------------------------------------------------
	|
	*/

	"table_files"                  => "files_manager",

	/*
	|--------------------------------------------------------------------------
	| General
	|--------------------------------------------------------------------------
	| General settings to know where to save the files
	*/

	"folder_default"               => "files",
	"disk_default"                 => "public",
	"disk_temp"                    => "temp", //name of disk to use like temporal disk

	/*
	|--------------------------------------------------------------------------
	| public methods
	|--------------------------------------------------------------------------
	|
	*/
	"url_link_private_files"       => "/private/file/",
	/*
	|--------------------------------------------------------------------------
	| public methods
	|--------------------------------------------------------------------------
	|
	*/
	"symbolic_link_download_files" => "download.file",
	/*
	|--------------------------------------------------------------------------
	| public methods
	|--------------------------------------------------------------------------
	|
	*/
	"extension_default"            => "txt",
	//type of file for bbdd
	"extensions"                   => [
		"bmp"  => "img",
		"gif"  => "img",
		"jpeg" => "img",
		"jpg"  => "img",
		"png"  => "img",
		"tiff" => "img",
		"txt"  => "txt",
		"pdf"  => "pdf",
		"zip"  => "zip",
		"xls"  => "excel",
		"xlsx" => "excel",
		"doc"  => "word",
		"docx" => "word",
		"js"   => "json",
		"json" => "json",
		"html" => "html",
		"*"    => "file",
		/*"x-world/x-3dmf"                => "qd3",
		"video/x-msvideo"               => "avi",
		"application/postscript"        => "ps",
		"application/x-macbinary"       => "bin",
		"application/x-shockwave-flash" => "swf",
		"application/java"              => "class",
		"text/css"                      => "css",
		"text/comma-separated-values"   => "csv",
		"application/cdr"               => "cdr",
		"application/acad"              => "dwg",
		"application/octet-stream"      => "exe",
		"application/gzip"              => "gz",
		"application/x-gtar"            => "gtar",
		"video/x-flv"                   => "flv",
		"image/x-freehand"              => "fhc",
		"application/x-helpfile"        => "hlp",
		"text/html"                     => "htm",
		"image/x-icon"                  => "ico",
		"application/x-httpd-imap"      => "imap",
		"application/inf"               => "inf",
		"application/x-javascript"      => "js",
		"text/x-java-source"            => "java",
		"application/x-latex"           => "latex",
		"audio/x-mpequrl"               => "m3u",
		"audio/midi"                    => "mid",
		"video/quicktime"               => "qt",
		"audio/mpeg"                    => "mp3",
		"video/mpeg"                    => "mp2",
		"application/ogg"               => "ogg",
		"application/x-httpd-php"       => "php",
		"application/pgp"               => "pgp",
		"application/mspowerpoint"      => "pot",
		"application/x-quark-express"   => "qxd",
		"application/x-rar-compressed"  => "rar",
		"audio/x-realaudio"             => "ra",
		"audio/x-pn-realaudio"          => "rm",
		"text/rtf"                      => "rtf",
		"application/x-sprite"          => "sprite",
		"audio/x-qt-stream"             => "stream",
		"text/xml-svg"                  => "svg",
		"text/x-sgml"                   => "sgm",
		"application/x-tar"             => "tar",
		"application/x-compressed"      => "tgz",
		"application/x-tex"             => "tex",
		"video/x-mpg"                   => "vob",
		"audio/x-wav"                   => "wav",
		"x-world/x-vrml"                => "wrl",
		"text/xml"                      => "xml",*/
	],
];