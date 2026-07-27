<?php
namespace App\Filament\Resources\CategoriaResource\Pages;
use App\Filament\Resources\CategoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategorias extends ListRecords
{
    protected static string $resource = CategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nova Categoria')->outlined(),
            Actions\Action::make('novo_equipamento')
                ->label('Novo Equipamento')->icon('heroicon-o-plus-circle')->color('success')->outlined()
                ->url(route('filament.admin.resources.equipamentos.create')),
            Actions\Action::make('gestao_rapida')
                ->label('Gestão Rápida')->icon('heroicon-o-bolt')->color('warning')->outlined()
                ->url(route('filament.admin.pages.gestao-rapida')),
            Actions\Action::make('importar_equipamentos')
                ->label('Importar Equipamentos')->icon('heroicon-o-document-arrow-up')->color('success')->outlined()
                ->url(route('filament.admin.pages.importar-equipamentos')),
            Actions\Action::make('export_equipamentos')
                ->label('Exportar')->icon('heroicon-o-arrow-down-tray')->color('gray')->outlined()
                ->url(route('export.equipamentos')),
        ];
    }
}
