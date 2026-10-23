<?php

use Illuminate\Support\Facades\Session;

it('simulates full browser flow', function () {
    // Clear session at start
    Session::flush();

    // Get the initial roster page (should be empty)
    $response = $this->get(route('roster.index'));
    $response->assertStatus(200);
    $response->assertSee('Semana sin título');

    // Assign an employee
    $response = $this->post(route('roster.assign'), [
        'day' => 'lunes',
        'shift' => 'manana',
        'employee_id' => 1,
    ]);

    $response->assertRedirect();

    // Verify the session was updated correctly
    $sessionRoster = Session::get('roster');
    expect($sessionRoster['lunes']['manana'])->toBe(1);

    // Now get the roster page again - this simulates what happens in browser
    $response = $this->get(route('roster.index'));
    $response->assertStatus(200);

    // Check that employee name is displayed
    $response->assertSee('Marta');

    // Also check for specific HTML elements
    $response->assertSee('Manana');
    $response->assertSee('Lunes');
});