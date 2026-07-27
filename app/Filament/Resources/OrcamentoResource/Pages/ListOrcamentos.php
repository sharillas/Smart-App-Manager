<?php
namespace App\Filament\Resources\OrcamentoResource\Pages;
use App\Filament\Resources\OrcamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrcamentos extends ListRecords
{
    protected static string $resource = OrcamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Novo Orçamento')->outlined()
                ->url(route('filament.admin.pages.orcamento-avancado')),
            Actions\Action::make('importar_pdf')
                ->label('Importar PDF')
                ->icon('heroicon-o-document-arrow-up')
                ->color('info')
                ->outlined()
                ->url(route('filament.admin.pages.importar-pdf')),
        ];
    }
}
