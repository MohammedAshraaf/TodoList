<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
	protected $fillable = [
		'invitor', 'invitee', 'task_id', 'status'
	];
}
