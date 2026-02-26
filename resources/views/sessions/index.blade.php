<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sesiones</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded bg-green-50 border border-green-200 p-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            @if ($errors->has('session'))
                <div class="rounded bg-red-50 border border-red-200 p-3 text-sm text-red-700">{{ $errors->first('session') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Crear sesión</h3>
                @if($subjects->isEmpty())
                    <p class="text-sm text-red-600">Primero debes crear al menos una materia.</p>
                @else
                    <form method="POST" action="{{ route('sessions.store') }}" class="grid md:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Materia</label>
                            <select name="subject_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Seleccionar...</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tema (opcional)</label>
                            <input name="topic" value="{{ old('topic') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Inicio</label>
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', now()->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            @error('starts_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fin</label>
                            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', now()->addMinutes(90)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            @error('ends_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rotación QR (segundos)</label>
                            <input type="number" min="10" max="120" name="qr_rotation_seconds" value="{{ old('qr_rotation_seconds', 30) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            @error('qr_rotation_seconds') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PIN del profesor (6 dígitos)</label>
                            <input name="professor_pin" value="{{ old('professor_pin') }}" maxlength="6" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="si lo dejas vacío se genera" />
                            @error('professor_pin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <button class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Crear sesión</button>
                        </div>
                    </form>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Historial</h3>
                @if($sessions->isEmpty())
                    <p class="text-sm text-gray-600">No hay sesiones aún.</p>
                @else
                    <div class="space-y-3">
                        @foreach($sessions as $session)
                            <div class="border rounded p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $session->subject?->name ?? 'Materia no disponible' }} @if($session->topic) · {{ $session->topic }} @endif</p>
                                    <p class="text-sm text-gray-600">{{ $session->starts_at?->format('d/m/Y H:i') ?? 'Sin inicio' }} - {{ $session->ends_at?->format('H:i') ?? 'Sin fin' }} · QR {{ $session->qr_rotation_seconds ?? '—' }}s · Asistencias {{ $session->attendances_count }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('sessions.show', $session) }}" class="rounded bg-indigo-600 px-3 py-2 text-xs font-semibold text-white">Abrir sesión</a>
                                    <a href="{{ route('sessions.export-csv', $session) }}" class="rounded bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">CSV</a>
                                    <form method="POST" action="{{ route('sessions.destroy', $session) }}" onsubmit="return confirm('¿Eliminar sesión?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded bg-red-600 px-3 py-2 text-xs font-semibold text-white">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
