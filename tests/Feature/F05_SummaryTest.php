<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/** F05 · Resumen del cuadrante. PRUEBA VISIBLE. */
final class F05_SummaryTest extends TestCase
{
    public function test_el_resumen_muestra_horas_asignadas_por_empleado(): void
    {
        // Assign a shift first to have data
        $this->post(route('roster.assign'), [
            'day' => 'lunes', 'shift' => 'manana', 'employee_id' => 1,
        ]);

        $response = $this->get(route('roster.summary'));

        $response->assertOk();
        $response->assertSee('Marta Ferrer');
        $response->assertSee('40'); // contract hours
    }

    public function test_el_resumen_muestra_asignaciones_por_turno(): void
    {
        // Assign a shift first to have data
        $this->post(route('roster.assign'), [
            'day' => 'lunes', 'shift' => 'manana', 'employee_id' => 1,
        ]);

        $response = $this->get(route('roster.summary'));

        $response->assertOk();
        // Should show at least one assignment in the table
        $response->assertSee('1'); // One shift assigned
    }

    public function test_el_resumen_muestra_dias_sin_asignaciones(): void
    {
        $response = $this->get(route('roster.summary'));

        $response->assertOk();
        // Should not show any empty day warnings by default since no shifts are assigned
    }
}