<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function create(ClassSession $classSession, Request $request)
    {
        $window = (int) $request->query('window');
        $signature = (string) $request->query('sig');

        if (! $this->isValidScanWindow($classSession, $window, $signature)) {
            return view('attendance.scan')->withErrors([
                'qr' => 'El QR ya expiró o no es válido. Vuelve a escanear el código actual.',
            ]);
        }

        if (! $classSession->isActive()) {
            return view('attendance.scan')->withErrors([
                'session' => 'La sesión no está activa en este momento.',
            ]);
        }

        return view('attendance.scan', [
            'classSession' => $classSession->load('subject'),
            'window' => $window,
            'sig' => $signature,
        ]);
    }

    public function store(ClassSession $classSession, Request $request)
    {
        $data = $request->validate([
            'student_code' => ['required', 'string', 'max:30'],
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'professor_pin' => ['required', 'digits:6'],
            'window' => ['required', 'integer'],
            'sig' => ['required', 'string'],
        ]);

        $window = (int) $data['window'];
        $signature = (string) $data['sig'];

        if (! $classSession->isActive()) {
            return back()->withErrors(['session' => 'La sesión no está activa.'])->withInput();
        }

        if (! $this->isValidScanWindow($classSession, $window, $signature)) {
            return back()->withErrors(['qr' => 'El QR ya expiró, vuelve a escanear.'])->withInput();
        }

        if ($data['professor_pin'] !== $classSession->professor_pin) {
            return back()->withErrors(['professor_pin' => 'PIN del profesor incorrecto.'])->withInput();
        }

        $student = Student::updateOrCreate(
            ['student_code' => $data['student_code']],
            [
                'full_name' => $data['full_name'],
                'email' => $data['email'],
            ]
        );

        $attendance = Attendance::firstOrCreate(
            [
                'class_session_id' => $classSession->id,
                'student_id' => $student->id,
            ],
            [
                'window_index' => $window,
                'scan_ip' => $request->ip(),
                'scanned_at' => now(),
            ]
        );

        $alreadyMarked = ! $attendance->wasRecentlyCreated;

        return back()->with('status', $alreadyMarked
            ? 'Tu asistencia ya estaba registrada en esta sesión.'
            : 'Asistencia registrada correctamente.');
    }

    private function isValidScanWindow(ClassSession $classSession, int $window, string $signature): bool
    {
        if ($window <= 0 || $signature === '') {
            return false;
        }

        if (! $classSession->verifyWindowSignature($window, $signature)) {
            return false;
        }

        $currentWindow = $classSession->windowForTime();

        return $window >= ($currentWindow - 1) && $window <= $currentWindow;
    }
}
