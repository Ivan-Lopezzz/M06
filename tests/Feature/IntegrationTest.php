<?php

use App\Data\Shifts;
use App\Data\Staff;
use Illuminate\Support\Facades\Session;

it('assigns and retrieves shifts correctly', function () {
    // Start with a fresh session
    Session::flush();

    // Create a test request to assign an employee to a shift
    $response = $this->post(route('roster.assign'), [
        'day' => 'lunes',
        'shift' => 'manana',
        'employee_id' => 1,
    ]);

    // Check that the redirect was successful
    $response->assertRedirect();

    // Now check what's in the session
    $roster = Session::get('roster');

    // Verify the roster has been set correctly
    expect($roster)->toBeArray();
    expect($roster['lunes']['manana'])->toBe(1);

    // Now visit the index page to see if it displays properly
    $response = $this->get(route('roster.index'));
    $response->assertStatus(200);

    // Check that the employee is displayed in the correct cell
    $response->assertSee('Marta'); // Assuming Marta is employee ID 1
});