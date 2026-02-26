<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sesión en vivo</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded bg-green-50 border border-green-200 p-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                <p class="text-sm text-gray-500">Materia</p>
                <p class="text-lg font-semibold text-gray-900">{{ $classSession->subject->name }} @if($classSession->topic) · {{ $classSession->topic }} @endif</p>
                <p class="mt-1 text-sm text-gray-600">Horario: {{ $classSession->starts_at->format('d/m/Y H:i') }} - {{ $classSession->ends_at->format('H:i') }}</p>
                <p class="mt-1 text-sm text-gray-600">Rotación QR: cada {{ $classSession->qr_rotation_seconds }} segundos</p>
                <p class="mt-2 text-sm text-gray-500">PIN para alumnos</p>
                <p class="text-2xl tracking-widest font-bold text-indigo-700">{{ $classSession->professor_pin }}</p>
                    </div>
                    <a href="{{ route('sessions.export-csv', $classSession) }}" class="self-start rounded bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Descargar CSV</a>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold">QR dinámico</h3>
                    <p class="mt-1 text-sm text-gray-600">Este QR se actualiza automáticamente para evitar reutilización de capturas.</p>
                    <div class="mt-4 flex justify-center">
                        <canvas id="qrCanvas" width="280" height="280" class="border rounded"></canvas>
                    </div>
                    <p id="expiresLabel" class="mt-3 text-center text-sm text-gray-600"></p>
                    <p id="qrUrl" class="mt-2 text-xs text-gray-500 break-all"></p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold">Asistencias registradas</h3>
                    <p class="mt-1 text-sm text-gray-600">Total: {{ $classSession->attendances->count() }}</p>
                    <div class="mt-4 max-h-[380px] overflow-auto">
                        @if($classSession->attendances->isEmpty())
                            <p class="text-sm text-gray-500">Aún no hay asistencias.</p>
                        @else
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left border-b">
                                        <th class="py-2">Alumno</th>
                                        <th class="py-2">Código</th>
                                        <th class="py-2">Hora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classSession->attendances as $attendance)
                                        <tr class="border-b">
                                            <td class="py-2">{{ $attendance->student->full_name }}</td>
                                            <td class="py-2">{{ $attendance->student->student_code }}</td>
                                            <td class="py-2">{{ $attendance->scanned_at->format('H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const qrEndpoint = "{{ route('sessions.qr-payload', $classSession) }}";
        const qrCanvas = document.getElementById('qrCanvas');
        const qrUrl = document.getElementById('qrUrl');
        const expiresLabel = document.getElementById('expiresLabel');
        let expiresIn = 0;

        async function drawQr() {
            const response = await fetch(qrEndpoint, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                expiresLabel.textContent = 'No se pudo actualizar el QR.';
                return;
            }

            const payload = await response.json();
            expiresIn = payload.expires_in;
            await window.QRCode.toCanvas(qrCanvas, payload.url, {
                width: 280,
                margin: 1,
            });
            qrUrl.textContent = payload.url;
            expiresLabel.textContent = `Expira en ${expiresIn}s`;
        }

        setInterval(() => {
            if (expiresIn > 0) {
                expiresIn -= 1;
                expiresLabel.textContent = `Expira en ${expiresIn}s`;
            }
        }, 1000);

        drawQr();
        setInterval(drawQr, 5000);
    </script>
</x-app-layout>
