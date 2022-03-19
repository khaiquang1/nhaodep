<?php

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DropOptionsSeeder extends Seeder
{
    use SoftDeletes;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('options')->where('id',18)->update(['value'=>'Main Staff','title'=>'Main Staff']);
        DB::table('options')->where('id',26)->delete();
        DB::table('options')->where('id',27)->delete();
        DB::table('options')->where('id',28)->delete();
    }
}
