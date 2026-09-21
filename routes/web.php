<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Admin\RoleController; 
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ChatController;
Route::get('/', function () {
    return view('welcome');
});

//middleware auth lgaya hy : Sirf authenticated (login) user can access this route
 Route::get('/dashboard', function () { return view('layouts.pages.dashboard.index'); })->middleware('auth')->name('dashboard');


 //Get route -> pointing to LoginController function showLoginForm
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
//POST route -> Login process karna (Form submit hone par yeh call hoga).
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
//POST Route: Logout process karna
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// Role Management Routes (Protected by 'auth' middleware)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    
    // Permissions Routes
    Route::get('/roles/{id}/permissions', [RoleController::class, 'getPermissions'])->name('roles.permissions');
    Route::post('/roles/{id}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions');


    //Users CRUD
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // User Roles Assignment
    Route::get('/users/{id}/roles', [UserController::class, 'getUserRoles'])->name('users.roles');
    Route::post('/users/{id}/sync-roles', [UserController::class, 'syncUserRoles'])->name('users.sync-roles');
});

Broadcast::routes(['middleware' => ['auth']]);

Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');
    Route::get('/chat/{userId}/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
    // Chunked file upload ke liye
    Route::post('/chat/upload-chunk', [ChatController::class, 'uploadChunk'])->name('chat.upload-chunk');
    Route::get('/chat/download/{attachmentId}', [ChatController::class, 'download'])->name('chat.download');
    Route::delete('/chat/{messageId}', [ChatController::class, 'destroy'])->name('chat.destroy');
});