<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::query()
            ->withCount('sessions')
            ->latest()
            ->get();

        return view('subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('subjects.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:30'],
        ]);

        Subject::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('subjects.index')->with('status', 'Materia creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subject $subject)
    {
        return redirect()->route('subjects.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject)
    {
        return redirect()->route('subjects.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:30'],
        ]);

        $subject->update($data);

        return redirect()->route('subjects.index')->with('status', 'Materia actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        DB::transaction(function () use ($subject): void {
            $sessionIds = ClassSession::query()
                ->where('subject_id', $subject->id)
                ->pluck('id');

            if ($sessionIds->isNotEmpty()) {
                Attendance::query()
                    ->whereIn('class_session_id', $sessionIds)
                    ->delete();

                ClassSession::query()
                    ->whereIn('id', $sessionIds)
                    ->delete();
            }

            $subject->delete();
        });

        return redirect()->route('subjects.index')->with('status', 'Materia eliminada.');
    }

    public function exportCsv(Subject $subject)
    {
        $subject->load(['sessions.attendances.student']);

        $filename = 'asistencias-materia-'.$subject->id.'.csv';

        return response()->streamDownload(function () use ($subject): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'materia',
                'sesion_uuid',
                'tema',
                'inicio_sesion',
                'fin_sesion',
                'codigo_alumno',
                'nombre_alumno',
                'email_alumno',
                'fecha_hora_asistencia',
                'ip',
            ]);

            foreach ($subject->sessions as $session) {
                foreach ($session->attendances as $attendance) {
                    fputcsv($handle, [
                        $subject->name,
                        $session->uuid,
                        $session->topic,
                        Carbon::parse($session->starts_at)->format('Y-m-d H:i:s'),
                        Carbon::parse($session->ends_at)->format('Y-m-d H:i:s'),
                        $attendance->student->student_code,
                        $attendance->student->full_name,
                        $attendance->student->email,
                        Carbon::parse($attendance->scanned_at)->format('Y-m-d H:i:s'),
                        $attendance->scan_ip,
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Subject $subject)
    {
        $subject->load(['sessions.attendances.student']);

        $rows = collect();

        foreach ($subject->sessions as $session) {
            foreach ($session->attendances as $attendance) {
                $rows->push([
                    'session_uuid' => $session->uuid,
                    'topic' => $session->topic,
                    'starts_at' => Carbon::parse($session->starts_at),
                    'ends_at' => Carbon::parse($session->ends_at),
                    'student_code' => $attendance->student->student_code,
                    'student_name' => $attendance->student->full_name,
                    'student_email' => $attendance->student->email,
                    'scanned_at' => Carbon::parse($attendance->scanned_at),
                    'scan_ip' => $attendance->scan_ip,
                ]);
            }
        }

        $pdf = Pdf::loadView('pdf.subject-attendance', [
            'subject' => $subject,
            'rows' => $rows,
        ]);

        return $pdf->download('asistencias-materia-'.$subject->id.'.pdf');
    }
}
