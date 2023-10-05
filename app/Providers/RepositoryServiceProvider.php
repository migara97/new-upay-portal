<?php

namespace App\Providers;


use App\Repository\AdminUserRepositoryInterface;
use App\Repository\BillerCategoryRepositoryInterface;
use App\Repository\BillerRepositoryInterface;
use App\Repository\DualAuthRepositoryInterface;
use App\Repository\DualAuthSettingsRepositoryInterface;
use App\Repository\Eloquent\AdminUserRepository;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Eloquent\BillerCategoryRepository;
use App\Repository\Eloquent\BillerRepository;
use App\Repository\Eloquent\DualAuthRepository;
use App\Repository\Eloquent\DualAuthSettingsRepository;
use App\Repository\Eloquent\JustpayBankRepository;
use App\Repository\Eloquent\RoleRepository;
use App\Repository\EloquentRepositoryInterface;
use App\Repository\JustpayBankRepositoryInterface;
use App\Repository\RoleRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(EloquentRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(AdminUserRepositoryInterface::class, AdminUserRepository::class);
        $this->app->bind(DualAuthRepositoryInterface::class, DualAuthRepository::class);
        $this->app->bind(DualAuthSettingsRepositoryInterface::class, DualAuthSettingsRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(BillerCategoryRepositoryInterface::class, BillerCategoryRepository::class);
        $this->app->bind(JustpayBankRepositoryInterface::class, JustpayBankRepository::class);
        $this->app->bind(BillerRepositoryInterface::class, BillerRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
