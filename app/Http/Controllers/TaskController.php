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
    // ログイン中のユーザーを取得
        /** @var User $user */
        $user = auth()->user();

        $sort = $request->input('sort');  // 並び替え指定（後でフォームから飛んでくるやつ）

        $query = $user->tasks()
            ->with('tags');   // タグを同時にロードして N+1 回避
        $status = $request->input('status');
        $hideCompleted = $request->boolean('hide_completed');

        if ($sort === 'due_asc') {
            // 期限が近い順
            $query->orderBy('due_date', 'asc')->orderBy('priority', 'desc');
        } elseif ($sort === 'due_desc') {
            // 期限が遠い順
            $query->orderBy('due_date', 'desc');
        } elseif ($sort === 'priority_desc') {
            // 優先度 高い順
            $query->orderBy('priority', 'desc'); // 1:高, 3:低 なので asc
        } elseif ($sort === 'custom') {
            // ★ カスタム順（sort_order）
            $query->orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'desc');
        } else {
            // デフォルト：作成日の新しい順
            $query->latest();
        }

        // キーワード検索（タイトル）
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where('title', 'LIKE', "%{$keyword}%");
        }

        // ★ ステータス絞り込みを追加
        if ($request->filled('status')) {
            $status = $request->input('status');  // not_started / in_progress / completed のどれか
            $query->where('status', $status);
        }

        // 優先度絞り込み
        if ($request->filled('priority')) {
            $priority = (int) $request->priority;
            $query->where('priority', $priority);
        }

        // タグ絞り込み
        $tagId = $request->input('tag_id');   // フォーム側の <select name="tag_id">
        if (!empty($tagId)) {
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        // 「完了タスクを非表示」フラグ
        if ($hideCompleted && $status !== \App\Models\Task::STATUS_COMPLETED) {
            $query->where('status', '!=', \App\Models\Task::STATUS_COMPLETED);
        }

        // 最後にページネーションをかける
        $tasks = $query
            ->paginate(10)       // 1ページ10件
            ->withQueryString(); // 検索条件をURLに維持したままページング

        // タグ一覧（セレクトボックス用）
        $tags = Tag::orderBy('name')->get();

        // resources/views/tasks/index.blade.php にデータを渡して表示
        return view('tasks.index', [
            'tasks' => $tasks,
            'tags'  => $tags,
            'tagId' => $tagId,
            'sort'  => $sort,
            'hideCompleted' => $hideCompleted,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $statusOptions = Task::statusOptions();
        $tags = Tag::orderBy('name')->get();

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
