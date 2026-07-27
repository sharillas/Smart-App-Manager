<x-filament-panels::page>
<div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
    <a href="{{ route('filament.admin.resources.categorias.index') }}" style="display: inline-flex; align-items: center; gap: 6px; color: #94A3B8; text-decoration: none; font-size: 13px; padding: 8px 16px; border: 1px solid #334155; border-radius: 8px;">← Voltar</a>
    <div style="display: flex; align-items: center; gap: 12px;">
        <button wire:click="semanaAnterior" style="background: #334155; color: #E2E8F0; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">←</button>
        <span style="color: #F1F5F9; font-weight: 600;">
            {{ \Carbon\Carbon::parse($semanaInicio)->format('d M') }} - {{ \Carbon\Carbon::parse($semanaInicio)->addDays(6)->format('d M Y') }}
        </span>
        <button wire:click="semanaSeguinte" style="background: #334155; color: #E2E8F0; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">→</button>
    </div>
</div>

@if($mostrarModal)
<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; justify-content: center; align-items: center;" wire:click="fecharModal">
    <div wire:click.stop style="background: #1E293B; border-radius: 16px; padding: 20px; max-width: 500px; width: 100%; max-height: 70vh; overflow-y: auto; border: 1px solid #334155;">
        <h3 style="color: #F1F5F9; margin-bottom: 16px;">Selecionar Evento</h3>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($eventos as $evento)
                @php $cor = match($evento['estado']) {'confirmado'=>'#10B981','orcamentacao'=>'#F59E0B','draft'=>'#3B82F6',default=>'#6B7280'}; @endphp
                <div wire:click="alocarTecnico({{ $tecnicoSelecionado }}, {{ $evento['id'] }})" style="padding: 12px; background: #0F172A; border: 1px solid #334155; border-radius: 8px; cursor: pointer;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: {{ $cor }}; font-weight: 600;">{{ $evento['numero'] }}</span>
                        <span style="color: #94A3B8; font-size: 11px;">{{ $evento['data_inicio'] }} → {{ $evento['data_fim'] }}</span>
                    </div>
                    <div style="color: #E2E8F0; margin-top: 4px;">{{ $evento['cliente_nome'] }}</div>
                </div>
            @endforeach
        </div>
        <button wire:click="fecharModal" style="margin-top: 12px; padding: 8px 20px; background: #334155; color: white; border: none; border-radius: 8px; cursor: pointer;">Fechar</button>
    </div>
</div>
@endif

<div style="background: #1E293B; border-radius: 16px; padding: 16px; border: 1px solid #334155; overflow-x: auto;">
    <div style="min-width: 900px;">
        <div style="display: grid; grid-template-columns: 180px repeat(7, 1fr); gap: 4px; margin-bottom: 8px;">
            <div style="color: #94A3B8; font-size: 11px; font-weight: 600; padding: 4px;">Eventos</div>
            @foreach($this->getDiasSemana() as $dia)
                <div style="background: {{ $dia->isToday() ? '#1E3A5F' : '#0F172A' }}; border: 1px solid {{ $dia->isToday() ? '#3B82F6' : '#334155' }}; border-radius: 8px; padding: 6px 4px; text-align: center;">
                    <div style="color: {{ $dia->isToday() ? 'white' : '#94A3B8' }}; font-size: 10px;">{{ $dia->format('D') }}</div>
                    <div style="color: {{ $dia->isToday() ? 'white' : '#E2E8F0' }}; font-size: 13px; font-weight: 700;">{{ $dia->format('d') }}</div>
                </div>
            @endforeach
        </div>
        
        <div style="display: grid; grid-template-columns: 180px repeat(7, 1fr); gap: 4px; margin-bottom: 16px;">
            <div></div>
            @foreach($this->getDiasSemana() as $dia)
                <div style="min-height: 44px; background: #0F172A; border-radius: 6px; border: 1px solid #1E293B; padding: 2px;">
                    @foreach($eventos as $evento)
                        @php
                            $inicio = \Carbon\Carbon::parse($evento['data_inicio']);
                            $fim = \Carbon\Carbon::parse($evento['data_fim']);
                        @endphp
                        @if($dia->between($inicio, $fim))
                            @php $cor = match($evento['estado']) {'confirmado'=>'#10B981','orcamentacao'=>'#F59E0B','draft'=>'#3B82F6',default=>'#6B7280'}; @endphp
                            <div style="background: {{ $cor }}; color: white; padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; margin-bottom: 1px;">
                                {{ $evento['numero'] }} | {{ \Illuminate\Support\Str::limit($evento['cliente_nome'], 12) }}
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
        
        @foreach($tecnicos as $tecnico)
            @php
                $corTecnico = match($tecnico['funcao'] ?? '') {
                    'Audio' => '#22c55e', 'Video' => '#3b82f6', 'Estruturas' => '#f59e0b',
                    'Iluminacao' => '#ef4444', 'Mobiliario' => '#8b5cf6', default => '#6b7280'
                };
            @endphp
            <div style="display: grid; grid-template-columns: 180px repeat(7, 1fr); gap: 4px; margin-bottom: 4px; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: #0F172A; border-radius: 6px; border: 1px solid #1E293B;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $corTecnico }};"></span>
                    <span style="color: #E2E8F0; font-size: 12px; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $tecnico['nome'] }}</span>
                </div>
                @foreach($this->getDiasSemana() as $dia)
                    @php
                        $escala = $this->temEscala($tecnico['id'], $dia->format('Y-m-d'));
                    @endphp
                    <div style="min-height: 38px; background: {{ $escala ? $corTecnico.'60' : '#0F172A' }}; border: 2px solid {{ $escala ? $corTecnico : '#1E293B' }}; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                        @if(!$escala)
                            wire:click="abrirModal({{ $tecnico['id'] }})"
                        @else
                            wire:click="removerAlocacao({{ $escala['id'] }})"
                        @endif
                        title="{{ !$escala ? 'Clique para alocar' : $escala['orcamento_numero'] . ' | ' . $escala['orcamento_cliente'] }}">
                        @if($escala)
                            <span style="font-size: 9px; color: {{ $corTecnico }}; font-weight: 700; padding: 2px 6px; background: {{ $corTecnico }}20; border-radius: 4px; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                                {{ $escala['orcamento_numero'] }} | {{ \Illuminate\Support\Str::limit($escala['orcamento_cliente'], 10) }}
                            </span>
                        @else
                            <span style="font-size: 16px; color: #334155;">+</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
</x-filament-panels::page>
