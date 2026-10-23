<?php

use App\Data\Shifts;
use App\Data\Staff;

it('displays candidates correctly in the view', function () {
    // Create a roster with some assignments
    $roster = [];
    foreach (Shifts::DAYS as $day) {
        foreach (Shifts::names() as $shift) {
            $roster[$day][$shift] = null; // Start with empty roster
        }
    }

    // Set up a mock session to simulate what happens when candidates are found for a specific day/shift
    $employee1 = Staff::find(1); // Marta Ferrer
    $employee2 = Staff::find(2); // Jordi Vidal

    // Simulate storing candidates in session (this is what our controller does)
    session([
        'candidate_employees' => [$employee1, $employee2],
        'candidate_day' => 'lunes',
        'candidate_shift' => 'manana'
    ]);

    // Access the index page
    $response = $this->get(route('roster.index'));

    // Check that the page loads without error
    $response->assertStatus(200);

    // Check that candidates are displayed for the specific cell where they were searched
    // Check for key elements that we know are present in the output
    $response->assertSee('Candidatos disponibles');

    // Use more flexible assertions to handle UTF-8 characters properly
    // Instead of exact string matching, check that the content contains the expected parts
    $response->assertSeeInOrder(['Candidatos disponibles', 'Lunes', 'por la', 'mañana', '(2)']);
    $response->assertSee($employee1->name);
    $response->assertSee($employee2->name);
});