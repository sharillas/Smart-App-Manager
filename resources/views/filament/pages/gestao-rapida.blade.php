<x-filament-panels::page>
    <div style="margin-bottom: 16px;">
        <a href="{{ route('filament.admin.resources.categorias.index') }}" style="display: inline-flex; align-items: center; gap: 6px; color: #94A3B8; text-decoration: none; font-size: 13px; padding: 8px 16px; border: 1px solid #334155; border-radius: 8px;">← Voltar</a>
    </div>

    <div style="display: flex; gap: 8px; margin-bottom: 20px;">
        <button wire:click="setTab('precos')" style="padding: 8px 20px; background: {{ $activeTab === 'precos' ? '#3B82F6' : 'transparent' }}; color: {{ $activeTab === 'precos' ? 'white' : '#94A3B8' }}; border: 1px solid {{ $activeTab === 'precos' ? '#3B82F6' : '#334155' }}; border-radius: 20px; cursor: pointer; font-weight: 600; font-size: 13px;">Preços</button>
        <button wire:click="setTab('categorias')" style="padding: 8px 20px; background: {{ $activeTab === 'categorias' ? '#3B82F6' : 'transparent' }}; color: {{ $activeTab === 'categorias' ? 'white' : '#94A3B8' }}; border: 1px solid {{ $activeTab === 'categorias' ? '#3B82F6' : '#334155' }}; border-radius: 20px; cursor: pointer; font-weight: 600; font-size: 13px;">Categorias</button>
    </div>

    {{-- TAB PREÇOS --}}
    @if($activeTab === 'precos')
    <div style="background: #1E293B; border-radius: 16px; padding: 24px; border: 1px solid #334155;">
        <div style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
            <select wire:model.lazy="departamento_id" style="background: #0F172A; color: #E2E8F0; border: 1px solid #334155; padding: 8px 12px; border-radius: 8px; font-size: 12px; min-width: 150px;">
                <option value="">Departamento</option>
                @foreach($this->getDepartamentos() as $d) <option value="{{ $d->id }}">{{ $d->nome }}</option> @endforeach
            </select>
            <select wire:model.lazy="familia_id" style="background: #0F172A; color: #E2E8F0; border: 1px solid #334155; padding: 8px 12px; border-radius: 8px; font-size: 12px; min-width: 150px;">
                <option value="">Família</option>
                @foreach($this->getFamilias() as $f) <option value="{{ $f->id }}">{{ $f->nome }}</option> @endforeach
            </select>
            <select wire:model.lazy="subfamilia_id" style="background: #0F172A; color: #E2E8F0; border: 1px solid #334155; padding: 8px 12px; border-radius: 8px; font-size: 12px; min-width: 150px;">
                <option value="">SubFamília</option>
                @foreach($this->getSubfamilias() as $s) <option value="{{ $s->id }}">{{ $s->nome }}</option> @endforeach
            </select>
        </div>
        
        <div style="max-height: 450px; overflow-y: auto;">
            @if(count($equipamentos) > 0)
            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                @foreach($equipamentos as $eq)
                <tr style="border-bottom: 1px solid #1E293B;">
                    <td style="padding:6px 8px;color:#E2E8F0;">{{ \Illuminate\Support\Str::limit($eq->nome, 50) }}</td>
                    <td style="padding:6px 8px;text-align:center;color:#94A3B8;width:70px;">{{ $eq->quantidade }}</td>
                    <td style="padding:6px 8px;width:120px;">
                        <input type="number" wire:model.lazy="precos.{{ $eq->id }}" step="0.01" min="0"
                            style="width:100px;background:#0F172A;color:#F59E0B;border:1px solid #334155;padding:5px 8px;border-radius:6px;text-align:right;font-weight:600;font-size:12px;">
                    </td>
                </tr>
                @endforeach
            </table>
            @else
            <p style="color:#64748B;text-align:center;padding:30px;">Selecione um filtro</p>
            @endif
        </div>
        @if(count($equipamentos) > 0)
        <div style="margin-top:16px;text-align:right;">
            <button wire:click="salvarPrecos" style="padding:6px 20px;background:transparent;color:#10B981;border:1px solid #10B981;border-radius:20px;cursor:pointer;font-size:11px;font-weight:600;">Guardar Preços</button>
        </div>
        @endif
    </div>
    @endif

    {{-- TAB CATEGORIAS --}}
    @if($activeTab === 'categorias')
    <div style="background: #1E293B; border-radius: 16px; padding: 24px; border: 1px solid #334155;">
        <div style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
            <select wire:model.lazy="deptSelecionado" style="background: #0F172A; color: #E2E8F0; border: 1px solid #334155; padding: 8px 12px; border-radius: 8px; font-size: 12px; min-width: 150px;">
                <option value="">Departamento</option>
                @foreach($this->getDepartamentos() as $d) <option value="{{ $d->id }}">{{ $d->nome }}</option> @endforeach
            </select>
            <select wire:model.lazy="familiaCatId" style="background: #0F172A; color: #E2E8F0; border: 1px solid #334155; padding: 8px 12px; border-radius: 8px; font-size: 12px; min-width: 150px;">
                <option value="">Família</option>
                @foreach($this->getFamiliasCat() as $f) <option value="{{ $f->id }}">{{ $f->nome }}</option> @endforeach
            </select>
        </div>
        
        <div style="max-height: 450px; overflow-y: auto;">
            @if(count($categorias) > 0)
            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                @foreach($categorias as $cat)
                <tr style="border-bottom: 1px solid #1E293B;">
                    <td style="padding:4px 8px;">
                        <input type="text" wire:model.lazy="nomesCategorias.{{ $cat->id }}" 
                            style="width:100%;background:#0F172A;color:#E2E8F0;border:1px solid #334155;padding:5px 8px;border-radius:6px;font-size:11px;">
                    </td>
                    <td style="padding:4px 8px;color:#94A3B8;font-size:11px;white-space:nowrap;">
                        {{ $cat->parent && $cat->parent->parent_id ? 'SubFamília' : 'Família' }}
                    </td>
                    <td style="padding:4px 8px;color:#64748B;font-size:11px;">{{ $cat->parent->nome ?? '—' }}</td>
                    <td style="padding:4px 8px;display:flex;gap:6px;">
                        <button wire:click="salvarCategoria({{ $cat->id }})" style="padding:3px 14px;background:transparent;color:#3B82F6;border:1px solid #3B82F6;border-radius:20px;cursor:pointer;font-size:10px;font-weight:600;">Guardar</button>
                        <button wire:click="removerCategoria({{ $cat->id }})" onclick="return confirm('Remover?')" style="padding:3px 14px;background:transparent;color:#EF4444;border:1px solid #EF4444;border-radius:20px;cursor:pointer;font-size:10px;font-weight:600;">Remover</button>
                    </td>
                </tr>
                @endforeach
            </table>
            @else
            <p style="color:#64748B;text-align:center;padding:30px;">Selecione um filtro</p>
            @endif
        </div>
    </div>
    @endif
</x-filament-panels::page>
