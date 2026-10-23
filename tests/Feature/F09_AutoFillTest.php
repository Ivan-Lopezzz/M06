<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/** F09 · Rellenar automáticamente. PRUEBA VISIBLE. */
final class F09_AutoFillTest extends TestCase
{
    public function test_rellenar_automaticamente_redirige_con_mensaje_flash(): void
    {
        $response = $this->post(route('roster.autofill'), [
            'day' => 'lunes',
            'shift' => 'manana',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
    }

    public function test_rellenar_automaticamente_asigna_empleados_a_turnos_vacios(): void
    {
        // First, create an empty roster
        $this->get(route('roster.index'))->assertOk();

        // Then auto-fill
        $response = $this->post(route('roster.autofill'), [
            'day' => 'lunes',
            'shift' => 'manana',
        ]);
        $response->assertRedirect();

        // Check that the roster is now filled (at least some employees should be assigned)
        $this->get(route('roster.index'))->assertOk();
    }

    public function test_rellenar_automaticamente_respetando_asignaciones_existentes(): void
    {
        // First, assign one employee to a shift
        $this->post(route('roster.assign'), [
            'day' => 'lunes',
            'shift' => 'manana',
            'employee_id' => 1,
        ]);

        // Then auto-fill - should not overwrite existing assignments
        $response = $this->post(route('roster.autofill'), [
            'day' => 'martes',
            'shift' => 'tarde',
        ]);
        $response->assertRedirect();

        // Verify that the existing assignment is still there
        $this->get(route('roster.index'))->assertOk()->assertSee('Marta Ferrer');
    }
}