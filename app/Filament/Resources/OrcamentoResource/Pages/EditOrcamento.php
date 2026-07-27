<?php
namespace App\Filament\Resources\OrcamentoResource\Pages;
use App\Filament\Resources\OrcamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrcamento extends EditRecord
{
    protected static string $resource = OrcamentoResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['valor_total'] = $this->calcularTotal($data);
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->outlined()
                ->url(fn () => route('filament.admin.resources.orcamentos.index')),
            Actions\DeleteAction::make()->label('Apagar'),
        ];
    }

    private function calcularTotal(array $data): float
    {
        $total = 0;
        $dias = intval($data['dias_total'] ?? 1);
        $desconto = \App\Filament\Resources\OrcamentoResource::getDesconto($dias);
        
        if (isset($data['itens']) && is_array($data['itens'])) {
            foreach ($data['itens'] as $item) {
                $qtd = intval($item['quantidade'] ?? 0);
                $preco = floatval($item['preco_unitario'] ?? 0);
                $total += $qtd * $preco;
            }
        }
        return $total * $dias * $desconto;
    }
}
