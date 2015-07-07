<?php
use CC\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * Seeds the user table.
 * @author b3nl <code@b3nl.de>
 * @packagge database
 * @subpackage seeds
 * @version $id$
 */
class UserTableSeeder extends Seeder
{
    /**
     * Runs the seeding.
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('likes')->truncate();
        DB::table('transactions')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Model::unguard();

        User::create([
            'alias' => 'admin',
            'balance' => 1000,
            'email' => 'foo@bar.com',
            'name' => 'Administrator',
            'is_admin' => true,
            'password' => Hash::make('password')
        ]);

        User::create([
            'alias' => 'test',
            'balance' => 1000,
            'email' => 'test@example.com',
            'name' => 'Test User',
            'password' => Hash::make('password')
        ]);

        Model::reguard();
    }
}
