<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\BorrowRequestController;
use App\Http\Controllers\Admin\ActiveBorrowController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\BorrowingController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'user.dashboard');
    }
    return redirect()->route('login');
});

// Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('reset-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/latest', [NotificationController::class, 'getLatest'])->name('notifications.latest');

    // Admin Routes
    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/monthly-borrowing', [DashboardController::class, 'monthlyBorrowing'])->name('monthly-borrowing');
        Route::get('/monthly-borrowing/export-borrowings', [DashboardController::class, 'exportBorrowings'])->name('monthly-borrowing.export-borrowings');
        Route::get('/monthly-borrowing/export-most-borrowed', [DashboardController::class, 'exportMostBorrowed'])->name('monthly-borrowing.export-most-borrowed');
        Route::get('/monthly-borrowing/export-returned', [DashboardController::class, 'exportReturnedItems'])->name('monthly-borrowing.export-returned');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
        Route::put('/assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');

        Route::get('/borrow-requests', [BorrowRequestController::class, 'index'])->name('borrow-requests.index');
        Route::post('/borrow-requests', [BorrowRequestController::class, 'adminStore'])->name('borrow-requests.store');
        Route::post('/borrow-requests/{borrowing}/approve', [BorrowRequestController::class, 'approve'])->name('borrow-requests.approve');
        Route::post('/borrow-requests/{borrowing}/reject', [BorrowRequestController::class, 'reject'])->name('borrow-requests.reject');
        Route::post('/borrow-requests/{borrowing}/handover', [BorrowRequestController::class, 'handover'])->name('borrow-requests.handover');

        Route::get('/active-borrows', [ActiveBorrowController::class, 'index'])->name('active-borrows.index');
        Route::put('/active-borrows/{borrowing}', [ActiveBorrowController::class, 'update'])->name('active-borrows.update');
        Route::post('/active-borrows/{borrowing}/return', [ActiveBorrowController::class, 'markReturned'])->name('active-borrows.return');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    });

    // User Routes
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/borrowings', [BorrowingController::class, 'index'])->name('borrowings.index');
        Route::post('/borrowings', [BorrowingController::class, 'store'])->name('borrowings.store');
        Route::get('/notifications', [NotificationController::class, 'userIndex'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    });
});
