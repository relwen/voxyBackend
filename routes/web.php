<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController as WebAuthController;
use App\Http\Controllers\Web\AdminController as WebAdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return 'Ceci est une page de test !';
});

// Routes d'authentification web (publiques)
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::get('/login-maestro', [WebAuthController::class, 'showMaestroLoginForm'])->name('login.maestro');
Route::post('/login-maestro', [WebAuthController::class, 'loginMaestro'])->name('login.maestro.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Routes d'inscription pour créer une chorale (publiques - pas besoin d'être admin)
Route::get('/register-chorale', [WebAuthController::class, 'showRegisterForm'])->name('register.chorale');
Route::post('/register-chorale', [WebAuthController::class, 'registerChorale'])->name('register.chorale.store');

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
});

// Configuration de la chorale (pupitres et rubriques) - Accessible aux maestros et admins
Route::middleware(['auth', 'maestro'])->group(function () {
    Route::get('/admin/chorale/config', [App\Http\Controllers\Web\ChoraleConfigController::class, 'index'])->name('admin.chorale.config');
    Route::post('/admin/chorale/pupitres', [App\Http\Controllers\Web\ChoraleConfigController::class, 'storePupitre'])->name('admin.chorale.pupitres.store');
    Route::get('/admin/chorale/pupitres/{id}', [App\Http\Controllers\Web\ChoraleConfigController::class, 'showPupitre'])->name('admin.chorale.pupitres.show');
    Route::put('/admin/chorale/pupitres/{id}', [App\Http\Controllers\Web\ChoraleConfigController::class, 'updatePupitre'])->name('admin.chorale.pupitres.update');
    Route::delete('/admin/chorale/pupitres/{id}', [App\Http\Controllers\Web\ChoraleConfigController::class, 'destroyPupitre'])->name('admin.chorale.pupitres.destroy');
    Route::post('/admin/chorale/categories', [App\Http\Controllers\Web\ChoraleConfigController::class, 'storeCategory'])->name('admin.chorale.categories.store');
    Route::get('/admin/chorale/categories/{id}', [App\Http\Controllers\Web\ChoraleConfigController::class, 'showCategory'])->name('admin.chorale.categories.show');
    Route::put('/admin/chorale/categories/{id}', [App\Http\Controllers\Web\ChoraleConfigController::class, 'updateCategory'])->name('admin.chorale.categories.update');
    Route::delete('/admin/chorale/categories/{id}', [App\Http\Controllers\Web\ChoraleConfigController::class, 'destroyCategory'])->name('admin.chorale.categories.destroy');
    Route::post('/admin/chorale/apply-template', [App\Http\Controllers\Web\ChoraleConfigController::class, 'applyTemplate'])->name('admin.chorale.apply-template');
    
    // Gestion des rubriques et leurs sections
    Route::get('/admin/rubriques/{id}', [App\Http\Controllers\Web\RubriqueController::class, 'show'])->name('admin.rubriques.show');
    Route::get('/admin/rubriques/{rubriqueId}/messes/{messeId}', [App\Http\Controllers\Web\RubriqueController::class, 'showMesse'])->name('admin.rubriques.messes.show');
    Route::post('/admin/rubriques/{id}/messes', [App\Http\Controllers\Web\RubriqueController::class, 'storeMesse'])->name('admin.rubriques.messes.store');
    Route::post('/admin/rubriques/{rubriqueId}/messes/{messeId}/partitions', [App\Http\Controllers\Web\RubriqueController::class, 'storePartitionForMessePart'])->name('admin.rubriques.messes.partitions.store');
    Route::post('/admin/rubriques/{id}/sections', [App\Http\Controllers\Web\RubriqueController::class, 'storeSection'])->name('admin.rubriques.sections.store');
    Route::get('/admin/rubriques/{rubriqueId}/sections/{sectionId}', [App\Http\Controllers\Web\RubriqueController::class, 'showSection'])->name('admin.rubriques.sections.show');
    Route::put('/admin/rubriques/{rubriqueId}/sections/{sectionId}', [App\Http\Controllers\Web\RubriqueController::class, 'updateSection'])->name('admin.rubriques.sections.update');
    Route::delete('/admin/rubriques/{rubriqueId}/sections/{sectionId}', [App\Http\Controllers\Web\RubriqueController::class, 'destroySection'])->name('admin.rubriques.sections.destroy');
    Route::post('/admin/rubriques/{id}/partitions', [App\Http\Controllers\Web\RubriqueController::class, 'storePartitionDirect'])->name('admin.rubriques.partitions.store');
    Route::post('/admin/rubriques/{rubriqueId}/sections/{sectionId}/partitions', [App\Http\Controllers\Web\RubriqueController::class, 'storePartition'])->name('admin.rubriques.sections.partitions.store');
    
    // Routes pour voir et modifier les partitions (accessibles aux maestros)
    Route::get('/admin/partitions/{id}', [WebAdminController::class, 'showPartition'])->name('admin.partitions.show');
    Route::get('/admin/partitions/{id}/edit', [WebAdminController::class, 'editPartition'])->name('admin.partitions.edit');
    Route::post('/admin/partitions/{id}/update', [WebAdminController::class, 'updatePartition'])->name('admin.partitions.update');
    
    // Gestion des utilisateurs pour les maestros
    Route::get('/admin/maestro/users', [WebAdminController::class, 'maestroUsers'])->name('admin.maestro.users');
    Route::post('/admin/maestro/users/{id}/approve', [WebAdminController::class, 'maestroApproveUser'])->name('admin.maestro.users.approve');
    Route::post('/admin/maestro/users/{id}/reject', [WebAdminController::class, 'maestroRejectUser'])->name('admin.maestro.users.reject');
    Route::post('/admin/maestro/users/{id}/delete', [WebAdminController::class, 'maestroDeleteUser'])->name('admin.maestro.users.delete');
});

// Routes protégées pour l'administration (suite)
Route::middleware(['auth', 'admin'])->group(function () {
    // Gestion des partitions (liste et création - admin uniquement)
    Route::get('/admin/partitions', [WebAdminController::class, 'partitions'])->name('admin.partitions');
    Route::get('/admin/partitions/create', [WebAdminController::class, 'createPartition'])->name('admin.partitions.create');
    Route::post('/admin/partitions', [WebAdminController::class, 'storePartition'])->name('admin.partitions.store');
    // Note: Les routes show, edit et update sont dans le groupe maestro pour permettre l'accès aux maestros
    Route::post('/admin/partitions/{id}/delete', [WebAdminController::class, 'deletePartition'])->name('admin.partitions.delete');
    
    // Gestion des catégories
    Route::get('/admin/categories', [WebAdminController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/categories/create', [WebAdminController::class, 'createCategory'])->name('admin.categories.create');
    Route::post('/admin/categories', [WebAdminController::class, 'storeCategory'])->name('admin.categories.store');
    Route::get('/admin/categories/{id}/edit', [WebAdminController::class, 'editCategory'])->name('admin.categories.edit');
    Route::post('/admin/categories/{id}/update', [WebAdminController::class, 'updateCategory'])->name('admin.categories.update');
    Route::post('/admin/categories/{id}/delete', [WebAdminController::class, 'deleteCategory'])->name('admin.categories.delete');
});

// Route publique pour accéder aux fichiers (sans authentification)
Route::get('/files/partition/{partitionId}/{fileIndex}', [App\Http\Controllers\PartitionController::class, 'downloadFile'])->name('files.serve');
