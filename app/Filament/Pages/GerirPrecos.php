<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Equipamento;
use App\Models\Categoria;

class GerirPrecos extends Page
{
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Gerir Preços';
    protected static ?string $title = 'Gestão Rápida de Preços';
    protected static ?string $slug = 'gerir-precos';
    protected static string $view = 'filament.pages.gerir-precos';
    protected static bool $shouldRegisterNavigation = false;
    
    public $departamento_id = null;
    public $familia_id = null;
    public $equipamentos = [];
    public $precos = [];
    
    public function mount()
    {
        $this->carregarEquipamentos();
    }
    
    public function updatedDepartamentoId()
    {
        $this->familia_id = null;
        $this->carregarEquipamentos();
    }
    
    public function updatedFamiliaId()
    {
        $this->carregarEquipamentos();
    }
    
    public function carregarEquipamentos()
    {
        $query = Equipamento::query()->orderBy('nome');
        
        if ($this->departamento_id) {
            $query->whereHas('categoria', function($q) {
                $q->whereHas('parent', fn($q2) => $q2->where('parent_id', $this->departamento_id))
                  ->orWhere('parent_id', $this->departamento_id);
            });
        }
        
        if ($this->familia_id) {
            $query->whereHas('categoria', function($q) {
                $q->where('parent_id', $this->familia_id);
            });
        }
        
        $this->equipamentos = $query->take(50)->get();
        
        foreach ($this->equipamentos as $eq) {
            $this->precos[$eq->id] = $eq->preco_aluguer_dia;
        }
    }
    
    public function salvarPrecos()
    {
        $atualizados = 0;
        foreach ($this->precos as $id => $preco) {
            if ($preco !== null && $preco >= 0) {
                Equipamento::where('id', $id)->update(['preco_aluguer_dia' => $preco]);
                $atualizados++;
            }
        }
        
        \Filament\Notifications\Notification::make()
            ->title('✅ Preços atualizados!')
            ->body($atualizados . ' equipamentos atualizados.')
            ->success()
            ->send();
            
        $this->carregarEquipamentos();
    }
}
