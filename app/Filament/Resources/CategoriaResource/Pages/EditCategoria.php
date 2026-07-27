<?php
namespace App\Filament\Resources\CategoriaResource\Pages;
use App\Filament\Resources\CategoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoria extends EditRecord
{
    protected static string $resource = CategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => route('filament.admin.resources.categorias.index')),
            Actions\DeleteAction::make()->label('Apagar'),
        ];
    }
}
