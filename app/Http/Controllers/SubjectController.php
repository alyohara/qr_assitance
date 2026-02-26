<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::query()
            ->where('user_id', auth()->id())
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
        abort_unless($subject->user_id === auth()->id(), 403);

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
        abort_unless($subject->user_id === auth()->id(), 403);

        $subject->delete();

        return redirect()->route('subjects.index')->with('status', 'Materia eliminada.');
    }

    public function exportCsv(Subject $subject)
    {
        abort_unless($subject->user_id === auth()->id(), 403);

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
}
