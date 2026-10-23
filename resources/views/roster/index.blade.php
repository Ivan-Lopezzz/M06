@extends('layouts.app')

@section('title', 'Cuadrante')

@section('content')
    {{-- El título es texto libre y se vuelve a mostrar: {{ }}, NUNCA {!! !!}. --}}
    <h1>{{ $title ?? 'Semana sin título' }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Turno</th>
                    @foreach (\App\Data\Shifts::DAYS as $day)
                        <th>{{ ucfirst($day) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach (\App\Data\Shifts::names() as $shift)
                    <tr>
                        <td class="fw-bold">{{ ucfirst($shift) }}</td>
                        @foreach (\App\Data\Shifts::DAYS as $day)
                            <td class="align-middle">
                                <x-shift-cell :day="$day" :shift="$shift" :employee="$displayRoster[$day][$shift] ?? null" />
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- F03/F04 · Asignar. La validación vive en el Form Request. --}}
    <div class="card mt-4">
        <div class="card-header">
            <h5>Asignar Turno</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('roster.assign') }}" class="row g-3">
                @csrf

                <div class="col-md-4">
                    <label for="day" class="form-label">Día</label>
                    <select name="day" id="day" class="form-select">
                        @foreach (\App\Data\Shifts::DAYS as $day)
                            <option value="{{ $day }}" @selected(old('day') === $day)>{{ ucfirst($day) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="shift" class="form-label">Turno</label>
                    <select name="shift" id="shift" class="form-select">
                        @foreach (\App\Data\Shifts::names() as $shift)
                            <option value="{{ $shift }}" @selected(old('shift') === $shift)>{{ ucfirst($shift) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="employee_id" class="form-label">Empleado</label>
                    <select name="employee_id" id="employee_id" class="form-select">
                        @foreach (\App\Data\Staff::all() as $employee)
                            <option value="{{ $employee->id }}" @selected((int) old('employee_id') === $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Asignar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

