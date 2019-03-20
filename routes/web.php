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

// Dashboard
Route::get('/', 'HomeController@index')->name('home');

// Login & logout
Route::get('login', 'UserController@index')->name('login');
Route::post('login', 'UserController@doLogin')->name('do-login');
Route::get('logout', 'UserController@doLogout')->name('do-logout');

Route::get('recovery', 'UserController@recovery')->name('recovery');
Route::post('recovery', 'UserController@doRecovery')->name('do-recovery');

// Transactions
Route::get('transactions', 'TransactionController@index')->name('transactions');
Route::get('transaction/delete/{id}', 'TransactionController@doDelete')->name('transaction-do-delete');

Route::get('duplicates', 'TransactionController@duplicates')->name('duplicates');

Route::get('duplicate/save/{id}', 'TransactionController@doDuplicateSave')->name('transaction-do-duplicate-save');
Route::get('duplicate/delete/all', 'TransactionController@doDuplicateDeleteAll')->name('transaction-do-duplicate-delete-all');
Route::get('duplicate/delete/{id}', 'TransactionController@doDuplicateDelete')->name('transaction-do-duplicate-delete');

// Transaction import
Route::get('import/create', 'ImportController@index')->name('import-create');
Route::post('import/create', 'ImportController@doImport')->name('import-do-create');

Route::get('import/error/{uuid}', 'ImportController@error')->name('import-error');

Route::get('import/delete/{uuid}', 'ImportController@doDelete')->name('import-do-delete');

// Categories
Route::get('categories', 'CategoryController@index')->name('categories');

Route::get('category/create', 'CategoryController@create')->name('category-create');
Route::post('category/create', 'CategoryController@doCreate')->name('category-do-create');

Route::get('category/{id}', 'CategoryController@view')->name('category');

Route::get('category/edit/{id}', 'CategoryController@edit')->name('category-edit');
Route::post('category/edit/{id}', 'CategoryController@doEdit')->name('category-do-edit');
Route::post('category/edit/{id}/conditions', 'CategoryController@doEditConditions')->name('category-do-edit-conditions');

Route::get('category/delete/{id}', 'CategoryController@doDelete')->name('category-do-delete');
Route::get('category/delete/{id}/condition/{i}', 'CategoryController@doDeleteCondition')->name('category-do-delete-condition');

Route::get('category/run/{id}', 'CategoryController@doRunConditions')->name('category-do-run');

// Budgets
Route::get('budgets', 'BudgetController@index')->name('budgets');

Route::get('budget/create', 'BudgetController@create')->name('budget-create');
Route::post('budget/create', 'BudgetController@doCreate')->name('budget-do-create');

Route::get('budget/{id}', 'BudgetController@view')->name('budget');

Route::get('budget/edit/{id}', 'BudgetController@edit')->name('budget-edit');
Route::post('budget/edit/{id}', 'BudgetController@doEdit')->name('budget-do-edit');
Route::post('budget/edit/{id}/conditions', 'BudgetController@doEditConditions')->name('budget-do-edit-conditions');
Route::post('budget/edit/{id}/amounts', 'BudgetController@doEditAmounts')->name('budget-do-edit-amounts');

Route::get('budget/delete/{id}', 'BudgetController@doDelete')->name('budget-do-delete');
Route::get('budget/delete/{id}/condition/{i}', 'BudgetController@doDeleteCondition')->name('budget-do-delete-condition');

Route::get('budget/run/{id}', 'BudgetController@doRunConditions')->name('budget-do-run');

// Accounts
Route::get('accounts', 'AccountController@index')->name('accounts');

Route::get('account/create', 'AccountController@create')->name('account-create');
Route::post('account/create', 'AccountController@doCreate')->name('account-do-create');

Route::get('account/edit/{id}', 'AccountController@edit')->name('account-edit');
Route::post('account/edit/{id}', 'AccountController@doEdit')->name('account-do-edit');

Route::get('account/delete/{id}', 'AccountController@doDelete')->name('account-do-delete');

// Installer
Route::get('install', 'InstallController@index')->name('install');
Route::post('install', 'InstallController@doInstall')->name('do-install');

// Ajax
Route::get('ajax/budget_by_year', 'AjaxController@budgetByYear')->name('ajax-budget-by-year');
Route::get('ajax/transactions_scroll', 'AjaxController@transactionsScroll')->name('ajax-transactions-scroll');
Route::get('ajax/transactions_filter', 'AjaxController@transactionsFilter')->name('ajax-transactions-filter');