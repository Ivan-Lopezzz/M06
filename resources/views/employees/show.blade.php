@extends('layouts.app')

@section('title', 'Detalles del Empleado')

@section('content')
<h1>Detalles del Empleado</h1>

@if ($employee)
    <div class="employee-details">
        <p><strong>Nombre:</strong> {{ $employee->name }}</p>
        <p><strong>Rol:</strong> {{ $employee->role }}</p>
        <p><strong>Horas de Contrato:</strong> {{ $employee->contractHours }}</p>
    </div>

    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Volver a la lista</a>
@else
    <p>Empleado no encontrado.</p>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Volver a la lista</a>
@endif
@endsection