<x-filament-panels::page>
    <div style="margin-bottom: 16px;">
        <a href="{{ route('filament.admin.resources.categorias.index') }}" 
            style="display: inline-flex; align-items: center; gap: 6px; color: #94A3B8; text-decoration: none; font-size: 13px; font-weight: 500; padding: 8px 16px; border: 1px solid #334155; border-radius: 8px; transition: 0.15s;"
            onmouseover="this.style.background='#1E293B';this.style.color='#F1F5F9'"
            onmouseout="this.style.background='transparent';this.style.color='#94A3B8'">
            ← Voltar para Equipamentos
        </a>
    </div>

    <div style="background: #1E293B; border-radius: 20px; padding: 24px; border: 1px solid #334155;">
        <h2 style="color: #F1F5F9; font-size: 18px; font-weight: 700; margin: 0 0 16px 0;">💰 Gestão Rápida de Preços</h2>
        
        <div style="display: flex; gap: 12px; margin-bottom: 20px;">
            <select wire:model.live="departamento_id" style="background: #0F172A; color: #E2E8F0; border: 1px solid #334155; padding: 8px 12px; border-radius: 8px; font-size: 13px;">
                <option value="">Todos Departamentos</option>
                @foreach(\App\Models\Categoria::whereNull('parent_id')->get() as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                @endforeach
            </select>
            
            @if($departamento_id)
            <select wire:model.live="familia_id" style="background: #0F172A; color: #E2E8F0; border: 1px solid #334155; padding: 8px 12px; border-radius: 8px; font-size: 13px;">
                <option value="">Todas Famílias</option>
                @foreach(\App\Models\Categoria::where('parent_id', $departamento_id)->get() as $fam)
                    <option value="{{ $fam->id }}">{{ $fam->nome }}</option>
                @endforeach
            </select>
            @endif
        </div>
        
        <div style="max-height: 500px; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                <tr style="background: #0F172A; position: sticky; top: 0;">
                    <th style="padding: 8px; text-align: left; color: #94A3B8;">Equipamento</th>
                    <th style="padding: 8px; text-align: left; color: #94A3B8;">Departamento</th>
                    <th style="padding: 8px; text-align: center; color: #94A3B8; width: 100px;">Stock</th>
                    <th style="padding: 8px; text-align: right; color: #94A3B8; width: 130px;">Preço/Dia (€)</th>
                </tr>
                @foreach($equipamentos as $eq)
                <tr style="border-bottom: 1px solid #1E293B;">
                    <td style="padding: 6px 8px; color: #E2E8F0;">{{ \Illuminate\Support\Str::limit($eq->nome, 35) }}</td>
                    <td style="padding: 6px 8px; color: #94A3B8; font-size: 11px;">
                        {{ $eq->categoria->parent->parent->nome ?? $eq->categoria->parent->nome ?? '—' }}
                    </td>
                    <td style="padding: 6px 8px; text-align: center; color: #E2E8F0;">{{ $eq->quantidade }}</td>
                    <td style="padding: 6px 8px; text-align: right;">
                        <input type="number" wire:model.lazy="precos.{{ $eq->id }}" step="0.01" min="0"
                            style="width: 100px; background: #0F172A; color: #F59E0B; border: 1px solid #334155; padding: 6px 8px; border-radius: 6px; text-align: right; font-weight: 600; font-size: 12px;" placeholder="0.00">
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
        
        <div style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #64748B; font-size: 11px;">Mostrando {{ count($equipamentos) }} equipamentos</span>
            <button wire:click="salvarPrecos" style="padding: 10px 24px; background: #10B981; color: white; border: none; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer;">
                💾 Salvar Preços
            </button>
        </div>
    </div>
</x-filament-panels::page>
