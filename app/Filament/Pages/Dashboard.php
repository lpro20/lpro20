<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Tableau de bord';

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 2,
            'xl' => 2,
        ];
    }
}