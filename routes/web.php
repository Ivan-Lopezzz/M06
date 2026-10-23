<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RosterController;

/*
 * Las rutas son tuyas. Dos condiciones:
 *
 *  · todas con nombre, y todos los enlaces de las vistas construidos con
 *    route(): hay una prueba oculta que busca rutas escritas a mano;
 *  · la rejilla (resources/views/roster/index.blade.php) y las pruebas visibles
 *    usan estos nombres, así que tus rutas tienen que llamarse así:
 *
 *      employees.index   employees.show   roster.index
 *      roster.assign     roster.remove    roster.summary
 *
 *    Las dos que cambian el cuadrante, roster.assign y roster.remove, son POST.
 *
 *    Y dos detalles que también comprueban: / redirige a roster.index, y la
 *    ficha de empleado vive en /empleados/{id} y solo acepta ids numéricos.
 *
 * La URL puede estar en castellano y el nombre en inglés: la URL es para el
 * usuario, el identificador con el que trabaja tu código.
 */

Route::get('/', function () {
    return redirect()->route('roster.index');
});

Route::get('/empleados', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/empleados/{id}', [EmployeeController::class, 'show'])->name('employees.show');

Route::get('/cuadrante', [RosterController::class, 'index'])->name('roster.index');
Route::post('/cuadrante/asignar', [RosterController::class, 'assign'])->name('roster.assign');
Route::post('/cuadrante/eliminar', [RosterController::class, 'remove'])->name('roster.remove');
Route::get('/cuadrante/resumen', [RosterController::class, 'summary'])->name('roster.summary');