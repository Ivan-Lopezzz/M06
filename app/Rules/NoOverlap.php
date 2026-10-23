<?php

declare(strict_types=1);

namespace App\Rules;

use App\Contracts\RosterStore;
use App\Data\Shifts;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** R01 · Un empleado no puede tener dos turnos el mismo día. */
final class NoOverlap implements ValidationRule
{
    /**
     * Ya viene hecho: AssignShiftRequest te pasa el cuadrante actual y el día y
     * el turno que se quieren asignar. En validate() recibes el id del empleado.
     */
    public function __construct(
        private readonly RosterStore $store,
        private readonly string $day,
        private readonly string $shift,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Convert the employee ID to integer (in case it's still a string)
        $employeeId = (int) $value;

        // Get all existing shifts for this employee
        $employeeShifts = $this->store->forEmployee($employeeId);

        // Check if the employee already has a shift on the same day
        foreach ($employeeShifts as $empShift) {
            if ($empShift['day'] === $this->day) {
                $fail('El empleado ya tiene un turno asignado para este día.');
                return;
            }
        }
    }
}
