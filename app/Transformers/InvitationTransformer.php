<?php

namespace App\Transformers;

use App\Invitation;
use App\Task;
use App\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class InvitationTransformer extends TransformerAbstract
{
	/**
	 * A Fractal transformer.
	 *
	 * @param Invitation $invitation
	 *
	 * @return array
	 */
    public function transform(Invitation $invitation)
    {
    	$invitor = User::find($invitation->invitor)->name;
    	$invitee = User::find($invitation->invitee)->name;
    	$task = Task::find($invitation->task_id)->name;
        return [
            'id' => $invitation->id,
	        'invitor' => $invitor,
	        'invitee' => $invitee,
	        'task' => $task,
	        'status' => $invitation->status
        ];
    }
}
