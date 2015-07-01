<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use CC\APIKey;

/**
 * Seeds the table.
 * @author b3nl <code@b3nl.de>
 * @packagge database
 * @subpackage seeds
 * @version $id$
 */
class APIKeySeeder extends Seeder
{
    /**
     * Runs the seeding.
     * @return void
     * @todo   Create config for hashing!
     */
    public function run()
    {
        DB::table('api_keys')->truncate();

        for ($round = 1; $round <= 10; ++$round) {
            APIKey::create([
                'desc' => 'desc ' . $round,
                'hash' => Hash::make(Str::random(32), ['rounds' => 10]),
                'user_id' => 1
            ]);
        } // for

        APIKey::create([
            'desc' => 'desc ' . ++$round,
            'hash' => Hash::make(Str::random(32), ['rounds' => 10]),
            'user_id' => 2
        ]);
    } // function
}
