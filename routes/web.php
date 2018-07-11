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

Route::match(['GET', 'POST'],'/skillplans', 'SkillPlanController@list')->name('skillplans.list');
Route::match(['GET', 'POST', 'DELETE'],'/skillplan/{skillplan}', 'SkillPlanController@view')->name('skillplan.view');


Route::match(['GET'],'/fittings', 'FittingController@list')->name('fittings.list');
// Route::match(['GET'],'/donate', 'PublicController@donate')->name('donate');


Route::match(['GET'],'/login', 'AuthController@login')->name('auth.login');
Route::match(['GET'],'/logout', 'AuthController@logout')->name('auth.logout');


Route::match(['GET'],'/sso/callback', 'SSOController@callback')->name('sso.callback');
Route::match(['GET'],'/sso/revoke', 'SSOController@revoke')->name('sso.revoke');
Route::match(['GET'],'/sso/refresh', 'SSOController@refresh')->name('sso.refresh');

Route::group(['middleware' => ['auth']], function () {

    Route::match(['GET'], '/dashboard', 'PortalController@dashboard')->name('dashboard');
    Route::match(['GET'], '/dashboard/switch', 'PortalController@switch')->name('dashboard.switch');
    Route::match(['GET', 'POST'], '/welcome', 'PortalController@welcome')->name('welcome');
    Route::match(['GET', 'POST'], '/view/{member}', 'PortalController@overview')->name('overview');
    Route::match(['GET'], '/view/{member}/clones', 'PortalController@clones')->name('clones');
    Route::match(['GET'], '/view/{member}/skills', 'PortalController@skills')->name('skillz');
    Route::match(['GET'], '/view/{member}/skills/flyable', 'PortalController@flyable')->name('skillz.flyable');
    Route::match(['GET'], '/view/{member}/queue', 'PortalController@queue')->name('queue');
    Route::match(['GET'], '/view/{member}/attributes', 'PortalController@attributes')->name('attributes');

    Route::match(['GET', 'POST'],'/view/{member}/skillplans', 'SkillPlanController@list')->name('skillplans.list');
    Route::match(['GET', 'POST', "DELETE"],'/view/{member}/skillplan/{skillplan}', 'SkillPlanController@view')->name('skillplan.view');

    Route::match(['GET'], '/settings', 'SettingController@index')->name('settings.index');
    Route::match(['GET', 'DELETE'], '/settings/token', 'SettingController@token')->name('settings.token');
});


Route::match(['GET'], '/hack', 'HackingController@index');
