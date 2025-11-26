<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        // ▼ リクエストパラメータを先に全部拾っておく
        $sort          = $request->input('sort', '');          // 並び替え種別
        $status        = $request->input('status');            // ステータス
        $hideCompleted = $request->boolean('hide_completed');  // 完了非表示フラグ
        $tagId         = $request->input('tag_id');            // タグID
        $keyword       = $request->input('keyword');           // タイトル検索
        $priority      = $request->filled('priority')
            ? (int) $request->input('priority')
            : null;

        // ▼ ベースとなるクエリ
        $query = $user->tasks()
            ->with('tags');
        // → アーカイブ済みを除外したいならここで where('is_archived', false) とか足す

        // ------------------------------
        // ① 絞り込み条件
        // ------------------------------

        // キーワード検索（タイトル）
        if (!empty($keyword)) {
            $query->where('title', 'LIKE', '%' . $keyword . '%');
        }

        // ステータス絞り込み
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // 優先度絞り込み
        if (!is_null($priority)) {
            $query->where('priority', $priority);
        }

        // タグ絞り込み
        if (!empty($tagId)) {
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        // 「完了タスクを非表示」
        // → すでに「ステータス=completed」で絞っているときは二重で絞らないように条件をつける
        if ($hideCompleted && $status !== Task::STATUS_COMPLETED) {
            $query->where('status', '!=', Task::STATUS_COMPLETED);
        }

        // ------------------------------
        // ② 並び替え
        // ------------------------------
        switch ($sort) {
            case 'due_asc':
                // 期限が近い順 → 同じ期限なら優先度高い順 → ID昇順
                $query->orderBy('due_date', 'asc')
                    ->orderBy('priority', 'desc')
                    ->orderBy('id', 'asc');
                break;

            case 'due_desc':
                // 期限が遠い順 → 同じ期限なら優先度高い順
                $query->orderBy('due_date', 'desc')
                    ->orderBy('priority', 'desc')
                    ->orderBy('id', 'asc');
                break;

            case 'priority_desc':
                // 優先度が高い順（3:高, 1:低）
                $query->orderBy('priority', 'desc')
                    ->orderBy('due_date', 'asc')
                    ->orderBy('id', 'asc');
                break;

            case 'custom':
                // カスタム順（sort_order カラム前提）
                $query->orderBy('sort_order', 'asc')
                    ->orderBy('created_at', 'desc');
                break;

            default:
                // デフォルト：作成日が新しい順
                $query->orderBy('created_at', 'desc');
                break;
        }

        // ------------------------------
        // ③ ページネーション
        // ------------------------------
        $tasks = $query
            ->paginate(10)
            ->withQueryString(); // クエリパラメータを維持してページング

        // タグ一覧（セレクトボックス用）
        $tags = Tag::orderBy('name')->get();

        return view('tasks.index', [
            'tasks'         => $tasks,
            'tags'          => $tags,
            'tagId'         => $tagId,
            'sort'          => $sort,
            'hideCompleted' => $hideCompleted,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // ステータス選択肢（未着手 / 進行中 / 完了）
        $statusOptions = Task::statusOptions();

        // タグ一覧（チェックボックス or セレクト用）
        $tags = Tag::orderBy('name')->get();

        // resources/views/tasks/create.blade.php を表示
        return view('tasks.create', compact('statusOptions', 'tags'));
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
        $tags = Tag::orderBy('name')->get();

        // 編集フォーム用のビューに Task を渡す
        return view('tasks.edit', compact('task', 'statusOptions', 'tags'));
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

        // バリデーション（store と揃える）
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|integer|in:1,2,3',
            'due_date'    => 'nullable|date',
            'status'      => 'required|string|in:' . implode(',', array_keys(Task::statusOptions())),

            // ★ タグ用バリデーションを追加
            'tags'   => 'array',
            'tags.*' => 'integer|exists:tags,id',
        ]);

        // ★ Task 本体の更新（tags はテーブルにないので渡さない）
        $task->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority'    => $validated['priority'],
            'due_date'    => $validated['due_date'] ?? null,
            'status'      => $validated['status'],
        ]);

        // ★ タグの同期（中間テーブル tag_task を更新）
        $task->tags()->sync($validated['tags'] ?? []);

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

    public function reorder(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer',
        ]);

        $order = $data['order'];

        DB::transaction(function () use ($order, $user) {
            foreach ($order as $index => $taskId) {
                $task = $user->tasks()->where('id', $taskId)->first();

                if ($task) {
                    $task->sort_order = $index + 1; // 1,2,3,...
                    $task->save();
                }
            }
        });

        return response()->json(['status' => 'ok']);
    }

    public function archiveIndex()
    {
        /** @var User $user */
        $user = auth()->user();

        // 論理削除されたタスクだけ取得
        $archivedTasks = $user->tasks()
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('tasks.archive', compact('archivedTasks'));
    }

    public function restore($id)
    {
        /** @var User $user */
        $user = auth()->user();

        $task = $user->tasks()->onlyTrashed()->findOrFail($id);
        $task->restore();

        return redirect()->route('tasks.archive')->with('success', 'タスクを復元しました。');
    }
}
