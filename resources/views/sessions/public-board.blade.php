<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR de asistencia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen p-6">
        <div class="mx-auto max-w-4xl rounded-lg bg-white p-8 shadow-sm">
            <div class="text-center">
                <p class="text-sm text-gray-500">{{ $classSession->subject?->name ?? 'Materia' }}</p>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">Asistencia en vivo</h1>
                @if($classSession->topic)
                    <p class="text-sm text-gray-600 mt-1">{{ $classSession->topic }}</p>
                @endif
            </div>

            <div class="mt-6 flex justify-center">
                <canvas id="qrCanvas" width="320" height="320" class="border rounded"></canvas>
            </div>

            <p id="expiresLabel" class="mt-4 text-center text-base text-gray-700"></p>

            @if($showToken)
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500">Token / PIN del profesor</p>
                    <p class="mt-1 text-4xl tracking-widest font-extrabold text-indigo-700">{{ $classSession->professor_pin }}</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        const qrEndpoint = "{{ $publicQrPayloadUrl }}";
        const qrCanvas = document.getElementById('qrCanvas');
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
                width: 320,
                margin: 1,
            });
            expiresLabel.textContent = `QR expira en ${expiresIn}s`;
        }

        setInterval(() => {
            if (expiresIn > 0) {
                expiresIn -= 1;
                expiresLabel.textContent = `QR expira en ${expiresIn}s`;
            }
        }, 1000);

        drawQr();
        setInterval(drawQr, 5000);
    </script>
</body>
</html>
