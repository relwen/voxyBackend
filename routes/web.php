<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController as WebAuthController;
use App\Http\Controllers\Web\AdminController as WebAdminController;
use App\Http\Controllers\Web\VocaliseController as WebVocaliseController;
use App\Http\Controllers\Web\ReferenceController as WebReferenceController;
use App\Http\Controllers\Web\MesseController as WebMesseController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return 'Ceci est une page de test !';
});

// Routes d'authentification web
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Routes protégées pour l'administration
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [WebAdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // Gestion des utilisateurs
    Route::get('/admin/users', [WebAdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/users/create', [WebAdminController::class, 'createUser'])->name('admin.users.create');
    Route::post('/admin/users', [WebAdminController::class, 'storeUser'])->name('admin.users.store');
    Route::get('/admin/users/{id}/edit', [WebAdminController::class, 'editUser'])->name('admin.users.edit');
    Route::post('/admin/users/{id}/update', [WebAdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/admin/users/{id}/approve', [WebAdminController::class, 'approveUser'])->name('admin.users.approve');
    Route::post('/admin/users/{id}/reject', [WebAdminController::class, 'rejectUser'])->name('admin.users.reject');
    Route::post('/admin/users/{id}/disapprove', [WebAdminController::class, 'disapproveUser'])->name('admin.users.disapprove');
    Route::post('/admin/users/{id}/activate', [WebAdminController::class, 'activateUser'])->name('admin.users.activate');
    Route::post('/admin/users/{id}/deactivate', [WebAdminController::class, 'deactivateUser'])->name('admin.users.deactivate');
    Route::post('/admin/users/{id}/delete', [WebAdminController::class, 'deleteUser'])->name('admin.users.delete');
    
    // Gestion des chorales
    Route::get('/admin/chorales', [WebAdminController::class, 'chorales'])->name('admin.chorales');
    Route::get('/admin/chorales/create', [WebAdminController::class, 'createChorale'])->name('admin.chorales.create');
    Route::post('/admin/chorales', [WebAdminController::class, 'storeChorale'])->name('admin.chorales.store');
    Route::get('/admin/chorales/{id}/edit', [WebAdminController::class, 'editChorale'])->name('admin.chorales.edit');
    Route::post('/admin/chorales/{id}/update', [WebAdminController::class, 'updateChorale'])->name('admin.chorales.update');
    Route::post('/admin/chorales/{id}/delete', [WebAdminController::class, 'deleteChorale'])->name('admin.chorales.delete');
    
    // Gestion des partitions
    Route::get('/admin/partitions', [WebAdminController::class, 'partitions'])->name('admin.partitions');
    Route::get('/admin/partitions/{id}', [WebAdminController::class, 'showPartition'])->name('admin.partitions.show');
    Route::get('/admin/partitions/create', [WebAdminController::class, 'createPartition'])->name('admin.partitions.create');
    Route::post('/admin/partitions', [WebAdminController::class, 'storePartition'])->name('admin.partitions.store');
    Route::get('/admin/partitions/{id}/edit', [WebAdminController::class, 'editPartition'])->name('admin.partitions.edit');
    Route::post('/admin/partitions/{id}/update', [WebAdminController::class, 'updatePartition'])->name('admin.partitions.update');
    Route::post('/admin/partitions/{id}/delete', [WebAdminController::class, 'deletePartition'])->name('admin.partitions.delete');
    
    // Gestion des catégories
    Route::get('/admin/categories', [WebAdminController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/categories/create', [WebAdminController::class, 'createCategory'])->name('admin.categories.create');
    Route::post('/admin/categories', [WebAdminController::class, 'storeCategory'])->name('admin.categories.store');
    Route::get('/admin/categories/{id}/edit', [WebAdminController::class, 'editCategory'])->name('admin.categories.edit');
    Route::post('/admin/categories/{id}/update', [WebAdminController::class, 'updateCategory'])->name('admin.categories.update');
    Route::post('/admin/categories/{id}/delete', [WebAdminController::class, 'deleteCategory'])->name('admin.categories.delete');
    
    // Gestion des messes
    Route::get('/admin/messes', [WebMesseController::class, 'index'])->name('admin.messes.index');
    Route::get('/admin/messes/{id}', [WebMesseController::class, 'show'])->name('admin.messes.show');
    Route::get('/admin/messes/create', [WebMesseController::class, 'create'])->name('admin.messes.create');
    Route::post('/admin/messes', [WebMesseController::class, 'store'])->name('admin.messes.store');
    Route::get('/admin/messes/{id}/edit', [WebMesseController::class, 'edit'])->name('admin.messes.edit');
    Route::post('/admin/messes/{id}/update', [WebMesseController::class, 'update'])->name('admin.messes.update');
    Route::post('/admin/messes/{id}/delete', [WebMesseController::class, 'destroy'])->name('admin.messes.delete');
    Route::get('/admin/messes/{id}/references', [WebMesseController::class, 'references'])->name('admin.messes.references');
    
    // Accès aux fichiers
    Route::get('/admin/files/partition/{partitionId}/{fileType}/{fileIndex}', [WebMesseController::class, 'serveFile'])->name('admin.files.serve');
    
    // Gestion des références
    Route::get('/admin/references', [WebReferenceController::class, 'index'])->name('admin.references.index');
    Route::get('/admin/references/create', [WebReferenceController::class, 'create'])->name('admin.references.create');
    Route::post('/admin/references', [WebReferenceController::class, 'store'])->name('admin.references.store');
    Route::get('/admin/references/{id}/edit', [WebReferenceController::class, 'edit'])->name('admin.references.edit');
    Route::post('/admin/references/{id}/update', [WebReferenceController::class, 'update'])->name('admin.references.update');
    Route::post('/admin/references/{id}/delete', [WebReferenceController::class, 'destroy'])->name('admin.references.delete');
    Route::get('/admin/references/messe/{messeId}', [WebReferenceController::class, 'getByMesse'])->name('admin.references.by-messe');
    
    // Gestion des partitions des sections
    Route::get('/admin/references/{id}/partitions', [WebReferenceController::class, 'partitions'])->name('admin.references.partitions');
    Route::get('/admin/references/{id}/partitions/create', [WebReferenceController::class, 'createPartition'])->name('admin.references.create-partition');
    Route::post('/admin/references/{id}/partitions', [WebReferenceController::class, 'storePartition'])->name('admin.references.store-partition');
    
    // Gestion des vocalises
    Route::get('/admin/vocalises', [WebVocaliseController::class, 'index'])->name('admin.vocalises.index');
    Route::get('/admin/vocalises/create', [WebVocaliseController::class, 'create'])->name('admin.vocalises.create');
    Route::post('/admin/vocalises', [WebVocaliseController::class, 'store'])->name('admin.vocalises.store');
    Route::get('/admin/vocalises/{id}/edit', [WebVocaliseController::class, 'edit'])->name('admin.vocalises.edit');
    Route::post('/admin/vocalises/{id}/update', [WebVocaliseController::class, 'update'])->name('admin.vocalises.update');
    Route::post('/admin/vocalises/{id}/delete', [WebVocaliseController::class, 'destroy'])->name('admin.vocalises.delete');
    Route::get('/admin/chorales/{choraleId}/vocalises', [WebVocaliseController::class, 'byChorale'])->name('admin.vocalises.by-chorale');
});

// Route publique pour accéder aux fichiers (sans authentification)
Route::get('/files/partition/{partitionId}/{fileType}/{fileIndex}', [App\Http\Controllers\Web\MesseController::class, 'serveFile'])->name('files.serve');
