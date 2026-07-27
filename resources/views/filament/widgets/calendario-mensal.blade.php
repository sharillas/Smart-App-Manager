<div style="background: #1E293B; border-radius: 16px; padding: 16px; border: 1px solid #334155; overflow-x: auto;">
    {{-- Cabeçalho --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <button wire:click="mesAnterior" style="background: #334155; color: #E2E8F0; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 12px;">←</button>
            <h2 style="color: #F1F5F9; font-size: clamp(12px, 3vw, 18px); font-weight: 700; margin: 0; text-align: center; min-width: 140px;">{{ $this->getNomeMes() }}</h2>
            <button wire:click="mesSeguinte" style="background: #334155; color: #E2E8F0; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 12px;">→</button>
        </div>
        <div style="display: flex; gap: 6px; font-size: 9px; font-weight: 600; flex-wrap: wrap;">
            <span style="display: flex; align-items: center; gap: 2px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: #10B981;"></span> Confirmado</span>
            <span style="display: flex; align-items: center; gap: 2px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: #F59E0B;"></span> Orçamentação</span>
            <span style="display: flex; align-items: center; gap: 2px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: #3B82F6;"></span> Draft</span>
            <span style="display: flex; align-items: center; gap: 2px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: #EF4444;"></span> Cancelado</span>
        </div>
    </div>
    
    {{-- Container scroll horizontal --}}
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <div style="min-width: 650px;">
            {{-- Dias da Semana --}}
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 2px;">
                @foreach(['S','T','Q','Q','S','S','D'] as $dia)
                    <div style="padding: 4px; text-align: center; color: #94A3B8; font-size: 9px; font-weight: 600;">{{ $dia }}</div>
                @endforeach
            </div>
            
            {{-- Grelha --}}
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;">
                @foreach($this->getDiasDoMes() as $dia)
                    @php
                        $bg = '#0F172A'; $border = '1px solid #1E293B';
                        if ($dia['hoje']) { $bg = '#1E3A5F'; $border = '2px solid #3B82F6'; }
                        elseif ($dia['fim_semana']) { $border = '1px solid #EF444420'; }
                        if (!$dia['mes_atual']) { $bg = '#0a0f1a'; }
                    @endphp
                    <div style="background: {{ $bg }}; border: {{ $border }}; border-radius: 6px; padding: 3px; min-height: 55px; {{ !$dia['mes_atual'] ? 'opacity:0.4;' : '' }}">
                        <div style="color: {{ $dia['hoje'] ? 'white' : '#94A3B8' }}; font-size: 9px; font-weight: {{ $dia['hoje'] ? '700' : '400' }}; margin-bottom: 2px;">{{ $dia['dia'] }}</div>
                        @foreach($dia['eventos'] as $evento)
                            <div onclick="document.getElementById('modal-mes-{{ $evento['id'] }}').style.display='flex'; event.stopPropagation();"
                                style="background: {{ $evento['cor'] }}; color: white; padding: 1px 3px; border-radius: 3px; font-size: 7px; font-weight: 600; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; cursor: pointer; margin-bottom: 1px;">
                                {{ $evento['cliente'] }}
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    {{-- Modais --}}
    @foreach($this->getDiasDoMes() as $dia)
        @foreach($dia['eventos'] as $evento)
        <div id="modal-mes-{{ $evento['id'] }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;" onclick="this.style.display='none'">
            <div onclick="event.stopPropagation()" style="background: #1E293B; border-radius: 16px; max-width: 450px; width: 100%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.5); border: 1px solid #334155;">
                <div style="background: {{ $evento['cor'] }}; padding: 16px; border-radius: 16px 16px 0 0; color: white;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <div style="font-size: 10px; opacity: 0.8;">{{ $evento['numero'] }}</div>
                            <div style="font-size: 16px; font-weight: 700; margin-top: 2px;">{{ $evento['cliente'] }}</div>
                            <div style="font-size: 12px; opacity: 0.9;">{{ $evento['evento'] ?: 'Sem nome' }}</div>
                        </div>
                        <span style="background: rgba(255,255,255,0.25); padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: 700;">{{ match($evento['estado']) {'confirmado'=>'Confirmado','orcamentacao'=>'Orçamentação','draft'=>'Draft','cancelado'=>'Cancelado',default=>$evento['estado']} }}</span>
                    </div>
                </div>
                <div style="padding: 12px 16px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                        <div style="background: #0F172A; padding: 8px 10px; border-radius: 8px; border: 1px solid #334155;">
                            <div style="font-size: 9px; color: #64748B;">📍 Local</div>
                            <div style="font-size: 12px; font-weight: 600; color: #F1F5F9;">{{ $evento['local'] ?: '—' }}</div>
                        </div>
                        <div style="background: #0F172A; padding: 8px 10px; border-radius: 8px; border: 1px solid #334155;">
                            <div style="font-size: 9px; color: #64748B;">📅 Período</div>
                            <div style="font-size: 12px; font-weight: 600; color: #F1F5F9;">{{ $evento['inicio'] }} → {{ $evento['fim'] }}</div>
                        </div>
                    </div>
                    @if(count($evento['equipamentos']) > 0)
                    <div style="border-top: 1px solid #334155; padding-top: 8px;">
                        <div style="font-size: 10px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">Equipamentos ({{ $evento['total_equipamentos'] }})</div>
                        @foreach($evento['equipamentos'] as $eq)
                        <div style="display: flex; justify-content: space-between; padding: 4px 8px; background: #0F172A; border-radius: 6px; border: 1px solid #334155; font-size: 10px; margin-bottom: 2px;">
                            <span style="color: #F1F5F9;">{{ $eq['nome'] }}</span>
                            <span style="color: #94A3B8;">x{{ $eq['quantidade'] }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div style="padding: 8px 16px; border-top: 1px solid #334155; display: flex; justify-content: flex-end; gap: 6px;">
                    <button onclick="document.getElementById('modal-mes-{{ $evento['id'] }}').style.display='none'" style="padding: 6px 16px; background: #334155; border: none; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 600; color: #CBD5E1;">Fechar</button>
                    <a href="/admin/orcamento-avancado?record={{ $evento['id'] }}" style="padding: 6px 16px; background: {{ $evento['cor'] }}; border: none; border-radius: 8px; font-size: 11px; font-weight: 600; color: white; text-decoration: none;">Editar</a>
                </div>
            </div>
        </div>
        @endforeach
    @endforeach
</div>
