<x-filament-panels::page>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <div style="background: #1E293B; border-radius: 20px; padding: 32px; border: 1px solid #334155;">
            <h2 style="color: #F1F5F9; font-size: 18px; font-weight: 700; margin: 0 0 8px 0;">📥 Importar Equipamentos</h2>
            <p style="color: #94A3B8; font-size: 12px; margin-bottom: 20px;">
                Colunas: Nome | Departamento | Família | SubFamília | Armazém | Quantidade | Series | Preço/Dia
            </p>
            <p style="color: #64748B; font-size: 10px; margin-bottom: 16px;">
                Séries separadas por " | " (ex: SN001 | SN002 | SN003)
            </p>
            
            <div style="background: #0F172A; border: 2px dashed #334155; border-radius: 12px; padding: 24px; margin-bottom: 16px;">
                <input type="file" wire:model="ficheiro" accept=".xlsx,.csv" style="color: white; width: 100%;">
            </div>
            
            <button wire:click="processar" style="width: 100%; padding: 12px; background: #3B82F6; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; margin-bottom: 8px;">📊 Analisar</button>
            
            @if($totalLinhas > 0)
            <button wire:click="importar" style="width: 100%; padding: 12px; background: #10B981; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;">✅ Importar {{ $totalLinhas }} Equipamentos</button>
            @endif
        </div>
        
        <div style="background: #1E293B; border-radius: 20px; padding: 24px; border: 1px solid #334155; max-height: 600px; overflow-y: auto;">
            <h3 style="font-size: 14px; font-weight: 700; color: #F1F5F9; margin: 0 0 8px 0;">📋 Preview</h3>
            @if(count($colunasDetectadas) > 0)
            <p style="color: #64748B; font-size: 10px; margin-bottom: 8px;">Colunas: {{ implode(' | ', $colunasDetectadas) }}</p>
            @endif
            @if(count($preview) > 1)
                <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    @foreach($preview as $i => $row)
                    <tr style="border-bottom: 1px solid #1E293B; {{ $i === 0 ? 'background:#0F172A;font-weight:600;color:#94A3B8;' : '' }}">
                        @foreach($row as $cell)
                        <td style="padding:3px 4px;color:{{ $i === 0 ? '#94A3B8' : '#E2E8F0' }};">{{ \Illuminate\Support\Str::limit($cell ?? '', 25) }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </table>
            @else
                <p style="color:#64748B;text-align:center;padding:20px;">Faça upload do Excel</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
