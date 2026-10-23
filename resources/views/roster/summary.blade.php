@extends('layouts.app')

@section('title', 'Resumen del Cuadrante')

@section('content')
    <h1>Resumen del Cuadrante</h1>

    @if (isset($warnings) && !empty($warnings))
        <div class="alert alert-warning">
            <h5>Advertencias:</h5>
            <ul>
                @foreach ($warnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5>Horas asignadas por empleado</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Rol</th>
                        <th>Horas de contrato</th>
                        <th>Horas asignadas</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (\App\Data\Staff::all() as $employee)
                        <tr>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->role }}</td>
                            <td>{{ $employee->contractHours }}</td>
                            <td>{{ $employeeHours[$employee->id] ?? 0 }}</td>
                            <td>
                                @if (isset($employeeHours[$employee->id]) && $employeeHours[$employee->id] > $employee->contractHours)
                                    <span class="badge bg-danger">Exceso</span>
                                @elseif (isset($employeeHours[$employee->id]) && $employeeHours[$employee->id] === $employee->contractHours)
                                    <span class="badge bg-success">Completo</span>
                                @else
                                    <span class="badge bg-warning">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Asignaciones por turno</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Día</th>
                        @foreach (\App\Data\Shifts::names() as $shift)
                            <th>{{ ucfirst($shift) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach (\App\Data\Shifts::DAYS as $day)
                        <tr>
                            <td>{{ ucfirst($day) }}</td>
                            @foreach (\App\Data\Shifts::names() as $shift)
                                <td>{{ $shiftCounts[$day][$shift] }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Días sin asignaciones</h5>
        </div>
        <div class="card-body">
            @php
                $emptyDays = [];
                foreach (\App\Data\Shifts::DAYS as $day) {
                    if (isset($shiftCounts[$day]) && array_sum($shiftCounts[$day]) === 0) {
                        $emptyDays[] = ucfirst($day);
                    }
                }
            @endphp

            @if (empty($emptyDays))
                <p>Todos los días tienen asignaciones.</p>
            @else
                <p>Los siguientes días no tienen asignaciones:</p>
                <ul>
                    @foreach ($emptyDays as $day)
                        <li>{{ $day }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection