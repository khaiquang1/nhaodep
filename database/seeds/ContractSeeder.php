<?php

use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (\App::environment() === 'local') {
            DB::table('contracts')->truncate();

            $users = \App\Models\User::where('id', '>', 1)->get();
            $users->each(function ($user) {
                factory(\App\Models\Contract::class, rand(5, 15))->create(['resp_staff_id' => $user->id]);
            });

        } else {
            dd('This is not local environment!');
        }
    }
}
