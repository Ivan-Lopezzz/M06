@extends('layouts.app')

@section('title', 'Empleados')

@section('content')
<h1>Lista de Empleados</h1>

<table class="table">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Rol</th>
            <th>Horas de Contrato</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($employees as $employee)
            <tr>
                <td>{{ $employee->name }}</td>
                <td>{{ $employee->role }}</td>
                <td>{{ $employee->contractHours }}</td>
                <td>
                    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-primary">Ver</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection