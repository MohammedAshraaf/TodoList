<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
    	'path',
	    'task_id'
    ];

    public function task()
    {
    	return $this->belongsTo(Task::class);
    }
}
