<?php
namespace App\Filament\Resources\EquipamentoResource\Pages;
use App\Filament\Resources\EquipamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEquipamento extends EditRecord
{
    protected static string $resource = EquipamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')->label('← Voltar')->color('gray')->outlined()
                ->url(fn () => route('filament.admin.resources.categorias.index')),
            Actions\Action::make('salvar')->label('💾 Salvar')->color('success')->action('save'),
            Actions\DeleteAction::make()->label('Apagar'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $equipamento = $this->getRecord();
        if ($equipamento->categoria) {
            $cat = $equipamento->categoria;
            $data['categoria_id'] = $cat->id;
            if ($cat->parent) {
                $data['familia_id'] = $cat->parent->id;
                if ($cat->parent->parent) {
                    $data['departamento_id'] = $cat->parent->parent->id;
                }
            }
        }
        return $data;
    }
}
