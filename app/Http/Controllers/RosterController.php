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
        // This method would be used for the summary page
        return view('roster.summary', ['store' => $store]);
    }

    private function hasMinimumRest(RosterStore $store, int $employeeId, string $day, string $shift): bool
    {
        // Get all existing shifts for this employee
        $employeeShifts = $store->forEmployee($employeeId);

        // If no previous shifts, then it's valid
        if (empty($employeeShifts)) {
            return true;
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
            return true;
        }

        // Get the day indices
        $prevDayIndex = Shifts::dayIndex($mostRecentShift['day']);
        $currentDayIndex = Shifts::dayIndex($day);

        // If same day, it's already prevented by earlier check
        if ($prevDayIndex === $currentDayIndex) {
            return true;
        }

        // Check if we're going forward in time (current day is after previous day)
        if ($currentDayIndex > $prevDayIndex) {
            // Calculate the hours of rest between shifts

            // Get shift start times in minutes
            $shiftStartTimes = [
                'manana' => 6 * 60,  // 06:00 = 360 minutes
                'tarde' => 14 * 60,  // 14:00 = 840 minutes
                'noche' => 22 * 60   // 22:00 = 1320 minutes
            ];

            $prevShiftEndTime = $shiftStartTimes[$mostRecentShift['shift']] + Shifts::hours($mostRecentShift['shift']) * 60;
            $currentShiftStartTime = $shiftStartTimes[$shift] ;

            // Calculate hours between shifts
            if ($currentDayIndex === $prevDayIndex + 1) {
                // Same day difference - calculate from end of previous shift to start of current shift
                $hoursOfRest = (24 * 60 - $prevShiftEndTime) + $currentShiftStartTime;

                // Check for illegal transitions
                $illegalTransitions = [
                    ['tarde', 'manana'],  // 8h gap
                    ['noche', 'manana'],  // 0h gap
                    ['noche', 'tarde']    // 8h gap
                ];

                foreach ($illegalTransitions as $transition) {
                    if ($mostRecentShift['shift'] === $transition[0] && $shift === $transition[1]) {
                        return false;
                    }
                }

                return $hoursOfRest >= 12 * 60; // 12 hours in minutes
            }
        }

        // For non-consecutive days or other cases, assume minimum rest is maintained
        return true;
    }
}