<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/** F08 · Listar candidatos. PRUEBA VISIBLE. */
final class F08_CandidatesTest extends TestCase
{
    public function test_listar_candidatos_redirige_con_mensaje_flash(): void
    {
        $response = $this->post(route('roster.candidates'), [
            'day' => 'lunes',
            'shift' => 'manana',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
    }

    public function test_listar_candidatos_no_funciona_en_turno_ocupado(): void
    {
        // First, assign an employee to a shift
        $this->post(route('roster.assign'), [
            'day' => 'lunes',
            'shift' => 'manana',
            'employee_id' => 1,
        ]);

        // Then try to get candidates for that occupied shift
        $response = $this->post(route('roster.candidates'), [
            'day' => 'lunes',
            'shift' => 'manana',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}