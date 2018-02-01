<?php

namespace App;

use App\Events\WatchSaver;
use Illuminate\Database\Eloquent\Model;

class Watch extends Model
{
    protected $fillable =[
    	'user_id', 'task_id'
    ];


	protected $dispatchesEvents = [
		'saved' => WatchSaver::class,
	];
}
