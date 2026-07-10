<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'home')->name('home');
Route::livewire('/terms', 'terms')->name('terms');
Route::livewire('/privacy', 'privacy')->name('privacy');
Route::livewire('/contact', 'contact')->name('contact');
