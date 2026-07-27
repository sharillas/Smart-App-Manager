<x-filament-panels::page>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        {{-- Scanner --}}
        <div style="background: #1E293B; border-radius: 20px; padding: 32px; text-align: center; border: 1px solid #334155;">
            <div style="font-size: 48px; margin-bottom: 16px;">📷</div>
            <h2 style="color: #F1F5F9; font-size: 18px; font-weight: 700; margin: 0 0 8px 0;">Check-in / Check-out</h2>
            <p style="color: #94A3B8; font-size: 12px; margin-bottom: 20px;">Aponte o scanner e leia o QR Code</p>
            
            <div style="background: #0F172A; border: 2px dashed #334155; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                <input 
                    type="text" 
                    wire:model="qrCode"
                    wire:keydown.enter="scanQR"
                    placeholder="Código QR ou Nº Série..."
                    autofocus
                    style="width: 100%; background: transparent; border: none; color: white; font-size: 16px; text-align: center; outline: none; font-family: monospace;"
                >
            </div>
            
            <button 
                wire:click="scanQR"
                style="width: 100%; padding: 12px; background: #3B82F6; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;"
            >
                Registar
            </button>
        </div>
        
        {{-- Últimos Registos - Dark Mode --}}
        <div style="background: #1E293B; border-radius: 20px; padding: 24px; border: 1px solid #334155;">
            <h3 style="font-size: 14px; font-weight: 700; color: #F1F5F9; margin: 0 0 16px 0;">📋 Últimos Registos</h3>
            
            @if($ultimoScan)
                <div style="background: {{ $ultimoScan['estado_novo'] === 'alugado' ? '#422006' : '#052E16' }}; border-radius: 12px; padding: 16px; margin-bottom: 16px; border: 1px solid {{ $ultimoScan['estado_novo'] === 'alugado' ? '#F59E0B' : '#10B981' }};">
                    <div style="font-size: 20px; margin-bottom: 4px;">
                        {{ $ultimoScan['estado_novo'] === 'alugado' ? '🚚 CHECK-OUT' : '✅ CHECK-IN' }}
                    </div>
                    <div style="font-weight: 700; color: #F1F5F9;">{{ $ultimoScan['equipamento'] }}</div>
                    <div style="font-size: 12px; color: #94A3B8;">S/N: {{ $ultimoScan['numero_serie'] }}</div>
                    <div style="font-size: 11px; color: #64748B; margin-top: 4px;">{{ $ultimoScan['hora'] }}</div>
                </div>
            @endif
            
            @if(count($historico) > 0)
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    @foreach($historico as $h)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #0F172A; border-radius: 8px; font-size: 11px; border: 1px solid #334155;">
                            <span style="color: #E2E8F0; font-weight: 500;">{{ $h['equipamento'] }}</span>
                            <span style="color: {{ $h['estado_novo'] === 'alugado' ? '#F59E0B' : '#10B981' }}; font-weight: 600;">
                                {{ $h['estado_novo'] === 'alugado' ? 'SAÍDA' : 'ENTRADA' }}
                            </span>
                            <span style="color: #64748B;">{{ $h['hora'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #64748B; font-size: 12px; text-align: center; padding: 20px;">Nenhum registo ainda</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
