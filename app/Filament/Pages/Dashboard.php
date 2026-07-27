<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CalendarioEventos;
use App\Filament\Widgets\CalendarioMensal;
use App\Filament\Widgets\StatsEquipamentos;
use App\Filament\Widgets\AlertasConflitos;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = null;
    protected static ?string $title = 'Dashboard';
    protected static ?string $navigationLabel = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            StatsEquipamentos::class,
            AlertasConflitos::class,
            CalendarioEventos::class,
            CalendarioMensal::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 1;
    }
}
