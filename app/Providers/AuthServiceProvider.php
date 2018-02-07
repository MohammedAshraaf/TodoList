<?php

namespace App\Providers;

use App\Policies\TaskPolicy;
use App\Task;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
		Task::class => TaskPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
	    Passport::routes();


	    Gate::define('show-task', 'TaskPolicy@view');
	    Gate::define('update-task', 'TaskPolicy@update');
	    Gate::define('delete-task', 'TaskPolicy@delete');

        //
    }
}
