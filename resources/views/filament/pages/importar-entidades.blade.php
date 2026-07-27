<x-filament-panels::page>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <div style="background: #1E293B; border-radius: 20px; padding: 32px; border: 1px solid #334155;">
            <h2 style="color: #F1F5F9; font-size: 18px; font-weight: 700; margin: 0 0 8px 0;">� Importar Entidades</h2>
            <p style="color: #94A3B8; font-size: 12px; margin-bottom: 20px;">Colunas: Nome | Designação Comercial | Tipo Entidade | NIF | País</p>
            
            <div style="background: #0F172A; border: 2px dashed #334155; border-radius: 12px; padding: 24px; margin-bottom: 16px;">
                <input type="file" wire:model="ficheiro" accept=".xlsx,.csv" style="color: white; width: 100%;">
            </div>
            
            <button wire:click="processar" style="width: 100%; padding: 12px; background: #3B82F6; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; margin-bottom: 8px;">� Analisar</button>
            
            @if($totalLinhas > 0)
            <button wire:click="importar" style="width: 100%; padding: 12px; background: #10B981; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;">✅ Importar {{ $totalLinhas }} Entidades</button>
            @endif
        </div>
        
        <div style="background: #1E293B; border-radius: 20px; padding: 24px; border: 1px solid #334155; max-height: 600px; overflow-y: auto;">
            <h3 style="font-size: 14px; font-weight: 700; color: #F1F5F9; margin: 0 0 16px 0;">� Preview</h3>
            @if(count($preview) > 0)
                <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    <tr style="background: #0F172A;">
                        <th style="padding: 4px;color:#94A3B8;">Nome</th>
                        <th style="padding: 4px;color:#94A3B8;">Designação</th>
                        <th style="padding: 4px;color:#94A3B8;">Tipo</th>
                        <th style="padding: 4px;color:#94A3B8;">NIF</th>
                        <th style="padding: 4px;color:#94A3B8;">País</th>
                    </tr>
                    @foreach(array_slice($preview, 1) as $row)
                    <tr style="border-bottom:1px solid #1E293B;">
                        <td style="padding:3px 4px;color:#E2E8F0;">{{ \Illuminate\Support\Str::limit($row[0] ?? '', 20) }}</td>
                        <td style="padding:3px 4px;color:#E2E8F0;">{{ \Illuminate\Support\Str::limit($row[1] ?? '', 15) }}</td>
                        <td style="padding:3px 4px;color:#E2E8F0;">{{ $row[2] ?? '' }}</td>
                        <td style="padding:3px 4px;color:#E2E8F0;">{{ $row[3] ?? '' }}</td>
                        <td style="padding:3px 4px;color:#E2E8F0;">{{ $row[4] ?? '' }}</td>
                    </tr>
                    @endforeach
                </table>
            @else
                <p style="color:#64748B;text-align:center;padding:20px;">Faça upload do Excel</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
