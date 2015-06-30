<?php
use Illuminate\Database\Seeder;
use CC\User;

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

        User::create([
            'alias' => 'admin',
            'balance' => 1000,
            'email' => 'foo@bar.com',
            'name' => 'Administrator',
            'password' => Hash::make('password')
        ]);

        User::create(
            ['alias' => 'test', 'balance' => 1000, 'email' => 'test@example.com', 'name' => 'Test User']
        );
    }
}
