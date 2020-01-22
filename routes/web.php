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


Auth::routes();

Route::get('/', 'Auth\LoginController@showLoginForm');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::group([
    'name' => 'division.',
    'prefix' => 'division',
    'middleware' => 'auth'
], function () {
    Route::get('', 'Division\DashboardController@dashboard')->name('home');

    Route::group(['prefix' => 'expense-approval'], function () {
        Route::get('', 'Division\ExpenseController@list')->name('expense-approval');
        Route::post('save', 'Division\ExpenseController@save_approval');
    });

    Route::group(['prefix' => 'indent'], function () {
        Route::get('', 'Division\IndentController@list')->name('raise-indent');
        Route::post('confirm', 'Division\IndentController@confirm')->name('confirm-indent');
        Route::post('save', 'Division\IndentController@save');
    });

    Route::group(['prefix' => 'stn'], function () {
        Route::get('', 'Division\STNController@open_indent_list')->name('open-indents');

        Route::get('view/{ind_nbr}', 'Division\STNController@view_indent')->name('view-indent');
        
        Route::get('allocate/{ind_nbr}', 'Division\STNController@allocate_list')->name('allocate');
        Route::post('allocate/save', 'Division\STNController@allocate_save')->name('allocate-save');

        Route::get('close/{ind_nbr}/{ind_id}', 'Division\STNController@close_indent')->name('indent-close');

        Route::get('stn/{ind_nbr}/{ind_status}', 'Division\STNController@stn_list')->name('stn');
        Route::get('stn/{site}/{item}/{lot}', 'Division\STNController@get_qty_by_lot');
        Route::post('stn/confirm', 'Division\STNController@stn_confirm')->name('stn-confirm');
        Route::post('stn/save', 'Division\STNController@stn_save')->name('stn-save');
    });

    Route::group(['prefix' => 'grr'], function () {
        Route::get('', 'Division\GRRController@grr_list')->name('grr-list');
        Route::get('grr-view/{do_nbr}', 'Division\GRRController@grr_view')->name('grr-view');
        Route::post('grr-save', 'Division\GRRController@grr_save')->name('grr-save');
    });
});

// Route::prefix('division')->group(function () {
//     // Matches The "/division/indent" URL
    
// });

Route::prefix('factory')->group(function () {
});

Route::prefix('accounts')->group(function () {
});
