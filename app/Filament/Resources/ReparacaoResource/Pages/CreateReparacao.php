<?php
namespace App\Filament\Resources\ReparacaoResource\Pages;
use App\Filament\Resources\ReparacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReparacao extends CreateRecord
{
    protected static string $resource = ReparacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => route('filament.admin.resources.reparacaos.index')),
        ];
    }
}
