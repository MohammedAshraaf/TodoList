<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('invitor')->unsigned();
            $table->integer('invitee')->unsigned();
            $table->integer('task_id')->unsigned();
            $table->enum('status', ['accepted', 'rejected', 'pending']);
	        $table->timestamps();

	        $table->foreign('invitor')->references('id')->on('users')->onDelete('cascade');
	        $table->foreign('invitee')->references('id')->on('users')->onDelete('cascade');
	        $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invitations');
    }
}
