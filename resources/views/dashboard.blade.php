<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel de asistencias
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <p class="text-sm text-gray-500">Materias</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $subjectsCount }}</p>
                </div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <p class="text-sm text-gray-500">Sesiones</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $sessionsCount }}</p>
                </div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <p class="text-sm text-gray-500">Asistencias</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $attendancesCount }}</p>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Sesiones activas</h3>
                        <a href="{{ route('sessions.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Gestionar sesiones</a>
                    </div>

                    @if($activeSessions->isEmpty())
                        <p class="mt-4 text-sm text-gray-600">No tienes sesiones activas ahora.</p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach($activeSessions as $session)
                                <a href="{{ route('sessions.show', $session) }}" class="block rounded border border-gray-200 p-4 hover:bg-gray-50">
                                    <p class="font-medium text-gray-900">{{ $session->subject->name }} @if($session->topic) · {{ $session->topic }} @endif</p>
                                    <p class="text-sm text-gray-600">{{ $session->starts_at->format('d/m/Y H:i') }} - {{ $session->ends_at->format('H:i') }}</p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
