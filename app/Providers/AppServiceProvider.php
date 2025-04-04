<?php

namespace App\Providers;

// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use App\Repositories\CostCenterRepository;
use App\Repositories\Settings\PaymentTypeRepository;
use App\Repositories\Settings\PaymentStatusTypeRepository;
use App\Repositories\Interfacies\CostCenterRepositoryInterface;
use App\Repositories\Settings\Interfacies\PaymentTypeRepositoryInterface;
use App\Repositories\Settings\Interfacies\PaymentStatusTypeRepositoryInterface;

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
        $this->app->bind(PaymentTypeRepositoryInterface::class, PaymentTypeRepository::class);
        $this->app->bind(PaymentStatusTypeRepositoryInterface::class, PaymentStatusTypeRepository::class);
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
