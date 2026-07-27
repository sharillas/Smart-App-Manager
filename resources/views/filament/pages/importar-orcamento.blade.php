<x-filament-panels::page>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        {{-- Upload --}}
        <div style="background: #1E293B; border-radius: 20px; padding: 32px; border: 1px solid #334155;">
            <h2 style="color: #F1F5F9; font-size: 18px; font-weight: 700; margin: 0 0 8px 0;">📄 Importar WorkPlanit</h2>
            <p style="color: #94A3B8; font-size: 12px; margin-bottom: 20px;">PDF exportado do WorkPlanit</p>
            
            <div style="background: #0F172A; border: 2px dashed #334155; border-radius: 12px; padding: 24px; text-align: center; margin-bottom: 16px;">
                <input type="file" wire:model="pdfFile" accept=".pdf" style="color: white; width: 100%;">
                <div wire:loading wire:target="pdfFile" style="color: #3B82F6; margin-top: 8px;">A carregar...</div>
            </div>
            
            <button wire:click="processar" style="width: 100%; padding: 12px; background: #3B82F6; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;">
                Processar PDF
            </button>
        </div>
        
        {{-- Dados extraídos --}}
        <div style="background: #1E293B; border-radius: 20px; padding: 24px; border: 1px solid #334155; max-height: 600px; overflow-y: auto;">
            <h3 style="font-size: 14px; font-weight: 700; color: #F1F5F9; margin: 0 0 16px 0;">📋 Dados Extraídos</h3>
            
            @if(!empty($dados))
                <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                    @foreach(['numero' => 'Nº', 'cliente_nome' => 'Cliente', 'evento_nome' => 'Evento', 'evento_local' => 'Local', 'data_inicio' => 'Início', 'data_fim' => 'Fim'] as $key => $label)
                        @if(!empty($dados[$key]))
                        <div style="display: flex; justify-content: space-between; padding: 6px 10px; background: #0F172A; border-radius: 6px; border: 1px solid #334155; font-size: 11px;">
                            <span style="color: #94A3B8;">{{ $label }}</span>
                            <span style="color: #E2E8F0; font-weight: 500;">{{ $dados[$key] }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            @endif
            
            @if(count($categoriasDetectadas) > 0)
                <div style="font-size: 10px; color: #64748B; margin-bottom: 8px;">{{ count($categoriasDetectadas) }} CATEGORIAS · {{ count($itensDetectados) }} ITENS</div>
                
                @php $catAtual = ''; @endphp
                @foreach($itensDetectados as $item)
                    @if($item['categoria'] !== $catAtual)
                        @php $catAtual = $item['categoria']; @endphp
                        <div style="color: #3B82F6; font-size: 11px; font-weight: 700; margin-top: 10px; margin-bottom: 4px; text-transform: uppercase;">{{ $catAtual }}</div>
                    @endif
                    <div style="display: flex; justify-content: space-between; padding: 4px 8px; background: #0F172A; border-radius: 4px; font-size: 10px; border: 1px solid #334155; margin-bottom: 2px;">
                        <span style="color: #E2E8F0;">{{ \Illuminate\Support\Str::limit($item['nome'], 40) }}</span>
                        <span style="color: #64748B;">x{{ $item['quantidade'] }}</span>
                    </div>
                @endforeach
                
                <button wire:click="criarOrcamento" style="width: 100%; padding: 12px; background: #10B981; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 16px;">
                    ✅ Criar Orçamento
                </button>
            @endif
            
            @if(empty($dados) && empty($itensDetectados))
                <p style="color: #64748B; font-size: 12px; text-align: center; padding: 20px;">Faça upload de um PDF do WorkPlanit</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
