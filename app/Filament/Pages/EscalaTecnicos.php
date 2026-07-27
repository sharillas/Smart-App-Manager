<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Colaborador;
use App\Models\Orcamento;
use App\Models\EscalaTecnico;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class EscalaTecnicos extends Page
{
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Escala de Técnicos';
    protected static ?string $title = 'Escala de Técnicos';
    protected static ?string $slug = 'escala-tecnicos';
    protected static string $view = 'filament.pages.escala-tecnicos';
    protected static ?string $navigationGroup = 'Logística';
    
    public $semanaInicio;
    public $tecnicos = [];
    public $eventos = [];
    public $escalasData = [];
    public $tecnicoSelecionado = null;
    public $mostrarModal = false;
    
    // Paleta de cores para eventos
    private $paletaCores = [
        '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899',
        '#06B6D4', '#F97316', '#6366F1', '#14B8A6', '#E11D48', '#7C3AED',
        '#0EA5E9', '#D946EF', '#84CC16', '#F43F5E', '#22D3EE', '#A855F7',
        '#EAB308', '#4ADE80', '#2DD4BF', '#FB923C', '#38BDF8', '#A78BFA',
        '#34D399', '#FBBF24', '#818CF8', '#FB7185', '#6EE7B7', '#C084FC',
    ];
    
    public function corEvento($eventoId)
    {
        $index = abs(crc32((string)$eventoId)) % count($this->paletaCores);
        return $this->paletaCores[$index];
    }
    
    public function mount()
    {
        $this->semanaInicio = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $this->carregarDados();
    }
    
    public function semanaAnterior()
    {
        $this->semanaInicio = Carbon::parse($this->semanaInicio)->subWeek()->format('Y-m-d');
        $this->carregarDados();
    }
    
    public function semanaSeguinte()
    {
        $this->semanaInicio = Carbon::parse($this->semanaInicio)->addWeek()->format('Y-m-d');
        $this->carregarDados();
    }
    
    public function carregarDados()
    {
        $inicio = Carbon::parse($this->semanaInicio);
        $fim = $inicio->copy()->addDays(6);
        
        $this->tecnicos = Colaborador::orderBy('nome')->get()->toArray();
        
        $this->eventos = Orcamento::whereIn('estado', ['confirmado', 'orcamentacao', 'draft'])
            ->where(function($q) use ($inicio, $fim) {
                $q->whereBetween('data_inicio', [$inicio, $fim])
                  ->orWhereBetween('data_fim', [$inicio, $fim])
                  ->orWhere(function($q2) use ($inicio, $fim) {
                      $q2->where('data_inicio', '<=', $inicio)->where('data_fim', '>=', $fim);
                  });
            })
            ->orderBy('data_inicio')
            ->get()
            ->toArray();
        
        $escalas = EscalaTecnico::with('colaborador', 'orcamento')
            ->where(function($q) use ($inicio, $fim) {
                $q->whereBetween('data_inicio', [$inicio, $fim])
                  ->orWhereBetween('data_fim', [$inicio, $fim]);
            })
            ->get();
        
        $this->escalasData = [];
        foreach ($escalas as $e) {
            $colabId = $e->colaborador_id;
            if (!isset($this->escalasData[$colabId])) {
                $this->escalasData[$colabId] = [];
            }
            $this->escalasData[$colabId][] = [
                'id' => $e->id,
                'colaborador_id' => $e->colaborador_id,
                'orcamento_id' => $e->orcamento_id,
                'data_inicio' => $e->data_inicio,
                'data_fim' => $e->data_fim,
                'orcamento_numero' => $e->orcamento->numero ?? '',
                'orcamento_cliente' => $e->orcamento->cliente_nome ?? '',
            ];
        }
    }
    
    public function abrirModal($tecnicoId)
    {
        $this->tecnicoSelecionado = $tecnicoId;
        $this->mostrarModal = true;
    }
    
    public function fecharModal()
    {
        $this->mostrarModal = false;
    }
    
    public function alocarTecnico($colaboradorId, $orcamentoId)
    {
        $colaborador = Colaborador::find($colaboradorId);
        $orcamento = Orcamento::find($orcamentoId);
        
        if (!$colaborador || !$orcamento) return;
        
        $conflito = EscalaTecnico::where('colaborador_id', $colaboradorId)
            ->where(function($q) use ($orcamento) {
                $q->whereBetween('data_inicio', [$orcamento->data_inicio, $orcamento->data_fim])
                  ->orWhereBetween('data_fim', [$orcamento->data_inicio, $orcamento->data_fim]);
            })
            ->exists();
        
        if ($conflito) {
            Notification::make()->title('⚠️ Conflito!')->body($colaborador->nome . ' já está alocado neste período.')->warning()->send();
            return;
        }
        
        EscalaTecnico::create([
            'colaborador_id' => $colaboradorId,
            'orcamento_id' => $orcamentoId,
            'data_inicio' => $orcamento->data_inicio,
            'data_fim' => $orcamento->data_fim,
            'funcao' => $colaborador->funcao,
        ]);
        
        $this->mostrarModal = false;
        Notification::make()->title('✅ Alocado!')->body($colaborador->nome . ' → ' . $orcamento->numero)->success()->send();
        $this->carregarDados();
    }
    
    public function removerAlocacao($escalaId)
    {
        EscalaTecnico::find($escalaId)?->delete();
        Notification::make()->title('🗑️ Removido!')->success()->send();
        $this->carregarDados();
    }
    
    public function getDiasSemana()
    {
        $dias = [];
        $inicio = Carbon::parse($this->semanaInicio);
        for ($i = 0; $i < 7; $i++) {
            $dias[] = $inicio->copy()->addDays($i);
        }
        return $dias;
    }
    
    public function temEscala($tecnicoId, $data)
    {
        if (!isset($this->escalasData[$tecnicoId])) return null;
        foreach ($this->escalasData[$tecnicoId] as $e) {
            $inicio = Carbon::parse($e['data_inicio']);
            $fim = Carbon::parse($e['data_fim']);
            if (Carbon::parse($data)->between($inicio, $fim)) {
                return $e;
            }
        }
        return null;
    }
    
    public function isPrimeiroDia($escala, $data)
    {
        return $escala && Carbon::parse($escala['data_inicio'])->isSameDay(Carbon::parse($data));
    }
}
