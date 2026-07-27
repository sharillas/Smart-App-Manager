<?php
namespace App\Filament\Resources\FuncaoResource\Pages;
use App\Filament\Resources\FuncaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFuncoes extends ListRecords
{
    protected static string $resource = FuncaoResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nova Função')->outlined()];
    }
}
