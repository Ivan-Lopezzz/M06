<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\RosterStore;
use App\Data\Shifts;
use App\Data\Staff;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    public function index(RosterStore $store)
    {
        $roster = $store->all();
        $title = $store->title();

        // If title is empty string, default to "Semana sin título" for display
        if ($title === '') {
            $title = 'Semana sin título';
        }

        // Convert employee IDs to employee objects for display
        $displayRoster = [];
        foreach ($roster as $day => $shifts) {
            foreach ($shifts as $shift => $employeeId) {
                if ($employeeId !== null) {
                    // Find the employee by ID from Staff data
                    $employee = collect(\App\Data\Staff::all())->firstWhere('id', $employeeId);
                    $displayRoster[$day][$shift] = $employee;
                } else {
                    $displayRoster[$day][$shift] = null;
                }
            }
        }

        // Get candidates if they exist in session
        $candidates = session('candidate_employees', []);

        return view('roster.index', compact('displayRoster', 'title', 'candidates'));
    }

    public function assign(\App\Http\Requests\AssignShiftRequest $request, RosterStore $store)
    {
        $validated = $request->validated();

        $store->assign($validated['day'], $validated['shift'], (int) $validated['employee_id']);

        return redirect()->route('roster.index')->with('status', 'Turno asignado correctamente');
    }

    public function remove(Request $request, RosterStore $store)
    {
        $validated = $request->validate([
            'day' => 'required|in:' . implode(',', Shifts::DAYS),
            'shift' => 'required|in:' . implode(',', Shifts::names()),
        ]);

        $store->remove($validated['day'], $validated['shift']);

        return redirect()->route('roster.index')->with('status', 'Turno eliminado correctamente');
    }

    public function summary(RosterStore $store)
    {
        // Calculate summary data
        $roster = $store->all();

        // Get all employees
        $employees = Staff::all();

        // Initialize summary data
        $employeeHours = [];
        $shiftCounts = [];
        $emptyDays = [];

        // Initialize shift counts for each day and shift
        foreach (Shifts::DAYS as $day) {
            foreach (Shifts::names() as $shift) {
                $shiftCounts[$day][$shift] = 0;
            }
        }

        // Process roster to calculate data
        foreach ($roster as $day => $shifts) {
            foreach ($shifts as $shift => $employeeId) {
                if ($employeeId !== null) {
                    // Count shifts per employee
                    if (!isset($employeeHours[$employeeId])) {
                        $employeeHours[$employeeId] = 0;
                    }
                    $employeeHours[$employeeId] += Shifts::hours($shift);

                    // Count shifts by shift type
                    $shiftCounts[$day][$shift]++;
                } else {
                    // Track empty days (no assignments)
                    if (!isset($emptyDays[$day])) {
                        $emptyDays[$day] = 0;
                    }
                    $emptyDays[$day]++;
                }
            }
        }

        // Check for warnings
        $warnings = [];

        // Check for shifts without employees
        foreach ($shiftCounts as $day => $shifts) {
            foreach ($shifts as $shift => $count) {
                if ($count === 0) {
                    $warnings[] = "No hay responsable asignado al turno {$shift} del día {$day}";
                }
            }
        }

        // Check for employees exceeding contract hours
        foreach ($employees as $employee) {
            if (isset($employeeHours[$employee->id]) && $employeeHours[$employee->id] > $employee->contractHours) {
                $warnings[] = "El empleado {$employee->name} excede las horas de contrato ({$employeeHours[$employee->id]} > {$employee->contractHours})";
            }
        }

        // Check for days with no assignments
        foreach ($emptyDays as $day => $count) {
            if ($count === count(Shifts::names())) {  // All shifts are empty
                $warnings[] = "No hay asignaciones para el día {$day}";
            }
        }

        return view('roster.summary', compact('store', 'employeeHours', 'shiftCounts', 'warnings'));
    }

    public function candidates(Request $request, RosterStore $store)
    {
        $validated = $request->validate([
            'day' => 'required|in:' . implode(',', Shifts::DAYS),
            'shift' => 'required|in:' . implode(',', Shifts::names()),
        ]);

        $day = $validated['day'];
        $shift = $validated['shift'];

        // Get current roster to check existing assignments
        $roster = $store->all();

        // Check if this slot is already filled
        if ($roster[$day][$shift] !== null) {
            return redirect()->route('roster.index')->with('error', 'Este turno ya está asignado');
        }

        // Get all employees
        $employees = Staff::all();

        // Find candidates who can work this shift
        $candidates = [];
        foreach ($employees as $employee) {
            if ($this->canWorkShift($employee, $roster, $day, $shift)) {
                // Make sure we're storing objects consistently
                if (is_array($employee)) {
                    // Convert array to object if needed
                    $obj = new \stdClass();
                    foreach ($employee as $key => $value) {
                        $obj->$key = $value;
                    }
                    $candidates[] = $obj;
                } else {
                    $candidates[] = clone $employee;
                }
            }
        }

        // Store candidates in session for display along with day and shift info
        session([
            'candidate_employees' => $candidates,
            'candidate_day' => $day,
            'candidate_shift' => $shift
        ]);

        return redirect()->route('roster.index')->with('status', 'Candidatos encontrados: ' . count($candidates));
    }

    public function autoFill(Request $request, RosterStore $store)
    {
        $validated = $request->validate([
            'day' => 'required|in:' . implode(',', Shifts::DAYS),
            'shift' => 'required|in:' . implode(',', Shifts::names()),
        ]);

        $day = $validated['day'];
        $shift = $validated['shift'];

        // Get current roster to check existing assignments
        $roster = $store->all();

        // Check if this slot is already filled
        if ($roster[$day][$shift] !== null) {
            return redirect()->route('roster.index')->with('error', 'Este turno ya está asignado');
        }

        // Get all employees
        $employees = Staff::all();

        // Find first available employee who can work this shift
        $availableEmployee = null;
        foreach ($employees as $employee) {
            if ($this->canWorkShift($employee, $roster, $day, $shift)) {
                $availableEmployee = clone $employee;
                break;
            }
        }

        // If we found an available employee, assign them
        if ($availableEmployee !== null) {
            $store->assign($day, $shift, $availableEmployee->id);
            return redirect()->route('roster.index')->with('status', "Turno asignado a {$availableEmployee->name}");
        } else {
            return redirect()->route('roster.index')->with('error', 'No hay empleados disponibles para este turno');
        }
    }

    private function canWorkShift($employee, $roster, $day, $shift)
    {
        // Check if employee already has a shift on the same day
        foreach (Shifts::names() as $checkShift) {
            if ($roster[$day][$checkShift] === $employee->id) {
                return false; // Employee already has a shift on this day
            }
        }

        // Additional overlap checking: check if employee has shifts that would conflict with the new shift
        foreach (Shifts::DAYS as $checkDay) {
            foreach (Shifts::names() as $checkShift) {
                // If employee is assigned to any shift, check for time conflicts
                if ($roster[$checkDay][$checkShift] === $employee->id) {
                    // Get the start and end times for both shifts
                    $currentStart = Shifts::startsAt($checkShift);
                    $currentEnd = Shifts::endsAt($checkShift);
                    $newStart = Shifts::startsAt($shift);
                    $newEnd = Shifts::endsAt($shift);

                    // Check if the shifts overlap
                    if ($currentStart < $newEnd && $newStart < $currentEnd) {
                        return false; // Shifts overlap, employee can't work both
                    }
                }
            }
        }

        return true; // Employee is available for this shift
    }
}