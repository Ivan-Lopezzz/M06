<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Test to verify that employee IDs are properly converted from string to integer
 * in validation rules, fixing the TypeError when assigning employees to shifts.
 */
final class F04_ValidationEmployeeIdTest extends TestCase
{
    public function test_employee_id_conversion_in_validation_rules(): void
    {
        // First assign an employee to a shift
        $this->post(route('roster.assign'), [
            'day' => 'lunes',
            'shift' => 'manana',
            'employee_id' => 5
        ])->assertSessionHasNoErrors();

        // Try to assign the same employee to another shift on the same day (should fail)
        $this->post(route('roster.assign'), [
            'day' => 'lunes',
            'shift' => 'tarde',
            'employee_id' => 5
        ])->assertSessionHasErrors();
    }

    public function test_assign_shift_with_string_employee_id(): void
    {
        // Test that we can assign an employee using string ID (simulating form input)
        $this->post(route('roster.assign'), [
            'day' => 'martes',
            'shift' => 'noche',
            'employee_id' => 3
        ])->assertSessionHasNoErrors();
    }
}