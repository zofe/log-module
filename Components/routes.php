<?php


use App\Modules\Log\Components\LogAppTable;
use App\Modules\Log\Components\LogUsersTable;
use Illuminate\Support\Facades\Route;


Route::get('log/users', LogUsersTable::class)
    ->middleware(['web'])
    ->name('log.users')
    ->crumbs(fn ($crumbs) => $crumbs->push('User Logs', route('log.users')));

Route::get('log/app', LogAppTable::class)
    ->middleware(['web'])
    ->name('log.app')
    ->crumbs(fn ($crumbs) => $crumbs->push('App Logs', route('log.app')));
