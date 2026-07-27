<?php
namespace App\Observers;

use App\Models\Orcamento;
use App\Models\Equipamento;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrcamentoObserver
{
    public function updating(Orcamento $orcamento)
    {
        if ($orcamento->estado === 'confirmado' && $orcamento->getOriginal('estado') !== 'confirmado') {
            $alertas = $this->verificarArmazens($orcamento);
            if (!empty($alertas)) {
                session()->flash('alertas_armazem', $alertas);
            }
        }
    }

    private function verificarArmazens(Orcamento $orcamento): array
    {
        $alertas = [];
        $armazens = [];
        
        foreach ($orcamento->itens as $item) {
            if ($item->subaluguer) continue;
            $eq = Equipamento::find($item->equipamento_id);
            if ($eq && $eq->armazem) {
                $armazens[$eq->armazem][] = $eq->nome . ' (x' . $item->quantidade . ')';
            }
        }
        
        if (count($armazens) > 1) {
            $mensagem = "⚠️ Equipamentos em armazéns diferentes:\n";
            foreach ($armazens as $armazem => $eqs) {
                $mensagem .= "📍 {$armazem}: " . implode(', ', $eqs) . "\n";
            }
            $mensagem .= "\n🚛 Necessário transferência entre armazéns!";
            $alertas[] = $mensagem;
        }
        
        return $alertas;
    }

    public function updated(Orcamento $orcamento)
    {
        if ($orcamento->estado === 'confirmado' && $orcamento->wasChanged('estado')) {
            $this->processarStock($orcamento);
        }
        if ($orcamento->wasChanged('estado') && $orcamento->getOriginal('estado') === 'confirmado' && $orcamento->estado !== 'confirmado') {
            $this->devolverStock($orcamento);
        }
    }

    private function processarStock(Orcamento $orcamento)
    {
        foreach ($orcamento->itens as $item) {
            if ($item->subaluguer) continue;
            $equipamento = Equipamento::find($item->equipamento_id);
            if (!$equipamento) continue;
            $antes = $equipamento->quantidade;
            $equipamento->quantidade = max(0, $equipamento->quantidade - $item->quantidade);
            if ($equipamento->quantidade == 0) $equipamento->estado = 'alugado';
            $equipamento->save();
            DB::table('logs_stock')->insert([
                'orcamento_id'=>$orcamento->id,'equipamento_id'=>$equipamento->id,
                'quantidade_antes'=>$antes,'quantidade_depois'=>$equipamento->quantidade,
                'acao'=>'baixa','created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    private function devolverStock(Orcamento $orcamento)
    {
        foreach ($orcamento->itens as $item) {
            if ($item->subaluguer) continue;
            $equipamento = Equipamento::find($item->equipamento_id);
            if ($equipamento) {
                $antes = $equipamento->quantidade;
                $equipamento->quantidade += $item->quantidade;
                if ($equipamento->quantidade > 0 && $equipamento->estado === 'alugado') $equipamento->estado = 'disponivel';
                $equipamento->save();
                DB::table('logs_stock')->insert([
                    'orcamento_id'=>$orcamento->id,'equipamento_id'=>$equipamento->id,
                    'quantidade_antes'=>$antes,'quantidade_depois'=>$equipamento->quantidade,
                    'acao'=>'devolucao','created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }
}
