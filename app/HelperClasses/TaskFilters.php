<?php


namespace App\HelperClasses;


use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;

class TaskFilters extends QueryFilter
{

	public function status($status)
	{
		return $this->builder->where('status', $status);
	}

	public function deadline($deadline)
	{
		return $this->builder->where('deadline', '<=' , $deadline);
	}

	public function username($username)
	{
		return $this->builder->whereHas('user', function ($query) use ($username){
			return $query->where('username', $username);
		});
	}

	public function watching()
	{
		$user = Auth::user();

		return $this->builder->join('watches', function (JoinClause $query) use ($user) {
			$query->on('tasks.id', '=', 'watches.task_id')->where('watches.user_id', $user->id);
		});
	}
}