<?php
namespace App\Filament\Resources\ColaboradorResource\Pages;
use App\Filament\Resources\ColaboradorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditColaborador extends EditRecord
{
    protected static string $resource = ColaboradorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->outlined()
                ->url(fn () => route('filament.admin.resources.colaboradors.index')),
            Actions\DeleteAction::make()
                ->label('Remover Colaborador')
                ->modalHeading('Remover Colaborador')
                ->modalDescription('Os equipamentos atribuídos serão devolvidos.'),
        ];
    }
}
