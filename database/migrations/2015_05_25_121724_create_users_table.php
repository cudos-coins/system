<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 255)->unique();
            $table->string('name', 255);
            $table->string('password', 60);
            $table->string('remember_token', 100)->nullable();
            $table->string('alias', 10);
            $table->string('balance')->default('0');
            $table->softDeletes('deleted_at')->nullable();
            $table->integer('last_transaction_id', false, true)->nullable()->index();

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
        Schema::drop('users');
    }
}
