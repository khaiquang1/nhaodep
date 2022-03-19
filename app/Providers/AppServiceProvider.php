<?php

namespace App\Providers;

use App\Models\Contract;
use App\Repositories\ContractRepository;
use App\Repositories\ContractRepositoryEloquent;
use App\Repositories\ExcelRepository;
use App\Repositories\ExcelRepositoryDefault;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Excel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        $this->setDbConfigurations();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(DropboxServiceProvider::class);
        $this->app->register(RepositoryServiceProvider::class);

        $this->app->bind(ContractRepository::class, function ($app) {
            return new ContractRepositoryEloquent(new Contract());
        });

        $this->app->bind(ExcelRepository::class, function ($app) {

            $excel = new Excel(
                $app['phpexcel'],
                $app['excel.reader'],
                $app['excel.writer'],
                $app['excel.parsers.view']
            );

            return new ExcelRepositoryDefault($excel);
        });

    }

    private function setDbConfigurations()
    {
        try {
            //Pusher
            config(['broadcasting.connections.pusher.key' => Settings::get('pusher_key')]);
            Config(['broadcasting.connections.pusher.secret' => Settings::get('pusher_secret')]);
            Config(['broadcasting.connections.pusher.app_id' => Settings::get('pusher_app_id')]);

            //Backup Manager
            //Backup Manager
            config(['backup.backup.destination.disks' => Settings::get('backup_type')]);
            config(['backup.monitorBackups.disks' => Settings::get('backup_type')]);

            //DISK Amazon S3
            Config(['filesystems.disks.s3.key' => Settings::get('disk_aws_key')]);
            Config(['filesystems.disks.s3.secret' => Settings::get('disk_aws_secret')]);
            Config(['filesystems.disks.s3.region' => Settings::get('disk_aws_region')]);
            Config(['filesystems.disks.s3.bucket' => Settings::get('disk_aws_bucket')]);

            //DISK Dropbox
            Config(['filesystems.disks.dropbox.accessToken' => Settings::get('disk_dbox_token')]);

            //Stripe
            Config(['services.stripe.secret' => Settings::get('stripe_secret')]);
            Config(['services.stripe.key' => Settings::get('stripe_publishable')]);

            //Mailserver
//            Config(['mail.driver' => ((Settings::get('email_driver') == null) ? Settings::get('email_driver') : 'mail')]);
//            Config(['mail.host' => Settings::get('email_host')]);
//            Config(['mail.port' => Settings::get('email_port')]);
//            Config(['mail.username' => Settings::get('email_username')]);
//            Config(['mail.password' => Settings::get('email_password')]);

        } catch (\Exception $e) {

        }
    }
}
