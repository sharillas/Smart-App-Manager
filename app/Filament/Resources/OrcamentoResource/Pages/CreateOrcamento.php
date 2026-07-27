<?php
namespace App\Filament\Resources\OrcamentoResource\Pages;
use App\Filament\Resources\OrcamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrcamento extends CreateRecord
{
    protected static string $resource = OrcamentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
