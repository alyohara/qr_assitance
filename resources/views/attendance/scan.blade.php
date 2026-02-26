<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro de asistencia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-lg mx-auto bg-white rounded-lg shadow-sm p-6">
            <h1 class="text-xl font-semibold text-gray-900">Registro de asistencia</h1>

            @if($errors->any())
                <div class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    <ul class="list-disc ps-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('status'))
                <div class="mt-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            @if(isset($classSession))
                <div class="mt-4 rounded border border-gray-200 p-4">
                    <p class="text-sm text-gray-500">Materia</p>
                    <p class="font-medium text-gray-900">{{ $classSession->subject->name }}</p>
                    <p class="text-sm text-gray-600">Sesión hasta {{ $classSession->ends_at->format('d/m/Y H:i') }}</p>
                </div>

                @auth
                    <div class="mt-4 rounded border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-800">
                        Registrando asistencia como <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }}).
                    </div>

                    <a href="{{ route('google.redirect', ['next' => $googleNext ?? url()->full(), 'force_account' => 1]) }}" class="mt-3 inline-flex w-full items-center justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cambiar cuenta de Google
                    </a>

                    <form method="POST" action="{{ route('attendance.store', $classSession) }}" class="mt-4 space-y-4" id="attendanceForm">
                        @csrf
                        <input type="hidden" name="window" value="{{ $window }}">
                        <input type="hidden" name="sig" value="{{ $sig }}">
                        <input type="hidden" name="device_id" id="deviceIdInput" value="">

                        <div>
                            <label class="block text-sm font-medium text-gray-700">PIN del profesor</label>
                            <input name="professor_pin" maxlength="6" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm" />
                        </div>

                        <button class="w-full rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Registrar asistencia</button>
                    </form>

                    <script>
                        const key = 'attendance_device_id';
                        let deviceId = localStorage.getItem(key);

                        if (!deviceId) {
                            deviceId = (window.crypto && window.crypto.randomUUID)
                                ? window.crypto.randomUUID()
                                : `dev-${Date.now()}-${Math.random().toString(16).slice(2)}`;
                            localStorage.setItem(key, deviceId);
                        }

                        document.getElementById('deviceIdInput').value = deviceId;
                    </script>
                @else
                    <a href="{{ route('google.redirect', ['next' => $googleNext ?? url()->full()]) }}" class="mt-4 inline-flex w-full items-center justify-center rounded bg-white border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Continuar con Google para registrar asistencia
                    </a>
                @endauth
            @else
                <p class="mt-4 text-sm text-gray-600">Escanea nuevamente el QR vigente de tu clase.</p>
            @endif
        </div>
    </div>
</body>
</html>
