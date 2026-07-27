<?php
namespace App\Filament\Resources\GuiaTransporteResource\Pages;
use App\Filament\Resources\GuiaTransporteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuiaTransporte extends EditRecord
{
    protected static string $resource = GuiaTransporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => route('filament.admin.resources.guia-transportes.index')),
            Actions\DeleteAction::make()->label('Apagar'),
        ];
    }
}
