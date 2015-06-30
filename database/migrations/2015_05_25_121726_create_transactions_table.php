<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('from_user_id', false, true);
            $table->integer('to_user_id', false, true);
            $table->bigInteger('amount', false, false);
            $table->string('description', 255);
            $table->tinyInteger('finished', false, false)->default('0')->index();
            $table->string('planned_date')->default('0000-00-00 00:00:00')->index();
            $table->string('processed_date')->nullable();
            $table->string('signature', 255)->index();
            $table->softDeletes('deleted_at')->nullable();
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('no action')->onUpdate('no action');
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('no action')->onUpdate('no action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transactions');
    }
}
