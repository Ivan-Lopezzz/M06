<?php

use App\Stores\SessionRosterStore;
use Illuminate\Support\Facades\Session;

it('debugs controller output directly', function () {
    // Clear session at start
    Session::flush();

    // Manually test what controller does
    $store = new SessionRosterStore();

    // Check what title() returns
    $title = $store->title();
    expect($title)->toBe('');  // Should be empty string

    // Check what all() returns
    $roster = $store->all();
    expect($roster)->toBeArray();

    // Now let's assign something manually to see how it works
    $store->assign('lunes', 'manana', 1);

    // Check that data was stored
    $roster2 = $store->all();
    expect($roster2['lunes']['manana'])->toBe(1);

    // Test what title returns after assignment
    $title2 = $store->title();
    expect($title2)->toBe('');

    // The controller would do:
    // $displayRoster = [];
    // foreach ($roster as $day => $shifts) {
    //     foreach ($shifts as $shift => $employeeId) {
    //         if ($employeeId !== null) {
    //             $employee = collect(\App\Data\Staff::all())->firstWhere('id', $employeeId);
    //             $displayRoster[$day][$shift] = $employee;
    //         } else {
    //             $displayRoster[$day][$shift] = null;
    //         }
    //     }
    // }

    // Test that employee data can be found
    $employees = \App\Data\Staff::all();
    $employee = collect($employees)->firstWhere('id', 1);
    expect($employee)->not->toBeNull();
    expect($employee->name)->toBe('Marta Ferrer');
});