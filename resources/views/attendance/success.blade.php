<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asistencia registrada</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-sm p-6 text-center">
            <h1 class="text-xl font-semibold text-green-700">✅ Registro exitoso</h1>
            <p class="mt-3 text-gray-700">{{ $message }}</p>
            <p class="mt-4 text-sm text-gray-500">Esta ventana se cerrará automáticamente.</p>
            <button id="closeBtn" class="mt-5 rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Cerrar ahora</button>
        </div>
    </div>

    <script>
        function closeWindow() {
            window.open('', '_self');
            window.close();
        }

        document.getElementById('closeBtn').addEventListener('click', closeWindow);
        setTimeout(closeWindow, 2000);
    </script>
</body>
</html>
