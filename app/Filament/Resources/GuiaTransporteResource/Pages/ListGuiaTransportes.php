<?php
namespace App\Filament\Resources\GuiaTransporteResource\Pages;
use App\Filament\Resources\GuiaTransporteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuiaTransportes extends ListRecords
{
    protected static string $resource = GuiaTransporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nova Guia')
                ->outlined(),
        ];
    }
}
