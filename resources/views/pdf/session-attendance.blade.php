<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Asistencias por sesión</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        .meta { margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; }
        th { background: #efefef; text-transform: uppercase; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Asistencias por sesión</h1>
    <div class="meta">
        <div><strong>Materia:</strong> {{ $classSession->subject?->name ?? 'N/D' }}</div>
        <div><strong>Tema:</strong> {{ $classSession->topic ?: 'N/D' }}</div>
        <div><strong>Inicio:</strong> {{ $classSession->starts_at?->format('d/m/Y H:i') ?? 'N/D' }}</div>
        <div><strong>Fin:</strong> {{ $classSession->ends_at?->format('d/m/Y H:i') ?? 'N/D' }}</div>
        <div><strong>Total:</strong> {{ $attendances->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Código</th>
                <th>Email</th>
                <th>Fecha/Hora</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->student?->full_name ?? 'N/D' }}</td>
                    <td>{{ $attendance->student?->student_code ?? 'N/D' }}</td>
                    <td>{{ $attendance->student?->email ?? 'N/D' }}</td>
                    <td>{{ $attendance->scanned_at?->format('d/m/Y H:i:s') ?? 'N/D' }}</td>
                    <td>{{ $attendance->scan_ip ?? 'N/D' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Sin asistencias registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
