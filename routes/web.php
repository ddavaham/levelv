<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::match(['GET'],'/', 'PublicController@home')->name('home');
// Route::match(['GET'],'/donate', 'PublicController@donate')->name('donate');


Route::match(['GET'],'/login', 'AuthController@login')->name('auth.login');
Route::match(['GET'],'/logout', 'AuthController@logout')->name('auth.logout');


Route::match(['GET'],'/sso/callback', 'SSOController@callback')->name('sso.callback');
Route::match(['GET'],'/sso/revoke', 'SSOController@revoke')->name('sso.revoke');
Route::match(['GET'],'/sso/refresh', 'SSOController@refresh')->name('sso.refresh');

Route::group(['middleware' => ['auth']], function () {

    Route::match(['GET'], '/dashboard', 'PortalController@dashboard')->name('dashboard');
    Route::match(['GET', 'POST'], '/welcome', 'PortalController@welcome')->name('welcome');

    Route::match(['GET'], '/{member}/overview', 'PortalController@overview')->name('overview')->middleware("authorized");
    Route::match(['GET'], '/{member}/clones', 'PortalController@clones')->name('clones')->middleware("authorized:esi-clones.read_clones.v1");
    Route::match(['GET'], '/{member}/skills', 'PortalController@skills')->name('skillz')->middleware("authorized:esi-skills.read_skills.v1");
    Route::match(['GET'], '/{member}/skills/flyable', 'PortalController@flyable')->name('skillz.flyable')->middleware("authorized:esi-skills.read_skills.v1");
    Route::match(['GET'], '/{member}/skillqueue', 'PortalController@queue')->name('skillqueue')->middleware("authorized:esi-skills.read_skillqueue.v1");

    Route::match(['GET'], '/settings', 'SettingController@index')->name('settings.index');
    Route::match(['GET', 'DELETE'], '/settings/token', 'SettingController@token')->name('settings.token');
    
});


Route::match(['GET'], '/hack', 'HackingController@index');
