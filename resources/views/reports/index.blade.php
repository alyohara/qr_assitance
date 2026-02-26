<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reportes</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Filtros</h3>
                <form method="GET" action="{{ route('reports.index') }}" class="grid md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Desde</label>
                        <input type="date" name="from" value="{{ $filters['from'] }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hasta</label>
                        <input type="date" name="to" value="{{ $filters['to'] }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Materia</label>
                        <select name="subject_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Todas</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected((string) $filters['subject_id'] === (string) $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="w-full rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Aplicar</button>
                    </div>
                </form>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Resumen diario</h3>
                    @if($dailySummary->isEmpty())
                        <p class="text-sm text-gray-500">Sin datos para el rango seleccionado.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="py-2">Fecha</th>
                                    <th class="py-2">Sesiones</th>
                                    <th class="py-2">Asistencias</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailySummary as $row)
                                    <tr class="border-b">
                                        <td class="py-2">{{ \Illuminate\Support\Carbon::parse($row->day)->format('d/m/Y') }}</td>
                                        <td class="py-2">{{ $row->sessions_count }}</td>
                                        <td class="py-2">{{ $row->attendances_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Resumen por materia</h3>
                    @if($bySubject->isEmpty())
                        <p class="text-sm text-gray-500">Sin datos para el rango seleccionado.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="py-2">Materia</th>
                                    <th class="py-2">Asistencias</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bySubject as $row)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $row->subject_name }}</td>
                                        <td class="py-2">{{ $row->attendances_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
