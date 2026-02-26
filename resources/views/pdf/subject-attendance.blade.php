<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Asistencias por materia</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        .meta { margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 5px; text-align: left; vertical-align: top; }
        th { background: #efefef; text-transform: uppercase; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Asistencias por materia</h1>
    <div class="meta">
        <div><strong>Materia:</strong> {{ $subject->name }}</div>
        <div><strong>Código:</strong> {{ $subject->code ?: 'N/D' }}</div>
        <div><strong>Total registros:</strong> {{ $rows->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sesión</th>
                <th>Tema</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Alumno</th>
                <th>Código</th>
                <th>Email</th>
                <th>Asistencia</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['session_uuid'] }}</td>
                    <td>{{ $row['topic'] ?: 'N/D' }}</td>
                    <td>{{ $row['starts_at']->format('d/m/Y H:i') }}</td>
                    <td>{{ $row['ends_at']->format('d/m/Y H:i') }}</td>
                    <td>{{ $row['student_name'] }}</td>
                    <td>{{ $row['student_code'] }}</td>
                    <td>{{ $row['student_email'] ?: 'N/D' }}</td>
                    <td>{{ $row['scanned_at']->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $row['scan_ip'] ?: 'N/D' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Sin asistencias registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
