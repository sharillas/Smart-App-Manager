<?php

namespace App\Filament\Widgets;

use App\Models\Orcamento;
use Filament\Widgets\Widget;
use Carbon\Carbon;

class CalendarioMensal extends Widget
{
    protected static string $view = 'filament.widgets.calendario-mensal';
    protected int|string|array $columnSpan = 'full';
    
    public $mesAtual;
    public $anoAtual;
    
    public function mount()
    {
        $this->mesAtual = Carbon::now()->month;
        $this->anoAtual = Carbon::now()->year;
    }
    
    public function mesAnterior()
    {
        if ($this->mesAtual == 1) {
            $this->mesAtual = 12;
            $this->anoAtual--;
        } else {
            $this->mesAtual--;
        }
    }
    
    public function mesSeguinte()
    {
        if ($this->mesAtual == 12) {
            $this->mesAtual = 1;
            $this->anoAtual++;
        } else {
            $this->mesAtual++;
        }
    }
    
    public function getDiasDoMes()
    {
        $inicioMes = Carbon::create($this->anoAtual, $this->mesAtual, 1);
        $fimMes = $inicioMes->copy()->endOfMonth();
        
        $dias = [];
        $diaAtual = $inicioMes->copy()->startOfWeek(Carbon::MONDAY);
        
        for ($i = 0; $i < 42; $i++) {
            $dias[] = [
                'data' => $diaAtual->format('Y-m-d'),
                'dia' => $diaAtual->day,
                'mes_atual' => $diaAtual->month == $this->mesAtual,
                'hoje' => $diaAtual->isToday(),
                'fim_semana' => $diaAtual->isWeekend(),
                'eventos' => [],
            ];
            $diaAtual->addDay();
        }
        
        // Buscar eventos do mês com equipamentos
        $eventos = Orcamento::with('itens.equipamento')
            ->whereIn('estado', ['draft', 'orcamentacao', 'confirmado', 'cancelado'])
            ->where(function($q) use ($inicioMes, $fimMes) {
                $q->whereBetween('data_inicio', [$inicioMes, $fimMes])
                  ->orWhereBetween('data_fim', [$inicioMes, $fimMes])
                  ->orWhere(function($q2) use ($inicioMes, $fimMes) {
                      $q2->where('data_inicio', '<=', $inicioMes)
                         ->where('data_fim', '>=', $fimMes);
                  });
            })
            ->orderBy('data_inicio')
            ->get();
        
        foreach ($dias as &$dia) {
            foreach ($eventos as $evento) {
                $inicio = Carbon::parse($evento->data_inicio);
                $fim = Carbon::parse($evento->data_fim);
                
                if (Carbon::parse($dia['data'])->between($inicio, $fim)) {
                    $cor = match ($evento->estado) {
                        'confirmado' => '#10B981',
                        'orcamentacao' => '#F59E0B',
                        'draft' => '#3B82F6',
                        'cancelado' => '#EF4444',
                        default => '#6B7280',
                    };
                    
                    // Lista de equipamentos
                    $equipamentos = $evento->itens->map(function($item) {
                        return [
                            'nome' => $item->equipamento->nome ?? 'N/A',
                            'quantidade' => $item->quantidade,
                        ];
                    })->toArray();
                    
                    $dia['eventos'][] = [
                        'id' => $evento->id,
                        'numero' => $evento->numero,
                        'cliente' => $evento->cliente_nome,
                        'evento' => $evento->evento_nome,
                        'local' => $evento->evento_local,
                        'estado' => $evento->estado,
                        'cor' => $cor,
                        'inicio' => $evento->data_inicio,
                        'fim' => $evento->data_fim,
                        'primeiro_dia' => Carbon::parse($evento->data_inicio)->isSameDay(Carbon::parse($dia['data'])),
                        'equipamentos' => $equipamentos,
                        'total_equipamentos' => $evento->itens->sum('quantidade'),
                    ];
                }
            }
        }
        
        return $dias;
    }
    
    public function getNomeMes(): string
    {
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        return $meses[$this->mesAtual] . ' ' . $this->anoAtual;
    }
}
