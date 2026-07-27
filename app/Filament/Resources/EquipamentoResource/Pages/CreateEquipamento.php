<?php
namespace App\Filament\Resources\EquipamentoResource\Pages;
use App\Filament\Resources\EquipamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipamento extends CreateRecord
{
    protected static string $resource = EquipamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')->label('← Voltar')->color('gray')->outlined()
                ->url(fn () => route('filament.admin.resources.categorias.index')),
            Actions\Action::make('salvar')
                ->label('💾 Salvar')
                ->color('success')
                ->action('create'),
        ];
    }
}
