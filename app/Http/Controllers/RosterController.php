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

        return view('roster.index', compact('displayRoster', 'title'));
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
}