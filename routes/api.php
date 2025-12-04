<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChoraleController;
use App\Http\Controllers\PartitionController;
use App\Http\Controllers\VoicePartController;
use App\Http\Controllers\VocaliseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Api\MesseController;

// Public routes
Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);
Route::post("/check-phone", [AuthController::class, "checkPhone"]);
Route::post("/request-otp", [AuthController::class, "requestOTP"]);
Route::post("/verify-otp", [AuthController::class, "verifyOTP"]);
// Route dépréciée - utiliser request-otp et verify-otp à la place
Route::post("/login-by-phone", [AuthController::class, "loginByPhone"]);

// Public chorale routes (accessible sans authentification)
Route::get("/chorales", [ChoraleController::class, "index"]);
Route::get("/chorales/{id}", [ChoraleController::class, "show"]);
Route::get("/chorales/{id}/pupitres", [ChoraleController::class, "getPupitres"]);

// Protected routes
Route::middleware("auth:sanctum")->group(function () {
    // Auth routes
    Route::post("/logout", [AuthController::class, "logout"]);
    Route::get("/me", [AuthController::class, "me"]);
    Route::put("/me", [AuthController::class, "updateProfile"]);

    // Admin routes
    Route::middleware("admin")->group(function () {
        Route::get("/admin/pending-users", [AdminController::class, "getPendingUsers"]);
        Route::post("/admin/approve-user/{id}", [AdminController::class, "approveUser"]);
        Route::post("/admin/reject-user/{id}", [AdminController::class, "rejectUser"]);
        Route::get("/admin/users", [AdminController::class, "getAllUsers"]);
        Route::get("/admin/stats", [AdminController::class, "getDashboardStats"]);
        Route::post("/admin/make-admin/{id}", [AdminController::class, "makeAdmin"]);
        Route::post("/admin/remove-admin/{id}", [AdminController::class, "removeAdmin"]);
        Route::post("/admin/users/{id}/activate", [AdminController::class, "activateUser"]);
        Route::post("/admin/users/{id}/deactivate", [AdminController::class, "deactivateUser"]);
    });

    // Protected chorale routes (création, modification, suppression)
    Route::post("/chorales", [ChoraleController::class, "store"]);
    Route::put("/chorales/{id}", [ChoraleController::class, "update"]);
    Route::delete("/chorales/{id}", [ChoraleController::class, "destroy"]);

    // Category routes
    Route::apiResource("categories", CategoryController::class);

    // Partition routes (système unifié)
    Route::get("/partitions/sync", [PartitionController::class, "getForSync"]);
    Route::get("/partitions/{id}/download/{fileIndex}", [PartitionController::class, "downloadFile"]);
    Route::apiResource("partitions", PartitionController::class);

    // Voice part routes (pour compatibilité)
    Route::apiResource("voice-parts", VoicePartController::class);
    Route::put("/voice-parts/{id}/partition-voix", [VoicePartController::class, "updatePartitionVoix"]);
    Route::put("/voice-parts/{id}/partition-musique", [VoicePartController::class, "updatePartitionMusique"]);
    Route::post("/voice-parts/{id}/upload-audio", [VoicePartController::class, "uploadAudio"]);

    // Vocalise routes (pour compatibilité)
    Route::get("/vocalises/sync", [VocaliseController::class, "getForSync"]);
    Route::get("/vocalises/{id}/download-audio", [VocaliseController::class, "downloadAudio"]);
    Route::apiResource("vocalises", VocaliseController::class);

    // Messe routes (nouveau système - basé sur RubriqueSection)
    Route::apiResource("messes", MesseController::class);
    Route::get("/messes/{id}/sections", [MesseController::class, "sections"]);
    Route::get("/references/{id}/partitions", [MesseController::class, "partitions"]);
    
    // Routes pour l'importation
    Route::delete("/messes/clear-all", [MesseController::class, "clearAll"]);
});
 