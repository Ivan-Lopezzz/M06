<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cuadrante Turnos')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .celda {
            min-width: 120px;
            min-height: 80px;
            padding: 10px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        .nom {
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        .rol {
            margin: 0 0 8px 0;
            font-size: 0.85em;
            color: #6c757d;
        }
        .vacia {
            margin: 0;
            text-align: center;
            color: #adb5bd;
        }
        .asignar {
            margin-top: 20px;
        }
        .asignar label {
            display: block;
            margin-bottom: 10px;
        }
        .errors {
            margin-bottom: 20px;
        }
        .errors ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ route('roster.index') }}">Cuadrante Turnos</a>
            <div class="navbar-nav">
                <a class="nav-link" href="{{ route('roster.index') }}">Roster</a>
                <a class="nav-link" href="{{ route('employees.index') }}">Empleados</a>
                <a class="nav-link" href="{{ route('roster.summary') }}">Resumen</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>