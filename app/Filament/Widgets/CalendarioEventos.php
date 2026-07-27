<?php

namespace App\Filament\Widgets;

use App\Models\Orcamento;
use Filament\Widgets\Widget;
use Carbon\Carbon;

class CalendarioEventos extends Widget
{
    protected static string $view = 'filament.widgets.calendario-eventos';
    protected int|string|array $columnSpan = 'full';
    
    public function getOrcamentos()
    {
        $inicioSemana = Carbon::now('Europe/Lisbon')->startOfWeek(Carbon::MONDAY)->startOfDay();
        $fimSemana = Carbon::now('Europe/Lisbon')->endOfWeek(Carbon::SUNDAY)->endOfDay();
        
        return Orcamento::with(['itens.equipamento'])
            ->whereIn('estado', ['draft', 'confirmado', 'orcamentacao'])
            ->where(function($query) use ($inicioSemana, $fimSemana) {
                $query->whereBetween('data_inicio', [$inicioSemana, $fimSemana])
                      ->orWhereBetween('data_fim', [$inicioSemana, $fimSemana])
                      ->orWhere(function($q) use ($inicioSemana, $fimSemana) {
                          $q->where('data_inicio', '<=', $inicioSemana)
                            ->where('data_fim', '>=', $fimSemana);
                      });
            })
            ->orderBy('data_inicio')
            ->get()
            ->map(function ($orcamento) use ($inicioSemana) {
                $inicio = Carbon::parse($orcamento->data_inicio)->startOfDay();
                $fim = Carbon::parse($orcamento->data_fim)->startOfDay();
                
                $colInicio = $inicioSemana->diffInDays($inicio, false) + 1;
                $colFim = $inicioSemana->diffInDays($fim, false) + 1;
                $colInicio = max(1, min(7, $colInicio));
                $colFim = max(1, min(7, $colFim));
                $duracao = max(1, $colFim - $colInicio + 1);
                
                $cor = match ($orcamento->estado) {
                    'confirmado' => '#10B981',
                    'orcamentacao' => '#F59E0B',
                    'draft' => '#3B82F6',
                    default => '#6B7280',
                };
                
                $equipamentos = $orcamento->itens->map(function($item) {
                    return [
                        'nome' => $item->equipamento->nome ?? 'N/A',
                        'quantidade' => $item->quantidade,
                        'preco' => $item->preco_unitario,
                    ];
                })->toArray();
                
                return (object) [
                    'id' => $orcamento->id,
                    'numero' => $orcamento->numero,
                    'cliente' => $orcamento->cliente_nome,
                    'evento' => $orcamento->evento_nome,
                    'local' => $orcamento->evento_local,
                    'inicio' => $orcamento->data_inicio,
                    'fim' => $orcamento->data_fim,
                    'coluna_inicio' => $colInicio,
                    'duracao' => $duracao,
                    'cor' => $cor,
                    'estado' => $orcamento->estado,
                    'estado_nome' => match ($orcamento->estado) {
                        'confirmado' => 'Confirmado',
                        'orcamentacao' => 'Orçamentação',
                        'draft' => 'Draft',
                        default => $orcamento->estado,
                    },
                    'total_equipamentos' => $orcamento->itens->sum('quantidade'),
                    'equipamentos' => $equipamentos,
                    'valor_total' => $orcamento->valor_total,
                ];
            });
    }
    
    public function getDiaAtual(): int
    {
        return Carbon::now('Europe/Lisbon')->dayOfWeekIso;
    }
    
    public function getSemanaAtual(): array
    {
        $dias = [];
        $inicio = Carbon::now('Europe/Lisbon')->startOfWeek(Carbon::MONDAY)->startOfDay();
        Carbon::setLocale('pt');
        
        for ($i = 0; $i < 7; $i++) {
            $data = $inicio->copy()->addDays($i);
            $dias[] = [
                'nome' => $data->translatedFormat('D'),
                'dia' => $data->format('d'),
                'mes' => $data->format('M'),
                'data' => $data->format('Y-m-d'),
                'hoje' => $data->isToday(),
            ];
        }
        return $dias;
    }
}
