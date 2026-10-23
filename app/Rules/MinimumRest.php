<?php

declare(strict_types=1);

namespace App\Rules;

use App\Contracts\RosterStore;
use App\Data\Shifts;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * R02 · Descanso mínimo de 12 horas entre el final de un turno y el principio
 * del siguiente.
 *
 * CALCÚLALO a partir de App\Data\Shifts. Si escribes a mano qué combinaciones
 * fallan, funcionará hoy y dejará de funcionar en cuanto cambie una hora de la
 * tabla — y cuatro de las optativas reutilizan este cálculo.
 */
final class MinimumRest implements ValidationRule
{
    public const MINIMUM_MINUTES = 12 * 60;

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

        if (empty($employeeShifts)) {
            return; // No previous shifts, so minimum rest is satisfied
        }

        // Find the most recent shift for this employee
        $mostRecentShift = null;
        $maxDayIndex = -1;

        foreach ($employeeShifts as $empShift) {
            $dayIndex = Shifts::dayIndex($empShift['day']);
            if ($dayIndex > $maxDayIndex) {
                $maxDayIndex = $dayIndex;
                $mostRecentShift = $empShift;
            }
        }

        // If no previous shift found, it's valid
        if ($mostRecentShift === null) {
            return;
        }

        // Get the day indices
        $prevDayIndex = Shifts::dayIndex($mostRecentShift['day']);
        $currentDayIndex = Shifts::dayIndex($this->day);

        // If same day, it's already prevented by NoOverlap rule
        if ($prevDayIndex === $currentDayIndex) {
            return;
        }

        // Get shift start times in minutes
        $shiftStartTimes = [
            'manana' => 6 * 60,  // 06:00 = 360 minutes
            'tarde' => 14 * 60,  // 14:00 = 840 minutes
            'noche' => 22 * 60   // 22:00 = 1320 minutes
        ];

        // Calculate end time of previous shift
        $prevShiftEndTime = $shiftStartTimes[$mostRecentShift['shift']] + Shifts::hours($mostRecentShift['shift']) * 60;

        // Calculate start time of current shift
        $currentShiftStartTime = $shiftStartTimes[$this->shift];

        // Calculate hours between shifts
        if ($currentDayIndex === $prevDayIndex + 1) {
            // Same day difference - calculate from end of previous shift to start of current shift
            $minutesOfRest = (24 * 60 - $prevShiftEndTime) + $currentShiftStartTime;

            // Check for illegal transitions
            $illegalTransitions = [
                ['tarde', 'manana'],  // 8h gap
                ['noche', 'manana'],  // 0h gap
                ['noche', 'tarde']    // 8h gap
            ];

            foreach ($illegalTransitions as $transition) {
                if ($mostRecentShift['shift'] === $transition[0] && $this->shift === $transition[1]) {
                    $fail('La transición ilegal no cumple el descanso mínimo de 12 horas.');
                    return;
                }
            }

            // Check if minimum rest is satisfied
            if ($minutesOfRest < self::MINIMUM_MINUTES) {
                $fail('El descanso entre turnos es menor a las 12 horas requeridas.');
                return;
            }
        }
    }
}
