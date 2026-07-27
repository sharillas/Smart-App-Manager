<?php
namespace App\Filament\Resources\EntidadeResource\Pages;
use App\Filament\Resources\EntidadeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEntidade extends CreateRecord
{
    protected static string $resource = EntidadeResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')->label('← Voltar')->color('gray')->outlined()
                ->url(fn () => route('filament.admin.resources.entidades.index')),
        ];
    }
}
