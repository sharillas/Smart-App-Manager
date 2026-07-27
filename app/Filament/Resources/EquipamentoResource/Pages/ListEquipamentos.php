<?php
namespace App\Filament\Resources\EquipamentoResource\Pages;
use App\Filament\Resources\EquipamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipamentos extends ListRecords
{
    protected static string $resource = EquipamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Novo Equipamento')->outlined(),
            Actions\Action::make('importar_series')
                ->label('Importar Nºs Série')
                ->icon('heroicon-o-qr-code')
                ->color('warning')
                ->outlined()
                ->url(route('filament.admin.pages.importar-series')),
        ];
    }
}
