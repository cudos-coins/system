<?php
use Illuminate\Database\Seeder;
use CC\Transaction;

/**
 * Seeds the transaction table.
 * @author b3nl <code@b3nl.de>
 * @packagge database
 * @subpackage seeds
 * @version $id$
 */
class TransactionTableSeeder extends Seeder
{
    /**
     * Runs the seeding.
     * @return void
     */
    public function run()
    {
        DB::table('transactions')->truncate();

        Transaction::create([
            'from_user_id' => 1,
            'to_user_id' => 2,
            'amount' => 100,
            'description' => 'test transaction',
            'finished' => 1,
            'processed_date' => date('Y-m-d H:i:s')
        ]);
    } // function
}
