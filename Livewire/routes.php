<?php

use App\Modules\Log\Livewire\LogAppTable;
use App\Modules\Log\Livewire\LogUsersTable;
use Illuminate\Support\Facades\Route;

Route::get('log/users', LogUsersTable::class)
    ->middleware(['web'])
    ->name('log.users')
    ->crumbs(fn ($crumbs) => $crumbs->push('Logs', route_lang('log.users')));

Route::get('log/app', LogAppTable::class)
    ->middleware(['web'])
    ->name('log.app')
    ->crumbs(fn ($crumbs) => $crumbs->push('App Logs', route_lang('log.app')));
