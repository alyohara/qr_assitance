<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::query()
            ->orderBy('name')
            ->get();

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'subject_id' => ['nullable', 'integer'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = isset($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : now()->endOfDay();

        $subjectId = $validated['subject_id'] ?? null;

        $dailySummary = Attendance::query()
            ->selectRaw('DATE(attendances.scanned_at) as day')
            ->selectRaw('COUNT(*) as attendances_count')
            ->selectRaw('COUNT(DISTINCT class_sessions.id) as sessions_count')
            ->join('class_sessions', 'class_sessions.id', '=', 'attendances.class_session_id')
            ->whereBetween('attendances.scanned_at', [$from, $to])
            ->when($subjectId, fn ($query) => $query->where('class_sessions.subject_id', $subjectId))
            ->groupBy(DB::raw('DATE(attendances.scanned_at)'))
            ->orderBy('day', 'desc')
            ->get();

        $bySubject = Attendance::query()
            ->selectRaw('subjects.name as subject_name')
            ->selectRaw('COUNT(*) as attendances_count')
            ->join('class_sessions', 'class_sessions.id', '=', 'attendances.class_session_id')
            ->join('subjects', 'subjects.id', '=', 'class_sessions.subject_id')
            ->whereBetween('attendances.scanned_at', [$from, $to])
            ->when($subjectId, fn ($query) => $query->where('class_sessions.subject_id', $subjectId))
            ->groupBy('subjects.name')
            ->orderBy('subjects.name')
            ->get();

        return view('reports.index', [
            'subjects' => $subjects,
            'dailySummary' => $dailySummary,
            'bySubject' => $bySubject,
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'subject_id' => $subjectId,
            ],
        ]);
    }
}
