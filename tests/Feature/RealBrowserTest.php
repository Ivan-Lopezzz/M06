<?php

use Illuminate\Support\Facades\Session;

it('tests real browser flow with separate requests', function () {
    // Clear session at start (this simulates a fresh browser session)
    Session::flush();

    // First request: Get the roster page (should be empty)
    $response = $this->get(route('roster.index'));
    $response->assertStatus(200);
    // This should show empty cells

    // Second request: Assign an employee
    $response = $this->post(route('roster.assign'), [
        'day' => 'lunes',
        'shift' => 'manana',
        'employee_id' => 1,
    ]);
    $response->assertRedirect();

    // Third request: Get the roster page again (should show assignment)
    $response = $this->get(route('roster.index'));
    $response->assertStatus(200);

    // This should now show "Marta Ferrer" in the cell
    // But let's check if it's actually there

    // For debugging, we can see what's in the session
    $sessionRoster = Session::get('roster');
    expect($sessionRoster['lunes']['manana'])->toBe(1);

    // The page should contain "Marta Ferrer" now
    $response->assertSee('Marta Ferrer');
});