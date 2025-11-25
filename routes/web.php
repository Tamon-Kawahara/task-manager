<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ルートパスはダッシュボードにリダイレクト
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// 認証が必要なルートを 1 つのグループにまとめる
Route::middleware('auth')->group(function () {

    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // プロフィール
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ▼▼▼ ここからタスク関連 ▼▼▼

    // ★ アーカイブ一覧 & 復元
    //   ※ Route::resource より「前」に書くのが重要！
    Route::get('/tasks/archive', [TaskController::class, 'archiveIndex'])
        ->name('tasks.archive');

    Route::patch('/tasks/{task}/restore', [TaskController::class, 'restore'])
        ->name('tasks.restore');

    // 並び替え（ドラッグ & ドロップ）
    Route::post('/tasks/reorder', [TaskController::class, 'reorder'])
        ->name('tasks.reorder');

    // ステータスだけを更新するルート
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])
        ->name('tasks.updateStatus');

    // タスクの CRUD 一式
    Route::resource('tasks', TaskController::class);

    // ▼▼▼ タグ関連 ▼▼▼
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
});

// 認証系のルート
require __DIR__ . '/auth.php';
