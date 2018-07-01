<?php

namespace LevelV\Providers;

use View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\{Collection, ServiceProvider};

use LevelV\Models\ESI\{Alliance, Character, Corporation, Station, Structure, System};
use LevelV\Models\SDE\{Constellation, Region};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'alliance' => Alliance::class,
            'character' => Character::class,
            'constellation' => Constellation::class,
            'corporation' => Corporation::class,
            'region' => Region::class,
            'station' => Station::class,
            'structure' => Structure::class,
            'system' => System::class,
        ]);

        View::composer('*', \LevelV\Composers\ScopeComposer::class);
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
