<?php

namespace LevelV\Providers;

use Illuminate\Support\{Collection, ServiceProvider};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Collection::macro('recursive', function () {
            return $this->map(function ($value) {
                if (is_array($value)) {
                    return collect($value)->recursive();
                }
                if (is_object($value)) {
                    return collect($value)->recursive();
                }

                return $value;
            });
        });

        Collection::macro('paginate', function( $perPage, $total = null, $page = null, $pageName = 'page' ) {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage( $pageName );

            return new LengthAwarePaginator( $this->forPage( $page, $perPage ), $total ?: $this->count(), $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        });
    }
}
