<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * The general database seeder.
 * @author b3nl <code@b3nl.de>
 * @package database
 * @subpackage seeds
 * @version $id$
 */
class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call('UserTableSeeder');
        $this->call('APIKeySeeder');
        $this->call('TransactionTableSeeder');

        Model::reguard();
    }
}
