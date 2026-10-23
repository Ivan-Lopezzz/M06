{{--
    Una celda de la rejilla del cuadrante. Viene hecho: la rejilla lo usa con
    <x-shift-cell>, y tú no tienes que escribir ningún componente.

    @props  day, shift, employee (App\Data\Employee|null)
--}}
@props(['day', 'shift', 'employee' => null])

<div {{ $attributes->merge(['class' => 'celda']) }}>
    @if ($employee)
        <p class="nom">{{ $employee->name }}</p>
        <p class="rol">{{ $employee->role }}</p>

        <form method="POST" action="{{ route('roster.remove') }}" style="margin-top: 10px;">
            @csrf
            <input type="hidden" name="day" value="{{ $day }}">
            <input type="hidden" name="shift" value="{{ $shift }}">
            <button type="submit" class="btn btn-sm btn-outline-danger">Quitar</button>
        </form>
    @else
        <p class="vacia">—</p>
        <!-- F08/F09 Controls for empty slots -->
        <div style="margin-top: 10px;">
            <form method="POST" action="{{ route('roster.candidates') }}" style="display: inline;">
                @csrf
                <input type="hidden" name="day" value="{{ $day }}">
                <input type="hidden" name="shift" value="{{ $shift }}">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Candidatos</button>
            </form>
            <form method="POST" action="{{ route('roster.autofill') }}" style="display: inline;">
                @csrf
                <input type="hidden" name="day" value="{{ $day }}">
                <input type="hidden" name="shift" value="{{ $shift }}">
                <button type="submit" class="btn btn-sm btn-outline-success">Autoasignar</button>
            </form>
        </div>

        {{-- F08 Enhanced Candidate List Display --}}
        @if (session('candidate_day') === $day && session('candidate_shift') === $shift)
            <div class="candidate-list mt-2 p-2 bg-light rounded" style="margin-top: 10px;">
                <p class="mb-1"><strong>Candidatos disponibles</strong> ·
                    {{
                        [
                            'lunes' => 'Lunes',
                            'martes' => 'Martes',
                            'miercoles' => 'Miércoles',
                            'jueves' => 'Jueves',
                            'viernes' => 'Viernes',
                            'sabado' => 'Sábado',
                            'domingo' => 'Domingo'
                        ][$day] ?? $day
                    }}
                    por la {{
                        ($shift === 'manana') ? 'mañana' :
                        (($shift === 'tarde') ? 'tarde' : 'noche')
                    }} ({{ count(session('candidate_employees', [])) }})</p>
                @if (count(session('candidate_employees', [])) > 0)
                    <ul class="list-unstyled mb-0">
                        @foreach (session('candidate_employees', []) as $candidate)
                            <li>{{
                                is_object($candidate) ? $candidate->name :
                                (is_array($candidate) && isset($candidate['name']) ? $candidate['name'] : 'Unknown')
                            }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted mb-0">No hay candidatos disponibles para este turno</p>
                @endif
            </div>
        @endif
    @endif
</div>