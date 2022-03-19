<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Repositories\InstallRepository::class,\App\Repositories\InstallRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\InstallRepository::class,\App\Repositories\InstallRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\UserRepository::class, \App\Repositories\UserRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\InviteUserRepository::class,\App\Repositories\InviteUserRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\SalesTeamRepository::class, \App\Repositories\SalesTeamRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\LeadRepository::class, \App\Repositories\LeadRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\CallRepository::class, \App\Repositories\CallRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\CategoryRepository::class, \App\Repositories\CategoryRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\ProductRepository::class, \App\Repositories\ProductRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\CompanyRepository::class, \App\Repositories\CompanyRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\CountryRepository::class, \App\Repositories\CountryRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\StateRepository::class, \App\Repositories\StateRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\CityRepository::class, \App\Repositories\CityRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\CustomerRepository::class, \App\Repositories\CustomerRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\OpportunityRepository::class, \App\Repositories\OpportunityRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\MeetingRepository::class, \App\Repositories\MeetingRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\QuotationTemplateRepository::class, \App\Repositories\QuotationTemplateRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\QuotationRepository::class, \App\Repositories\QuotationRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\SalesOrderRepository::class, \App\Repositories\SalesOrderRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\InvoiceRepository::class, \App\Repositories\InvoiceRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\InvoicePaymentRepository::class, \App\Repositories\InvoicePaymentRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\EmailTemplateRepository::class, \App\Repositories\EmailTemplateRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\OptionRepository::class, \App\Repositories\OptionRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\TaskRepository::class, \App\Repositories\TaskRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\EmailRepository::class, \App\Repositories\EmailRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\ProjectsRepository::class, \App\Repositories\ProjectsRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\DomainTrackingRepository::class, \App\Repositories\DomainTrackingRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\PartnerRepository::class, \App\Repositories\PartnerRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\GroupclientRepository::class, \App\Repositories\GroupclientRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\ClientstatusRepository::class, \App\Repositories\ClientstatusRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\GetdataRepository::class, \App\Repositories\GetdataRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\GroupuserRepository::class, \App\Repositories\GroupuserRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\BotcontentRepository::class, \App\Repositories\BotcontentRepositoryEloquent::class);

        
    }
}
