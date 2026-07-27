<?php
namespace App\Filament\Resources\ColaboradorResource\Pages;
use App\Filament\Resources\ColaboradorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateColaborador extends CreateRecord
{
    protected static string $resource = ColaboradorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => route('filament.admin.resources.colaboradors.index')),
        ];
    }
}
