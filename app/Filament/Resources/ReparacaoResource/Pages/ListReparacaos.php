<?php
namespace App\Filament\Resources\ReparacaoResource\Pages;
use App\Filament\Resources\ReparacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReparacaos extends ListRecords
{
    protected static string $resource = ReparacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nova Reparação')
                ->outlined(),
        ];
    }
}
