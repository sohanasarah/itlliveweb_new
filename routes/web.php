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

Route::get('/division', 'Division\DashboardController@dashboard')->name('home');

Route::get('/division/expense-approval', 'division\ExpenseController@list')->name('expense-approval');
Route::post('/division/expense-approval/save', 'division\ExpenseController@save_approval');

Route::get('/division/indent', 'division\IndentController@list')->name('raise-indent');
Route::post('/division/indent', 'division\IndentController@confirm')->name('confirm-indent');
Route::post('/division/indent/save', 'division\IndentController@save');

Route::get('/division/stn/open-indents', 'division\STNController@open_indent_list')->name('open-indents');

Route::get('/division/stn/view/{ind_nbr}', 'division\STNController@view_indent')->name('view-indent');
Route::get('/division/stn/allocate/{ind_nbr}', 'division\STNController@allocate_list')->name('allocate');
Route::post('/division/stn/allocate/save', 'division\STNController@allocate_save')->name('allocate-save');

Route::get('/division/stn/close/{ind_nbr}/{ind_id}', 'division\STNController@close_indent')->name('indent-close');

Route::get('/division/stn/stn/{ind_nbr}/{ind_status}', 'division\STNController@stn_list')->name('stn');
Route::get('/division/stn/stn/{site}/{item}/{lot}', 'division\STNController@get_qty_by_lot');
Route::post('/division/stn/stn/confirm', 'division\STNController@stn_confirm')->name('stn-confirm');

Route::post('/division/stn/stn/save', 'division\STNController@stn_save')->name('stn-save');

Route::get('/division/grr', 'division\IndentController@list')->name('grr');


