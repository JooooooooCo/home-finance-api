<?php

namespace App\Providers;

// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use App\Repositories\CostCenterRepository;
use App\Repositories\TransactionTypeRepository;
use App\Repositories\CashFlow\TransactionRepository;
use App\Repositories\Settings\PaymentTypeRepository;
use App\Repositories\Settings\PrimaryCategoryRepository;
use App\Repositories\Settings\PaymentStatusTypeRepository;
use App\Repositories\Settings\SecondaryCategoryRepository;
use App\Repositories\Interfacies\CostCenterRepositoryInterface;
use App\Repositories\Interfacies\TransactionTypeRepositoryInterface;
use App\Repositories\CashFlow\Interfacies\TransactionRepositoryInterface;
use App\Repositories\Settings\Interfacies\PaymentTypeRepositoryInterface;
use App\Repositories\Settings\Interfacies\PrimaryCategoryRepositoryInterface;
use App\Repositories\Settings\Interfacies\PaymentStatusTypeRepositoryInterface;
use App\Repositories\Settings\Interfacies\SecondaryCategoryRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CostCenterRepositoryInterface::class, CostCenterRepository::class);
        $this->app->bind(TransactionTypeRepositoryInterface::class, TransactionTypeRepository::class);
        $this->app->bind(PaymentTypeRepositoryInterface::class, PaymentTypeRepository::class);
        $this->app->bind(PaymentStatusTypeRepositoryInterface::class, PaymentStatusTypeRepository::class);
        $this->app->bind(PrimaryCategoryRepositoryInterface::class, PrimaryCategoryRepository::class);
        $this->app->bind(SecondaryCategoryRepositoryInterface::class, SecondaryCategoryRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_DEBUG', false)) {
            // TODO: ADJUST TO LOG IN DB AS SERVER IS STATELESS
            // DB::listen(function($query) {
            //     File::append(
            //         storage_path('/logs/query.log'),
            //         '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL . PHP_EOL
            //     );
            // });
        }
    }
}
