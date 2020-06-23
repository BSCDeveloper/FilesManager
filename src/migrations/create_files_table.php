<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create(config('filemanager.table_files'), function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name', 190)->nullable(false);
			$table->string('folder', 150)->nullable(false);
			$table->string('url', 400)->nullable(false);
			$table->morphs('filesable');
			$table->text('description');
			$table->string('group', 50);
			$table->string('type', 10)->nullable(false);
			$table->string('mime_type', 50)->nullable(false);
			$table->string('file_name', 150)->nullable(false);
			$table->string('file_extension', 10)->nullable(false);
			$table->bigInteger('size');
			$table->boolean('public')->default(true);
			$table->string('disk', 25)->nullable(false);
			$table->string('driver', 10)->nullable(false);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists(config('filemanager.table_files'));
	}
}
