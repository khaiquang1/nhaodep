<?php


use Efriandika\LaravelSettings\Facades\Settings;

class SettingsSeeder extends DatabaseSeeder
{

    public function run()
    {
        Settings::set('site_name', "Salesdy");
        Settings::set('site_logo', 'logo.png');
        Settings::set('site_email', 'info@domain.com');
        Settings::set('allowed_extensions', 'gif,jpg,jpeg,png,pdf,txt');
        Settings::set('backup_type', 'local');
        Settings::set('email_driver', 'mail');
        Settings::set('minimum_characters', 3);
        Settings::set('date_format', 'F j,Y');
        Settings::set('time_format', 'H:i');

    }

}