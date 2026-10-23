<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Staff;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');
        $sort = $request->query('sort');

        $employees = Staff::byRole($role);

        if ($sort === 'hours') {
            usort($employees, fn ($a, $b) => $a->contractHours <=> $b->contractHours);
        }

        return view('employees.index', compact('employees'));
    }

    public function show($id)
    {
        // Validate that id is numeric
        if (!is_numeric($id)) {
            abort(404);
        }

        $employee = Staff::find((int)$id);

        if (!$employee) {
            abort(404);
        }

        return view('employees.show', compact('employee'));
    }
}