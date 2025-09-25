<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\CoachDashboardController;
use App\Http\Controllers\ClientThreadController;
use App\Http\Controllers\CoachThreadController;
use App\Http\Controllers\CoachPlanController;
use App\Http\Controllers\ClientPlanController;
use App\Http\Controllers\ClientAssignmentController;
use App\Http\Controllers\ClientWeighInController;
use App\Http\Controllers\SessionLogController;
use App\Http\Controllers\CoachClientController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');


// Intakegesprek
Route::middleware('guest')->group(function () {
    Route::get('/intake', [IntakeController::class, 'start'])->name('intake.start');
    Route::post('/intake/step', [IntakeController::class, 'storeStep'])->name('intake.step');
});

// Checkout
Route::post('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// Wachtwoord instellen
Route::middleware('auth')->group(function () {
    Route::get('/account/password/set', [AccountController::class,'showSetPassword'])->name('account.password.set');
    Route::post('/account/password/set', [AccountController::class,'storeSetPassword'])->name('account.password.store');
});



// Client portal
Route::middleware(['auth','role:client'])->group(function () {
    Route::get('/client', [ClientDashboardController::class, 'index'])->name('client.dashboard');
    Route::get('/client/threads',                [ClientThreadController::class, 'index'])->name('client.threads.index');
    Route::get('/client/threads/create',         [ClientThreadController::class, 'create'])->name('client.threads.create');
    Route::post('/client/threads',               [ClientThreadController::class, 'store'])->name('client.threads.store');
    Route::get('/client/threads/{thread}',       [ClientThreadController::class, 'show'])->name('client.threads.show');
    Route::post('/client/threads/{thread}/msg',  [ClientThreadController::class, 'storeMessage'])->name('client.threads.messages.store');
    Route::get('/client/plan', [ClientPlanController::class, 'show'])->name('client.plan.show');
    Route::post('/client/weigh-ins', [ClientWeighInController::class, 'store'])->name('client.weighins.store');
    Route::post('/client/session-logs', [SessionLogController::class,'store'])->name('client.session_logs.store');
});



// Coach portal
Route::middleware(['auth','role:coach'])->group(function () {
    Route::get('/coach', [CoachDashboardController::class, 'index'])->name('coach.dashboard');
    Route::get('/coach/threads',                 [CoachThreadController::class, 'index'])->name('coach.threads.index');
    Route::get('/coach/threads/{thread}',        [CoachThreadController::class, 'show'])->name('coach.threads.show');
    Route::post('/coach/threads/{thread}/msg',   [CoachThreadController::class, 'storeMessage'])->name('coach.threads.messages.store');
    Route::get('/coach/plans',                 [CoachPlanController::class, 'index'])->name('coach.plans.index');
    Route::get('/coach/plans/create/{client}', [CoachPlanController::class, 'create'])->name('coach.plans.create');
    Route::post('/coach/plans',                [CoachPlanController::class, 'store'])->name('coach.plans.store');
    Route::get('/coach/plans/{plan}',          [CoachPlanController::class, 'show'])->name('coach.plans.show');
    Route::post('/coach/plans/{client}/gen',   [CoachPlanController::class, 'generate'])->name('coach.plans.generate');
    Route::post('/coach/plans/{client}/ai-draft', [CoachPlanController::class, 'aiDraft'])->name('coach.plans.aiDraft');
    Route::post('/coach/plans/{client}/ai-week', [CoachPlanController::class, 'aiDraftWeek'])->name('coach.plans.aiWeek');
    Route::get('/coach/plans/{plan}/edit',  [CoachPlanController::class, 'edit'])->name('coach.plans.edit');
    Route::put('/coach/plans/{plan}',       [CoachPlanController::class, 'update'])->name('coach.plans.update');
    Route::get('/coach/clients/unassigned', [ClientAssignmentController::class, 'index'])->name('coach.clients.unassigned');
    Route::post('/coach/clients/{client}/claim', [ClientAssignmentController::class, 'claim'])->name('coach.clients.claim');
    Route::get('/coach/clients/{client}', [CoachClientController::class, 'show'])->name('coach.clients.show');
});



require __DIR__.'/auth.php';
