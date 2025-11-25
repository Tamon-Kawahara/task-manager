<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Support\Carbon;
use App\Models\User; 

class DashboardController extends Controller
{
    public function index()
    {
         /** @var User $user */
        $user = auth()->user();
        $today = Carbon::today();

        // 今日のタスク
        $todayTasks = $user->tasks()
            ->whereDate('due_date', $today)
            ->whereIn('status', ['not_started', 'in_progress'])
            ->orderBy('priority')
            ->get();

        // 今週のタスク（今日を除く）
        $weekTasks = $user->tasks()
            ->whereBetween('due_date', [
                $today->copy()->addDay(),
                $today->copy()->addDays(7)
            ])
            ->whereIn('status', ['not_started', 'in_progress'])
            ->orderBy('due_date')
            ->get();

        // ステータス統計
        $statusCounts = [
            'not_started' => $user->tasks()->where('status', 'not_started')->count(),
            'in_progress' => $user->tasks()->where('status', 'in_progress')->count(),
            'completed'   => $user->tasks()->where('status', 'completed')->count(),
        ];

        return view('dashboard', compact('todayTasks', 'weekTasks', 'statusCounts'));
    }
}