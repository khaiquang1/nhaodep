<?php

use Illuminate\Database\Seeder;

class OpportunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (\App::environment() === 'local') {
            DB::table('opportunities')->truncate();

            $users = \App\Models\User::where('id', '>', 1)->get();
            $users->each(function ($user) {
                factory(\App\Models\Opportunity::class, rand(5, 15))->create(['salesperson_id' => $user->id]);
            });

        } else {
            dd('This is not local environment!');
        }
    }
}
