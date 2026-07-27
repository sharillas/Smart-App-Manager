<?php

namespace App\Filament\Widgets;

use App\Models\Orcamento;
use App\Models\OrcamentoItem;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlertasConflitos extends Widget
{
    protected static string $view = 'filament.widgets.alertas-conflitos';
    protected int|string|array $columnSpan = 'full';
    
    public function getConflitos()
    {
        $conflitos = [];
        $orcamentos = Orcamento::where('estado', 'confirmado')
            ->where('data_fim', '>=', Carbon::now())
            ->with('itens.equipamento')
            ->get();
        
        $verificados = [];
        foreach ($orcamentos as $o1) {
            foreach ($orcamentos as $o2) {
                if ($o1->id >= $o2->id) continue;
                
                // Verificar sobreposição de datas
                $inicio1 = Carbon::parse($o1->data_inicio);
                $fim1 = Carbon::parse($o1->data_fim);
                $inicio2 = Carbon::parse($o2->data_inicio);
                $fim2 = Carbon::parse($o2->data_fim);
                
                if ($inicio1->lte($fim2) && $fim1->gte($inicio2)) {
                    // Verificar equipamentos em comum
                    $eqs1 = $o1->itens->pluck('equipamento_id')->toArray();
                    $eqs2 = $o2->itens->pluck('equipamento_id')->toArray();
                    $comuns = array_intersect($eqs1, $eqs2);
                    
                    if (!empty($comuns)) {
                        $conflitos[] = [
                            'orcamento1' => $o1->numero . ' - ' . $o1->cliente_nome,
                            'orcamento2' => $o2->numero . ' - ' . $o2->cliente_nome,
                            'periodo' => $inicio1->format('d/m') . '-' . $fim1->format('d/m') . ' ↔ ' . $inicio2->format('d/m') . '-' . $fim2->format('d/m'),
                            'equipamentos' => count($comuns),
                        ];
                    }
                }
            }
        }
        
        return $conflitos;
    }
}
