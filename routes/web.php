<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Root route - redirect to login if not authenticated
Route::get('/', function () {
    return auth()->check() ? redirect('/slides/search') : redirect('/login');
});

// Authentication routes
Volt::route('/login', 'auth.login')->name('login');
Volt::route('/register', 'auth.register')->name('register');
Volt::route('/forgot-password', 'auth.forgot-password')->name('password.request');
Volt::route('/reset-password/{token}', 'auth.reset-password')->name('password.reset');

// Team routes that require authentication
Route::middleware(['auth'])->group(function () {
    Volt::route('/teams', 'teams.index')->name('teams.index');
    Volt::route('/teams/create', 'teams.create')->name('teams.create');
});

// Routes that require authentication and a team
Route::middleware(['auth', 'ensure.has.team'])->group(function () {
    // Dashboard route
    Volt::route('/dashboard', 'users.index')->name('dashboard');
    
    // Team routes that require a team
    Volt::route('/teams/{teamId}/members', 'teams.members')->name('teams.members');
    Volt::route('/teams/{teamId}/settings', 'teams.settings')->name('teams.settings');
    
    // Slides routes
    Volt::route('/slides/search', 'slides.search')->name('slides.search');
    Volt::route('/slides/view', 'slides.view')->name('slides.view');
});
