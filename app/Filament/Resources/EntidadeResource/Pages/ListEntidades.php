<?php
namespace App\Filament\Resources\EntidadeResource\Pages;
use App\Filament\Resources\EntidadeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEntidades extends ListRecords
{
    protected static string $resource = EntidadeResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nova Entidade')->outlined(),
            Actions\Action::make('importar_excel')
                ->label('Importar Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->outlined()
                ->url(route('filament.admin.pages.importar-entidades')),
        ];
    }
}
