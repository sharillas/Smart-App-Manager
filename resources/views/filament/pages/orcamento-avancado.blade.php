<x-filament-panels::page>
    {{-- Botão Voltar --}}
    <div style="margin-bottom: 16px;">
        <a href="{{ route('filament.admin.resources.orcamentos.index') }}" 
            style="display: inline-flex; align-items: center; gap: 6px; color: #94A3B8; text-decoration: none; font-size: 13px; font-weight: 500; padding: 8px 16px; border: 1px solid #334155; border-radius: 8px; transition: 0.15s;"
            onmouseover="this.style.background='#1E293B';this.style.color='#F1F5F9'"
            onmouseout="this.style.background='transparent';this.style.color='#94A3B8'">
            ← Voltar para Orçamentos
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 400px 1fr; gap: 20px; height: 75vh;">
        
        {{-- PAINEL ESQUERDO: Equipamentos Disponíveis --}}
        <div style="background: #1E293B; border-radius: 16px; padding: 16px; border: 1px solid #334155; display: flex; flex-direction: column; overflow: hidden;">
            <h3 style="color: #F1F5F9; font-size: 14px; font-weight: 700; margin: 0 0 10px 0;">📦 Equipamentos</h3>
            
            <select wire:model.live="departamento_id" style="width: 100%; background: #0F172A; border: 1px solid #334155; padding: 7px 10px; border-radius: 8px; color: #E2E8F0; font-size: 11px; margin-bottom: 6px;">
                <option value="">Todos Departamentos</option>
                @foreach($this->getDepartamentos() as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                @endforeach
            </select>
            
            <input type="text" wire:model.live.debounce.200ms="search" placeholder="🔍 Pesquisar..." 
                style="width: 100%; background: #0F172A; border: 1px solid #334155; padding: 7px 10px; border-radius: 8px; color: #E2E8F0; font-size: 11px; margin-bottom: 8px;">
            
            <div style="flex: 1; overflow-y: auto;">
                @foreach($this->getEquipamentos() as $eq)
                    @php $cor = match($eq->estado) {'disponivel'=>'#22c55e',default=>'#6b7280'}; @endphp
                    <div wire:click="adicionarEquipamento({{ $eq->id }})"
                        style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: #0F172A; border: 1px solid #334155; border-radius: 8px; cursor: pointer; font-size: 11px; margin-bottom: 3px; transition: 0.15s;"
                        onmouseover="this.style.borderColor='#3B82F6';this.style.background='#1E3A5F'"
                        onmouseout="this.style.borderColor='#334155';this.style.background='#0F172A'">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $cor }};flex-shrink:0"></span>
                        <span style="color:#E2E8F0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ \Illuminate\Support\Str::limit($eq->nome, 35) }}</span>
                        <span style="color:#64748B;font-size:10px;white-space:nowrap">{{ number_format($eq->preco_aluguer_dia ?? 0, 0) }}€</span>
                        <span style="color:#3B82F6;font-weight:700;font-size:16px">+</span>
                    </div>
                @endforeach
            </div>
        </div>
        
        {{-- PAINEL DIREITO: Orçamento --}}
        <div style="background: #1E293B; border-radius: 16px; padding: 16px; border: 1px solid #334155; display: flex; flex-direction: column; overflow: hidden;">
            
            {{-- Cabeçalho --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                <input type="text" wire:model="numero" placeholder="Nº Orçamento" style="background:#0F172A;border:1px solid #334155;padding:8px;border-radius:8px;color:#F1F5F9;font-size:12px" required>
                <input type="text" wire:model="cliente_nome" placeholder="Cliente" style="background:#0F172A;border:1px solid #334155;padding:8px;border-radius:8px;color:#F1F5F9;font-size:12px" required>
                <input type="text" wire:model="evento_nome" placeholder="Evento" style="background:#0F172A;border:1px solid #334155;padding:8px;border-radius:8px;color:#F1F5F9;font-size:12px">
                <input type="text" wire:model="evento_local" placeholder="Local" style="background:#0F172A;border:1px solid #334155;padding:8px;border-radius:8px;color:#F1F5F9;font-size:12px">
                <input type="date" wire:model="data_inicio" style="background:#0F172A;border:1px solid #334155;padding:8px;border-radius:8px;color:#F1F5F9;font-size:12px" required>
                <input type="date" wire:model="data_fim" style="background:#0F172A;border:1px solid #334155;padding:8px;border-radius:8px;color:#F1F5F9;font-size:12px" required>
                <select wire:model="estado" style="background:#0F172A;border:1px solid #334155;padding:8px;border-radius:8px;color:#F1F5F9;font-size:12px">
                    <option value="orcamentacao">Orçamentação</option>
                    <option value="draft">Draft</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
                <input type="text" wire:model="notas" placeholder="Notas" style="background:#0F172A;border:1px solid #334155;padding:8px;border-radius:8px;color:#F1F5F9;font-size:12px">
            </div>
            
            {{-- Itens do Orçamento --}}
            <h3 style="color:#F1F5F9;font-size:13px;font-weight:600;margin:0 0 8px 0;">🛒 Itens ({{ count($itens) }})</h3>
            
            <div style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:4px;margin-bottom:12px;">
                @foreach($itens as $index => $item)
                <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;background:#0F172A;border:1px solid #334155;border-radius:8px;font-size:11px">
                    <span style="color:#E2E8F0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $item['equipamento_nome'] }}</span>
                    
                    <button wire:click="diminuir({{ $index }})" style="background:#334155;color:#E2E8F0;border:none;border-radius:4px;width:24px;height:24px;cursor:pointer;font-size:14px">−</button>
                    <span style="color:#F1F5F9;font-weight:600;min-width:25px;text-align:center">{{ $item['quantidade'] }}</span>
                    <button wire:click="aumentar({{ $index }})" style="background:#334155;color:#E2E8F0;border:none;border-radius:4px;width:24px;height:24px;cursor:pointer;font-size:14px">+</button>
                    
                    <input type="number" value="{{ $item['preco_unitario'] }}" 
                        wire:change="atualizarPreco({{ $index }}, $event.target.value)"
                        style="background:#0F172A;border:1px solid #334155;padding:4px 6px;border-radius:4px;color:#F59E0B;font-size:11px;width:70px;text-align:right" step="0.01">
                    
                    <span style="color:#10B981;font-weight:600;min-width:65px;text-align:right">{{ number_format($item['subtotal'], 0) }}€</span>
                    
                    <button wire:click="remover({{ $index }})" style="background:#EF4444;color:white;border:none;border-radius:4px;width:24px;height:24px;cursor:pointer;font-size:12px">✕</button>
                </div>
                @endforeach
                
                @if(count($itens) === 0)
                <p style="color:#64748B;text-align:center;padding:20px;font-size:12px">Clique nos equipamentos à esquerda para adicionar</p>
                @endif
            </div>
            
            {{-- Total e Salvar --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#0F172A;border-radius:12px;border:1px solid #334155;">
                <span style="color:#94A3B8;font-size:14px">Total</span>
                <span style="color:#10B981;font-size:22px;font-weight:700">{{ number_format($total, 2) }} €</span>
                <button wire:click="salvar" style="padding:10px 32px;background:#10B981;color:white;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer">
                    💾 Salvar Orçamento
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
