<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AttendanceController extends Controller
{
    public function create(ClassSession $classSession, Request $request): View
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
            'googleNext' => $request->fullUrl(),
        ]);
    }

    public function store(ClassSession $classSession, Request $request): Response|RedirectResponse
    {
        if (! $request->user()) {
            return redirect()->route('google.redirect', ['next' => route('attendance.scan', [
                'classSession' => $classSession,
                'window' => $request->input('window'),
                'sig' => $request->input('sig'),
            ])]);
        }

        $data = $request->validate([
            'professor_pin' => ['required', 'digits:6'],
            'window' => ['required', 'integer'],
            'sig' => ['required', 'string'],
            'device_id' => ['required', 'string', 'min:16', 'max:191'],
        ]);

        $window = (int) $data['window'];
        $signature = (string) $data['sig'];
        $deviceId = (string) $data['device_id'];
        $user = $request->user();

        if (! $user->google_id) {
            return back()->withErrors([
                'auth' => 'Para registrar asistencia debes ingresar con tu cuenta de Google.',
            ]);
        }

        if (! $classSession->isActive()) {
            return back()->withErrors(['session' => 'La sesión no está activa.'])->withInput();
        }

        if (! $this->isValidScanWindow($classSession, $window, $signature)) {
            return back()->withErrors(['qr' => 'El QR ya expiró, vuelve a escanear.'])->withInput();
        }

        if ($data['professor_pin'] !== $classSession->professor_pin) {
            return back()->withErrors(['professor_pin' => 'PIN del profesor incorrecto.'])->withInput();
        }

        if (! $user->device_fingerprint) {
            $user->forceFill(['device_fingerprint' => $deviceId])->save();
        } elseif ($user->device_fingerprint !== $deviceId) {
            return back()->withErrors([
                'device_id' => 'Este usuario ya registró asistencias desde otro dispositivo. Usa el móvil original.',
            ])->withInput();
        }

        $student = Student::updateOrCreate(
            ['student_code' => 'USER-'.$user->id],
            [
                'full_name' => $user->name,
                'email' => $user->email,
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

        return response()->view('attendance.success', [
            'alreadyMarked' => $alreadyMarked,
            'message' => $alreadyMarked
                ? 'Tu asistencia ya estaba registrada en esta sesión.'
                : 'Asistencia registrada correctamente.',
        ]);
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
