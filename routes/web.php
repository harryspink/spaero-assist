<?php

use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Root route - redirect to login if not authenticated
Route::get('/', function () {
    return auth()->check() ? redirect('/parts/search') : redirect('/login');
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

// Stripe webhook route
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');

// Routes that require authentication and a team
Route::middleware(['auth', 'ensure.has.team'])->group(function () {
    // Dashboard route
    Volt::route('/dashboard', 'users.index')->name('dashboard');
    
    // Team routes that require a team
    Volt::route('/teams/{teamId}/members', 'teams.members')->name('teams.members');
    Volt::route('/teams/{teamId}/settings', 'teams.settings')->name('teams.settings');
    Volt::route('/teams/{teamId}/billing', 'teams.billing')->name('teams.billing');
    Volt::route('/teams/{teamId}/credentials', 'teams.credentials')->name('teams.credentials');
    
    // Parts routes
    Volt::route('/parts/search', 'parts.search')->name('parts.search');
    Route::get('/parts/chat-search', \App\Livewire\Parts\ChatSearch::class)->name('parts.chat-search');
    Volt::route('/parts/view', 'parts.view')->name('parts.view');
    Route::get('/parts/search-history', \App\Livewire\Parts\SearchHistory::class)->name('parts.search-history');
    Route::get('/parts/search-history/{id}/results', \App\Livewire\Parts\ViewStoredResults::class)->name('parts.search-history.results');
});
