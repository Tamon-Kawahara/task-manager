<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // ログイン中のユーザーを取得
        /** @var User $user */
        $user = auth()->user();

        // ログイン中ユーザーのタスクを新しい順に10件ずつ取得
        $tasks = $user->tasks()
            ->latest()          // created_at の新しい順
            ->paginate(10);     // 1ページ10件でページネーション

        // resources/views/tasks/index.blade.php にデータを渡して表示
        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $statusOptions = Task::statusOptions();

        return view('tasks.create', compact('statusOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // バリデーション
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|integer|in:1,2,3',
            'due_date'    => 'nullable|date',
            'status'      => 'required|string|in:' . implode(',', array_keys(Task::statusOptions())),
        ]);

        // デフォルトで status が無い場合の保険（ほぼ来ないけど念のため）
        $validated['status'] = $validated['status'] ?? Task::STATUS_NOT_STARTED;

        // タスク作成
        $user->tasks()->create($validated);

        // 完了後のリダイレクト
        return redirect()->route('tasks.index')->with('success', 'タスクを作成しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        // ログインユーザー以外のタスクを編集させない簡易チェック
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        $statusOptions = Task::statusOptions();

        // 編集フォーム用のビューに Task を渡す
        return view('tasks.edit', compact('task', 'statusOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        /** @var User $user */
        $user = auth()->user();

        // 他人のタスクを更新させない
        if ($task->user_id !== $user->id) {
            abort(403);
        }

        // バリデーション（store と合わせる）
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|integer|in:1,2,3',
            'due_date'    => 'nullable|date',
            'status'      => 'required|string|in:' . implode(',', array_keys(Task::statusOptions())),
        ]);

        // 更新
        $task->update($validated);

        // 完了後のリダイレクト
        return redirect()
            ->route('tasks.index')
            ->with('success', 'タスクを更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // 他人のタスク削除を阻止
        if ($task->user_id !== $user->id) {
            abort(403);
        }

        // ソフトデリート（trash へ移動）
        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'タスクを削除しました。');
    }

    /**
     * タスクのステータスだけを更新するアクション
     */
    public function updateStatus(Request $request, Task $task)
    {
        /** @var User $user */
        $user = auth()->user();

        // 他人のタスクを触らせない
        if ($task->user_id !== $user->id) {
            abort(403);
        }

        // ステータスのバリデーション
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(Task::statusOptions())),
        ]);

        // ステータスだけ更新
        $task->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'ステータスを更新しました。');
    }
}
