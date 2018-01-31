<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','avatar', 'info', 'username'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];




    public function tasks()
    {
    	return $this->hasMany(Task::class);
    }


	public function tasksHeWatches()
	{
		return $this->belongsToMany('App\Task', 'watches', 'user_id', 'task_id');
	}


	public function filterTasks($filters, $limit = 15)
	{

		$user =  new User();

		if(isset($filters['username']))
		{
			$user = $user->where('username', $filters['username'])->first();
		}
		else
		{
			$user = Auth::user();
		}


		$userTasks = $user->tasks();
		$watchTasks = $user->tasksHeWatches();

		if(isset($filters['status']))
		{
			$userTasks = $userTasks->where('status', $filters['status']);
			$watchTasks = $watchTasks->where('status', $filters['status']);
		}

		if(isset($filters['deadline']))
		{
			$userTasks = $userTasks->where('deadline', '<=', $filters['deadline']);
			$watchTasks = $watchTasks->where('deadline', '<=', $filters['deadline']);
		}

		$userTasks = $userTasks->paginate($limit);
		$watchTasks = $watchTasks->paginate($limit);


		return $userTasks->getCollection()->merge($watchTasks->getCollection());

	}
}
