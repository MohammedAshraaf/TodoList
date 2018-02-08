<?php

namespace App;

use App\HelperClasses\QueryFilter;
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


	public function scopeFilter($query, QueryFilter $filter)
	{
		$filter->apply($query);
	}
}
