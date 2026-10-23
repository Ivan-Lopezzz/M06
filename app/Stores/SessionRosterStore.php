<?php

declare(strict_types=1);

namespace App\Stores;

use App\Contracts\RosterStore;
use App\Data\Shifts;
use Illuminate\Support\Facades\Session;

/**
 * Implementación que guarda el cuadrante en la sesión de Laravel.
 *
 * El cuadrante se almacena en la clave 'roster' de la sesión.
 * Cuando la sesión está vacía, devuelve un cuadrante vacío (todos los turnos null).
 */
final class SessionRosterStore implements RosterStore
{
    private const SESSION_KEY = 'roster';
    private const TITLE_KEY = 'roster_title';

    public function all(): array
    {
        $roster = Session::get(self::SESSION_KEY);

        if ($roster === null) {
            return $this->emptyWeek();
        }

        return $roster;
    }

    public function assign(string $day, string $shift, int $employeeId): void
    {
        $roster = $this->all();
        $roster[$day][$shift] = $employeeId;
        Session::put(self::SESSION_KEY, $roster);
    }

    public function remove(string $day, string $shift): void
    {
        $roster = $this->all();
        $roster[$day][$shift] = null;
        Session::put(self::SESSION_KEY, $roster);
    }

    public function forEmployee(int $employeeId): array
    {
        $shifts = [];
        $roster = $this->all();

        foreach ($roster as $day => $dayShifts) {
            foreach ($dayShifts as $shift => $id) {
                if ($id === $employeeId) {
                    $shifts[] = ['day' => $day, 'shift' => $shift];
                }
            }
        }

        return $shifts;
    }

    public function title(): string
    {
        return Session::get(self::TITLE_KEY, '');
    }

    public function setTitle(string $title): void
    {
        Session::put(self::TITLE_KEY, $title);
    }

    /** @return array<string, array<string, int|null>> */
    private function emptyWeek(): array
    {
        $week = [];

        foreach (Shifts::DAYS as $day) {
            foreach (Shifts::names() as $shift) {
                $week[$day][$shift] = null;
            }
        }

        return $week;
    }
}
