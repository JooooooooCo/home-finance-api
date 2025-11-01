<?php

namespace App\Providers;

// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use App\Repositories\CostCenterRepository;
use App\Repositories\CashFlow\TransactionRepository;
use App\Repositories\Settings\PaymentTypeRepository;
use App\Repositories\Settings\ClassificationRepository;
use App\Repositories\Settings\SubCategoryRepository;
use App\Repositories\Settings\CategoryRepository;
use App\Repositories\Interfacies\CostCenterRepositoryInterface;
use App\Repositories\CashFlow\Interfacies\TransactionRepositoryInterface;
use App\Repositories\Settings\Interfacies\PaymentTypeRepositoryInterface;
use App\Repositories\Settings\Interfacies\ClassificationRepositoryInterface;
use App\Repositories\Settings\Interfacies\SubCategoryRepositoryInterface;
use App\Repositories\Settings\Interfacies\CategoryRepositoryInterface;

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
        $this->app->bind(ClassificationRepositoryInterface::class, ClassificationRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(SubCategoryRepositoryInterface::class, SubCategoryRepository::class);
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
