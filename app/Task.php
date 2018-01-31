<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
    	'name',
    	'description',
    	'user_id',
	    'privacy',
	    'status',
	    'deadline',
    ];


    public function files()
    {
    	return $this->hasMany(File::class);
    }


    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function watchers()
    {
    	return $this->belongsToMany(User::class, 'watches', 'task_id', 'user_id');
    }
}
