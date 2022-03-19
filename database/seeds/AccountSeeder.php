<?php

class AccountSeeder extends DatabaseSeeder
{

    public function run()
    {

        Sentinel::getRoleRepository()->createModel()->create(array(
            'name' => 'Admin',
            'slug' => 'admin',
        ));

	    Sentinel::getRoleRepository()->createModel()->create(array(
            'name' => 'Staff',
            'slug' => 'staff',
        ));

        Sentinel::getRoleRepository()->createModel()->create(array(
            'name' => 'Customer',
            'slug' => 'customer',
        ));

    }

}