<?php

namespace App\Rules;

use App\Data\Staff;
use Illuminate\Contracts\Validation\ValidationRule;

class EmployeeExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): void  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!Staff::find($value)) {
            $fail('El empleado no existe.');
        }
    }
}