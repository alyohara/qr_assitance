<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class SessionController extends Controller
{
    public function index()
    {
        $subjects = Subject::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        $sessions = ClassSession::query()
            ->where('user_id', auth()->id())
            ->with(['subject'])
            ->withCount('attendances')
            ->latest('starts_at')
            ->get();

        return view('sessions.index', compact('subjects', 'sessions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('sessions.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'topic' => ['nullable', 'string', 'max:120'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'qr_rotation_seconds' => ['required', 'integer', 'min:10', 'max:120'],
            'professor_pin' => ['nullable', 'digits:6'],
        ]);

        ClassSession::create([
            'user_id' => auth()->id(),
            'subject_id' => $data['subject_id'],
            'topic' => $data['topic'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'qr_rotation_seconds' => $data['qr_rotation_seconds'],
            'professor_pin' => $data['professor_pin'] ?? str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
        ]);

        return redirect()->route('sessions.index')->with('status', 'Sesión creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassSession $classSession)
    {
        abort_unless($classSession->user_id === auth()->id(), 403);

        $classSession->load([
            'subject',
            'attendances.student',
        ]);

        $classSession->setRelation(
            'attendances',
            $classSession->attendances->sortByDesc('scanned_at')->values()
        );

        return view('sessions.show', compact('classSession'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassSession $classSession)
    {
        return redirect()->route('sessions.show', $classSession);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassSession $classSession)
    {
        abort_unless($classSession->user_id === auth()->id(), 403);

        $data = $request->validate([
            'topic' => ['nullable', 'string', 'max:120'],
            'ends_at' => ['required', 'date', 'after:'.$classSession->starts_at],
            'qr_rotation_seconds' => ['required', 'integer', 'min:10', 'max:120'],
            'professor_pin' => ['required', 'digits:6'],
        ]);

        $classSession->update($data);

        return redirect()->route('sessions.show', $classSession)->with('status', 'Sesión actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassSession $classSession)
    {
        abort_unless($classSession->user_id === auth()->id(), 403);

        $classSession->delete();

        return redirect()->route('sessions.index')->with('status', 'Sesión eliminada.');
    }

    public function qrPayload(ClassSession $classSession)
    {
        abort_unless($classSession->user_id === auth()->id(), 403);

        $window = $classSession->windowForTime();
        $signature = $classSession->signWindow($window);
        $url = route('attendance.scan', [
            'classSession' => $classSession,
            'window' => $window,
            'sig' => $signature,
        ]);

        $seconds = max(1, $classSession->qr_rotation_seconds);
        $expiresIn = $seconds - (now()->timestamp % $seconds);

        return response()->json([
            'url' => $url,
            'window' => $window,
            'expires_in' => $expiresIn,
        ]);
    }

    public function exportCsv(ClassSession $classSession)
    {
        abort_unless($classSession->user_id === auth()->id(), 403);

        $classSession->load(['subject', 'attendances.student']);

        $filename = 'asistencias-sesion-'.$classSession->uuid.'.csv';

        return response()->streamDownload(function () use ($classSession): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'materia',
                'tema',
                'codigo_alumno',
                'nombre_alumno',
                'email_alumno',
                'fecha_hora_asistencia',
                'ventana_qr',
                'ip',
            ]);

            foreach ($classSession->attendances->sortBy('scanned_at') as $attendance) {
                fputcsv($handle, [
                    $classSession->subject->name,
                    $classSession->topic,
                    $attendance->student->student_code,
                    $attendance->student->full_name,
                    $attendance->student->email,
                    Carbon::parse($attendance->scanned_at)->format('Y-m-d H:i:s'),
                    $attendance->window_index,
                    $attendance->scan_ip,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
