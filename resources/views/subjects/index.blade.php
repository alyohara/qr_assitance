<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Materias</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded bg-green-50 border border-green-200 p-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Nueva materia</h3>
                <form method="POST" action="{{ route('subjects.store') }}" class="grid md:grid-cols-3 gap-4">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm" />
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Código</label>
                        <input name="code" value="{{ old('code') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" />
                        @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-3">
                        <button class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Guardar materia</button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Listado</h3>
                @if ($subjects->isEmpty())
                    <p class="text-sm text-gray-600">No tienes materias cargadas.</p>
                @else
                    <div class="space-y-3">
                        @foreach($subjects as $subject)
                            <div class="border rounded p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $subject->name }}</p>
                                    <p class="text-sm text-gray-600">Código: {{ $subject->code ?: '—' }} · Sesiones: {{ $subject->sessions_count }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('subjects.export-csv', $subject) }}" class="rounded bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">CSV</a>
                                    <form method="POST" action="{{ route('subjects.update', $subject) }}" class="flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input name="name" value="{{ $subject->name }}" class="rounded-md border-gray-300 text-sm" />
                                        <input name="code" value="{{ $subject->code }}" class="rounded-md border-gray-300 text-sm" />
                                        <button class="rounded bg-gray-700 px-3 py-2 text-xs font-semibold text-white">Actualizar</button>
                                    </form>
                                    <form method="POST" action="{{ route('subjects.destroy', $subject) }}" onsubmit="return confirm('¿Eliminar materia?');">
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
