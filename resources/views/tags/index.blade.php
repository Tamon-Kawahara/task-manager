<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            タグ管理
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- フラッシュメッセージ --}}
                    @if (session('success'))
                        <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 px-4 py-2 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- バリデーションエラー --}}
                    @if ($errors->any())
                        <div class="mb-4 border border-red-200 bg-red-50 text-red-700 text-sm rounded px-4 py-3">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- 新規登録フォーム --}}
                    <form method="POST" action="{{ route('tags.store') }}"
                        class="mb-8 space-y-4 sm:space-y-0 sm:flex sm:flex-wrap sm:items-end sm:gap-4">
                        @csrf

                        <div class="w-full sm:flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">タグ名</label>
                            <input type="text" name="name"
                                class="border rounded w-full px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                value="{{ old('name') }}">
                        </div>

                        <div class="w-full sm:flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                スラッグ（任意）
                                <span class="block text-xs text-gray-500 mt-0.5">
                                    空の場合はタグ名から自動生成されます。
                                </span>
                            </label>
                            <input type="text" name="slug"
                                class="border rounded w-full px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                value="{{ old('slug') }}">
                        </div>

                        <div class="w-full sm:w-auto">
                            <button type="submit"
                                class="inline-flex justify-center items-center px-5 py-2.5 text-sm font-medium rounded bg-blue-600 text-white hover:bg-blue-700 w-full sm:w-auto">
                                追加
                            </button>
                        </div>
                    </form>

                    {{-- タグ一覧 --}}
                    @if ($tags->isEmpty())
                        <div class="bg-white border border-gray-100 rounded-lg px-4 py-6 text-center shadow-sm">
                            <p class="text-sm text-gray-500">
                                まだタグが登録されていません。
                            </p>
                        </div>
                    @else
                        {{-- スマホ用カード一覧 --}}
                        <div class="space-y-3 sm:hidden">
                            @foreach ($tags as $tag)
                                <div
                                    class="bg-white border border-gray-100 rounded-lg px-4 py-3 shadow-sm flex flex-col gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $tag->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            ID：{{ $tag->id }} / スラッグ：{{ $tag->slug ?: '（なし）' }}
                                        </p>
                                    </div>

                                    <div class="flex justify-end">
                                        <form action="{{ route('tags.destroy', $tag) }}" method="POST"
                                            onsubmit="return confirm('このタグを削除しますか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="px-3 py-1.5 border rounded text-xs font-medium text-red-600 hover:bg-red-50">
                                                削除
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- sm 以上用テーブル一覧 --}}
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead>
                                    <tr class="whitespace-nowrap">
                                        <th class="px-4 py-2 border-b w-16">ID</th>
                                        <th class="px-4 py-2 border-b">名前</th>
                                        <th class="px-4 py-2 border-b">スラッグ</th>
                                        <th class="px-4 py-2 border-b w-24"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tags as $tag)
                                        <tr>
                                            <td class="px-4 py-2 border-b text-gray-700">
                                                {{ $tag->id }}
                                            </td>
                                            <td class="px-4 py-2 border-b text-gray-900">
                                                {{ $tag->name }}
                                            </td>
                                            <td class="px-4 py-2 border-b text-gray-700">
                                                {{ $tag->slug ?: '（なし）' }}
                                            </td>
                                            <td class="px-4 py-2 border-b">
                                                <form action="{{ route('tags.destroy', $tag) }}" method="POST"
                                                    onsubmit="return confirm('このタグを削除しますか？');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        class="px-3 py-1 border rounded text-xs text-red-600 hover:bg-red-50">
                                                        削除
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
