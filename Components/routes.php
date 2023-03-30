<?php


use App\Modules\Log\Components\LogUsersTable;
use Illuminate\Support\Facades\Route;


Route::get('log/users', LogUsersTable::class)
    ->middleware(['web'])
    ->name('log.users')
    ->crumbs(fn ($crumbs) => $crumbs->push('User Logs', route('log.users')));

