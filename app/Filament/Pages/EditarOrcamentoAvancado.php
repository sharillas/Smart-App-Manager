<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\Orcamento;
use App\Models\Equipamento;
use App\Models\Categoria;

class EditarOrcamentoAvancado extends Page
{
    protected static ?string $navigationIcon = null;
    protected static ?string $title = 'Orçamento';
    protected static ?string $slug = 'orcamento-avancado';
    protected static string $view = 'filament.pages.orcamento-avancado';
    protected static bool $shouldRegisterNavigation = false;
    
    public $orcamento = null;
    public $numero = '';
    public $cliente_nome = '';
    public $cliente_email = '';
    public $cliente_telefone = '';
    public $evento_nome = '';
    public $evento_local = '';
    public $data_inicio = '';
    public $data_fim = '';
    public $estado = 'orcamentacao';
    public $notas = '';
    
    public $departamento_id = null;
    public $search = '';
    public $itens = [];
    public $total = 0;
    public $isEdit = false;
    
    public function mount($record = null)
    {
        // Carregar orçamento
        if ($record === null && request()->has('record')) {
            $record = request()->get('record');
        }
        
        if ($record instanceof Orcamento) {
            $orc = $record;
        } elseif (is_numeric($record) || (is_string($record) && !empty($record))) {
            $orc = Orcamento::with('itens.equipamento')->find($record);
        } else {
            $orc = null;
        }
        
        if ($orc) {
            $this->orcamento = $orc;
            $this->isEdit = true;
            $this->numero = $orc->numero;
            $this->cliente_nome = $orc->cliente_nome;
            $this->cliente_email = $orc->cliente_email;
            $this->cliente_telefone = $orc->cliente_telefone;
            $this->evento_nome = $orc->evento_nome;
            $this->evento_local = $orc->evento_local;
            $this->data_inicio = $orc->data_inicio;
            $this->data_fim = $orc->data_fim;
            $this->estado = $orc->estado;
            $this->notas = $orc->notas;
            
            $this->itens = [];
            foreach ($orc->itens as $item) {
                $this->itens[] = [
                    'id' => $item->id,
                    'equipamento_id' => $item->equipamento_id,
                    'equipamento_nome' => $item->equipamento->nome ?? 'N/A',
                    'quantidade' => $item->quantidade,
                    'preco_unitario' => $item->preco_unitario,
                    'dias' => $item->dias,
                    'subtotal' => $item->subtotal,
                    'subaluguer' => $item->subaluguer,
                ];
            }
        } else {
            $this->numero = 'ORC-' . date('Ymd') . '-' . rand(100, 999);
            $this->data_inicio = date('Y-m-d');
            $this->data_fim = date('Y-m-d', strtotime('+1 day'));
        }
        $this->calcularTotal();
    }
    
    public function getDepartamentos()
    {
        return Categoria::whereNull('parent_id')->get();
    }
    
    public function getEquipamentos()
    {
        return Equipamento::where('quantidade', '>', 0)
            ->when($this->departamento_id, function($q) {
                $q->whereHas('categoria', function($q2) {
                    $q2->whereHas('parent', fn($q3) => $q3->where('parent_id', $this->departamento_id))
                      ->orWhere('parent_id', $this->departamento_id);
                });
            })
            ->when($this->search, function($q) {
                $q->where('nome', 'ilike', '%' . $this->search . '%');
            })
            ->orderBy('nome')
            ->take(200)
            ->get();
    }
    
    public function adicionarEquipamento($equipamentoId)
    {
        $eq = Equipamento::find($equipamentoId);
        if (!$eq) return;
        
        foreach ($this->itens as $key => $item) {
            if ($item['equipamento_id'] == $equipamentoId) {
                $this->itens[$key]['quantidade']++;
                $this->itens[$key]['subtotal'] = $this->itens[$key]['quantidade'] * $this->itens[$key]['preco_unitario'];
                $this->calcularTotal();
                return;
            }
        }
        
        $this->itens[] = [
            'id' => null,
            'equipamento_id' => $equipamentoId,
            'equipamento_nome' => $eq->nome,
            'quantidade' => 1,
            'preco_unitario' => $eq->preco_aluguer_dia ?? 0,
            'dias' => 1,
            'subtotal' => ($eq->preco_aluguer_dia ?? 0) * 1,
            'subaluguer' => false,
        ];
        
        $this->calcularTotal();
    }
    
    public function aumentar($index) {
        $this->itens[$index]['quantidade']++;
        $this->itens[$index]['subtotal'] = $this->itens[$index]['quantidade'] * $this->itens[$index]['preco_unitario'];
        $this->calcularTotal();
    }
    
    public function diminuir($index) {
        if ($this->itens[$index]['quantidade'] > 1) {
            $this->itens[$index]['quantidade']--;
            $this->itens[$index]['subtotal'] = $this->itens[$index]['quantidade'] * $this->itens[$index]['preco_unitario'];
        } else {
            unset($this->itens[$index]); $this->itens = array_values($this->itens);
        }
        $this->calcularTotal();
    }
    
    public function remover($index) {
        unset($this->itens[$index]); $this->itens = array_values($this->itens);
        $this->calcularTotal();
    }
    
    public function atualizarPreco($index, $valor) {
        $this->itens[$index]['preco_unitario'] = floatval($valor);
        $this->itens[$index]['subtotal'] = $this->itens[$index]['quantidade'] * floatval($valor);
        $this->calcularTotal();
    }
    
    public function calcularTotal() {
        $this->total = array_sum(array_column($this->itens, 'subtotal'));
    }
    
    public function salvar()
    {
        $this->validate([
            'numero' => 'required', 'cliente_nome' => 'required',
            'data_inicio' => 'required', 'data_fim' => 'required',
        ]);
        
        $orc = $this->orcamento ?? new Orcamento();
        $orc->numero = $this->numero;
        $orc->cliente_nome = $this->cliente_nome;
        $orc->cliente_email = $this->cliente_email;
        $orc->cliente_telefone = $this->cliente_telefone;
        $orc->evento_nome = $this->evento_nome;
        $orc->evento_local = $this->evento_local;
        $orc->data_inicio = $this->data_inicio;
        $orc->data_fim = $this->data_fim;
        $orc->estado = $this->estado;
        $orc->notas = $this->notas;
        $orc->valor_total = $this->total;
        $orc->save();
        
        $orc->itens()->delete();
        foreach ($this->itens as $item) {
            $orc->itens()->create([
                'equipamento_id' => $item['equipamento_id'],
                'quantidade' => $item['quantidade'],
                'preco_unitario' => $item['preco_unitario'],
                'dias' => $item['dias'] ?? 1,
                'subtotal' => $item['subtotal'],
                'subaluguer' => $item['subaluguer'] ?? false,
            ]);
        }
        
        Notification::make()
            ->title('✅ Orçamento salvo!')
            ->body($orc->numero . ' · ' . count($this->itens) . ' itens · ' . number_format($this->total, 2) . '€')
            ->success()
            ->send();
        
        return redirect()->route('filament.admin.resources.orcamentos.index');
    }
}
