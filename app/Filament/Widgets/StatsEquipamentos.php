<?php

namespace App\Filament\Widgets;

use App\Models\Equipamento;
use App\Models\Orcamento;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsEquipamentos extends BaseWidget
{
    protected function getStats(): array
    {
        $stockDisponivel = Equipamento::where('estado', 'disponivel')->sum('quantidade');
        $stockTotal = Equipamento::sum('quantidade');
        $emManutencao = Equipamento::where('estado', 'manutencao')->sum('quantidade');
        
        $confirmados = Orcamento::where('estado', 'confirmado')->count();
        $orcamentacao = Orcamento::where('estado', 'orcamentacao')->count();
        $drafts = Orcamento::where('estado', 'draft')->count();
        $cancelados = Orcamento::where('estado', 'cancelado')->count();
        $total = $confirmados + $orcamentacao + $drafts + $cancelados;
        
        return [
            Stat::make('Stock Disponível', $stockDisponivel)
                ->description('Total: ' . $stockTotal . ' unidades')
                ->descriptionIcon('heroicon-o-cube')
                ->color('success'),
                
            Stat::make('Em Manutenção', $emManutencao)
                ->description(Equipamento::where('estado', 'manutencao')->count() . ' equipamentos')
                ->descriptionIcon('heroicon-o-wrench')
                ->color($emManutencao > 0 ? 'danger' : 'success'),
                
            Stat::make('Orçamentos', $total)
                ->description(
                    ($confirmados > 0 ? '🟢' . $confirmados . ' ' : '') .
                    ($orcamentacao > 0 ? '🟡' . $orcamentacao . ' ' : '') .
                    ($drafts > 0 ? '🔵' . $drafts . ' ' : '') .
                    ($cancelados > 0 ? '🔴' . $cancelados : '')
                )
                ->descriptionIcon('heroicon-o-document-text')
                ->color('warning'),
        ];
    }
}
