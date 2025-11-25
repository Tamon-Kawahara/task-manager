<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    // タグ一覧＋登録フォーム
    public function index()
    {
        $tags = Tag::orderBy('name')->get();

        return view('tags.index', compact('tags'));
    }

    // タグ登録
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'nullable|string|max:50',
        ]);

        // slug の基準を決める
        $baseSlug = $validated['slug'];

        // slug が未入力なら name を使う（日本語でもOK）
        if (empty($baseSlug)) {
            $baseSlug = $validated['name'];
        }

        // 念のため空文字を避ける
        if ($baseSlug === '') {
            $baseSlug = Str::random(8);
        }

        // 既存 slug と被らないように連番でユニーク化
        $slug = $baseSlug;
        $counter = 1;

        while (Tag::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        Tag::create([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return redirect()
            ->route('tags.index')
            ->with('success', 'タグを登録しました。');
    }

    // 消したいなら（任意）
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()
            ->route('tags.index')
            ->with('success', 'タグを削除しました。');
    }
}
