<?php

namespace Fboseca\Filesmanager\Tests;


use Fboseca\Filesmanager\Traits\HasFiles;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
	use HasFiles;

	protected $table = 'users';

	protected $fillable = [
		'name', 'email', 'password', 'activo', 'activation_token', 'avatar', 'passvisible'
	];

	protected $hidden = [ 'password', 'remember_token', 'activation_token' ];

	protected $dates = [ 'created_at', 'updated_at', 'last_seen' ];
}