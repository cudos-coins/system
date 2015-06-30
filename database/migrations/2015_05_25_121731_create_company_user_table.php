<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->integer('user_id', false, true);
            $table->integer('company_id', false, true);
            $table->primary([
                0 => 'user_id',
                1 => 'company_id',
            ]);
            $table->foreign('user_id')->references('id')->on('companies')->onDelete('no action')->onUpdate('no action');
            $table->foreign('company_id')->references('id')->on('users')->onDelete('no action')->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('company_user');
    }

}
