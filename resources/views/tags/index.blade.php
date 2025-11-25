<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            タグ管理
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('success'))
                        <div class="mb-4 text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- 新規登録フォーム --}}
                    <form method="POST" action="{{ route('tags.store') }}" class="mb-6 flex gap-4 items-end">
                        @csrf

                        <div class="flex-1">
                            <label class="block text-sm font-medium mb-1">タグ名</label>
                            <input type="text" name="name" class="border rounded w-full p-2"
                                value="{{ old('name') }}">
                        </div>

                        <div class="flex-1">
                            <label class="block text-sm font-medium mb-1">スラッグ（任意） <span
                                    class="text-xs text-gray-500 mt-1">
                                    空の場合はタグ名から自動生成されます。
                            </span></label>
                            <input type="text" name="slug" class="border rounded w-full p-2"
                                value="{{ old('slug') }}">
                        </div>

                        <div>
                            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                                追加
                            </button>
                        </div>
                    </form>

                    @if ($errors->any())
                        <div class="mb-4 text-red-600">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- タグ一覧 --}}
                    @if ($tags->isEmpty())
                        <p>まだタグが登録されていません。</p>
                    @else
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 border-b">ID</th>
                                    <th class="px-4 py-2 border-b">名前</th>
                                    <th class="px-4 py-2 border-b">スラッグ</th>
                                    <th class="px-4 py-2 border-b"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tags as $tag)
                                    <tr>
                                        <td class="px-4 py-2 border-b">{{ $tag->id }}</td>
                                        <td class="px-4 py-2 border-b">{{ $tag->name }}</td>
                                        <td class="px-4 py-2 border-b">{{ $tag->slug }}</td>
                                        <td class="px-4 py-2 border-b">
                                            <form action="{{ route('tags.destroy', $tag) }}" method="POST"
                                                onsubmit="return confirm('このタグを削除しますか？');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    class="px-3 py-1 border rounded text-sm text-red-600 hover:bg-red-50">
                                                    削除
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
