<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\Equipamento;
use App\Models\Categoria;

class GestaoRapida extends Page
{
    protected static ?string $navigationIcon = null;
    protected static ?string $title = 'Gestão Rápida';
    protected static ?string $slug = 'gestao-rapida';
    protected static string $view = 'filament.pages.gestao-rapida';
    protected static bool $shouldRegisterNavigation = false;
    
    public $activeTab = 'precos';
    
    public $departamento_id = null;
    public $familia_id = null;
    public $subfamilia_id = null;
    public $equipamentos = [];
    public $precos = [];
    
    public $deptSelecionado = null;
    public $familiaCatId = null;
    public $categorias = [];
    public $nomesCategorias = [];
    
    public function mount()
    {
        $this->carregarPrecos();
        $this->carregarCategorias();
    }
    
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    public function updated($field)
    {
        if (in_array($field, ['departamento_id', 'familia_id', 'subfamilia_id'])) {
            $this->carregarPrecos();
        }
        if (in_array($field, ['deptSelecionado', 'familiaCatId'])) {
            $this->carregarCategorias();
        }
    }
    
    public function carregarPrecos()
    {
        $query = Equipamento::query()->orderBy('nome');
        if ($this->subfamilia_id) {
            $query->where('categoria_id', $this->subfamilia_id);
        } elseif ($this->familia_id) {
            $query->whereHas('categoria', fn($q) => $q->where('parent_id', $this->familia_id));
        } elseif ($this->departamento_id) {
            $query->whereHas('categoria', function($q) {
                $q->whereHas('parent', fn($q2) => $q2->where('parent_id', $this->departamento_id));
            });
        }
        $this->equipamentos = $query->take(100)->get();
        $this->precos = [];
        foreach ($this->equipamentos as $eq) {
            $this->precos[$eq->id] = $eq->preco_aluguer_dia;
        }
    }
    
    public function salvarPrecos()
    {
        $atualizados = 0;
        foreach ($this->precos as $id => $preco) {
            if ($preco !== null && $preco !== '') {
                Equipamento::where('id', $id)->update(['preco_aluguer_dia' => floatval($preco)]);
                $atualizados++;
            }
        }
        Notification::make()->title('✅ ' . $atualizados . ' preços atualizados!')->success()->send();
        $this->carregarPrecos();
    }
    
    public function carregarCategorias()
    {
        $query = Categoria::with('parent')->orderBy('nome');
        if ($this->familiaCatId) {
            $query->where('parent_id', $this->familiaCatId);
        } elseif ($this->deptSelecionado) {
            $query->where('parent_id', $this->deptSelecionado)
                  ->orWhereHas('parent', fn($q) => $q->where('parent_id', $this->deptSelecionado));
        } else {
            $query->whereNotNull('parent_id');
        }
        $this->categorias = $query->take(200)->get();
        $this->nomesCategorias = [];
        foreach ($this->categorias as $cat) {
            $this->nomesCategorias[$cat->id] = $cat->nome;
        }
    }
    
    public function salvarCategoria($id)
    {
        $nome = trim($this->nomesCategorias[$id] ?? '');
        if (!empty($nome)) {
            Categoria::where('id', $id)->update(['nome' => $nome]);
            Notification::make()->title('✅ Atualizado!')->success()->send();
        }
        $this->carregarCategorias();
    }
    
    public function removerCategoria($id)
    {
        $cat = Categoria::find($id);
        if ($cat) {
            $nome = $cat->nome;
            Equipamento::where('categoria_id', $id)->update(['categoria_id' => $cat->parent_id]);
            Categoria::where('parent_id', $id)->update(['parent_id' => $cat->parent_id]);
            $cat->delete();
            Notification::make()->title('🗑️ "' . $nome . '" removida!')->success()->send();
        }
        $this->carregarCategorias();
    }
    
    public function getDepartamentos() { return Categoria::whereNull('parent_id')->get(); }
    
    public function getFamilias()
    {
        $query = Categoria::whereNotNull('parent_id')->whereHas('parent', fn($q) => $q->whereNull('parent_id'))->orderBy('nome');
        if ($this->departamento_id) $query->where('parent_id', $this->departamento_id);
        return $query->get();
    }
    
    public function getSubfamilias()
    {
        $query = Categoria::whereNotNull('parent_id')->whereHas('parent', fn($q) => $q->whereNotNull('parent_id'))->orderBy('nome');
        if ($this->familia_id) $query->where('parent_id', $this->familia_id);
        return $query->take(200)->get();
    }
    
    public function getFamiliasCat()
    {
        $query = Categoria::whereNotNull('parent_id')->whereHas('parent', fn($q) => $q->whereNull('parent_id'))->orderBy('nome');
        if ($this->deptSelecionado) $query->where('parent_id', $this->deptSelecionado);
        return $query->get();
    }
}
