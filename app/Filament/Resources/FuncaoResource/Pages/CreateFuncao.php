<?php
namespace App\Filament\Resources\FuncaoResource\Pages;
use App\Filament\Resources\FuncaoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFuncao extends CreateRecord
{
    protected static string $resource = FuncaoResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')->label('← Voltar')->color('gray')->outlined()
                ->url(fn () => route('filament.admin.resources.funcaos.index')),
        ];
    }
}
