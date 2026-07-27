<div style="background: #1E293B; border-radius: 16px; padding: 16px; border: 1px solid #334155; overflow-x: auto;">
    {{-- Cabeçalho --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
        <div>
            <h2 style="font-size: clamp(14px, 3vw, 20px); font-weight: 700; color: #F1F5F9; margin: 0;">Agenda Semanal</h2>
            <p style="font-size: clamp(10px, 2vw, 12px); color: #94A3B8; margin: 2px 0 0 0;">
                {{ \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('d M') }} → {{ \Carbon\Carbon::now()->endOfWeek(\Carbon\Carbon::SUNDAY)->format('d M Y') }}
            </p>
        </div>
        <div style="display: flex; gap: 8px; font-size: 10px; font-weight: 600; flex-wrap: wrap;">
            <span style="display: flex; align-items: center; gap: 3px;"><span style="width: 6px; height: 6px; border-radius: 50%; background: #10B981;"></span><span style="color: #CBD5E1;">Confirmado</span></span>
            <span style="display: flex; align-items: center; gap: 3px;"><span style="width: 6px; height: 6px; border-radius: 50%; background: #F59E0B;"></span><span style="color: #CBD5E1;">Orçamentação</span></span>
            <span style="display: flex; align-items: center; gap: 3px;"><span style="width: 6px; height: 6px; border-radius: 50%; background: #3B82F6;"></span><span style="color: #CBD5E1;">Draft</span></span>
            <span style="display: flex; align-items: center; gap: 3px;"><span style="width: 6px; height: 6px; border-radius: 50%; background: #EF4444;"></span><span style="color: #CBD5E1;">Cancelado</span></span>
        </div>
    </div>
    
    {{-- Container com scroll horizontal --}}
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <div style="min-width: 700px;">
            {{-- Dias da Semana --}}
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 8px;">
                @foreach($this->getSemanaAtual() as $index => $dia)
                    @php
                        $isWeekend = in_array($dia['nome'], ['Sat', 'Sun', 'Sáb', 'Dom']);
                        $isToday = $dia['hoje'];
                        if ($isToday) { $bg = '#3B82F6'; $textColor = 'white'; }
                        elseif ($isWeekend) { $bg = '#1E293B'; $textColor = '#EF4444'; $border = '1px solid #EF4444'; }
                        else { $bg = '#334155'; $textColor = '#E2E8F0'; }
                    @endphp
                    <div style="padding: 8px 4px; text-align: center; border-radius: 8px; font-weight: 600; font-size: clamp(8px, 2vw, 12px); background: {{ $bg }}; color: {{ $textColor }}; {{ isset($border) ? 'border: '.$border.';' : '' }}">
                        <div style="font-size: 9px; opacity: 0.8;">{{ substr($dia['nome'], 0, 3) }}</div>
                        <div style="font-size: clamp(12px, 2.5vw, 18px); font-weight: 700;">{{ $dia['dia'] }}</div>
                    </div>
                @endforeach
            </div>
            
            {{-- Grelha --}}
            <div style="position: relative; min-height: 120px;">
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;">
                    @for($i = 0; $i < 7; $i++)
                        <div style="background: {{ ($this->getDiaAtual() == $i + 1) ? '#1E3A5F' : '#0F172A' }}; min-height: 100px; border-radius: 8px; border: 1px solid {{ ($i == 5 || $i == 6) ? '#EF444440' : '#334155' }};"></div>
                    @endfor
                </div>
                
                <div style="position: absolute; top: 4px; left: 2px; right: 2px;">
                    @foreach($this->getOrcamentos() as $evento)
                        @php $largura = $evento->duracao * 100 / 7; $esquerda = ($evento->coluna_inicio - 1) * 100 / 7; @endphp
                        <div style="margin-bottom: 3px; height: 30px; position: relative;">
                            <div onclick="document.getElementById('modal-{{ $evento->id }}').style.display='flex'; event.stopPropagation();" style="
                                position: absolute; left: {{ $esquerda }}%; width: {{ $largura }}%; min-width: 40px;
                                background: {{ $evento->cor }}; color: white; padding: 4px 6px; border-radius: 6px;
                                font-size: clamp(8px, 1.5vw, 11px); font-weight: 600; cursor: pointer;
                                overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
                                box-shadow: 0 2px 8px rgba(0,0,0,0.3); transition: all 0.15s; z-index: 5;
                            " onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                {{ $evento->numero }} | {{ $evento->cliente }}
                            </div>
                        </div>
                        
                        {{-- MODAL --}}
                        <div id="modal-{{ $evento->id }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 16px;" onclick="this.style.display='none'">
                            <div onclick="event.stopPropagation()" style="background: #1E293B; border-radius: 16px; max-width: 500px; width: 100%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.5); border: 1px solid #334155;">
                                <div style="background: {{ $evento->cor }}; padding: 16px; border-radius: 16px 16px 0 0; color: white;">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div>
                                            <div style="font-size: 10px; opacity: 0.8;">{{ $evento->numero }}</div>
                                            <div style="font-size: 16px; font-weight: 700; margin-top: 2px;">{{ $evento->cliente }}</div>
                                            <div style="font-size: 12px; opacity: 0.9;">{{ $evento->evento ?: 'Sem nome' }}</div>
                                        </div>
                                        <span style="background: rgba(255,255,255,0.25); padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: 700;">{{ $evento->estado_nome }}</span>
                                    </div>
                                </div>
                                <div style="padding: 12px 16px;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                                        <div style="background: #0F172A; padding: 8px 10px; border-radius: 8px; border: 1px solid #334155;">
                                            <div style="font-size: 9px; color: #64748B;">📍 Local</div>
                                            <div style="font-size: 12px; font-weight: 600; color: #F1F5F9;">{{ $evento->local ?: '—' }}</div>
                                        </div>
                                        <div style="background: #0F172A; padding: 8px 10px; border-radius: 8px; border: 1px solid #334155;">
                                            <div style="font-size: 9px; color: #64748B;">📅 Período</div>
                                            <div style="font-size: 12px; font-weight: 600; color: #F1F5F9;">{{ $evento->inicio }} → {{ $evento->fim }}</div>
                                        </div>
                                    </div>
                                    @if(count($evento->equipamentos) > 0)
                                    <div style="border-top: 1px solid #334155; padding-top: 8px;">
                                        <div style="font-size: 10px; font-weight: 700; color: #94A3B8; margin-bottom: 6px;">Equipamentos ({{ $evento->total_equipamentos }})</div>
                                        @foreach($evento->equipamentos as $eq)
                                        <div style="display: flex; justify-content: space-between; padding: 4px 8px; background: #0F172A; border-radius: 6px; border: 1px solid #334155; font-size: 10px; margin-bottom: 2px;">
                                            <span style="color: #F1F5F9;">{{ $eq['nome'] }}</span>
                                            <span style="color: #94A3B8;">x{{ $eq['quantidade'] }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                <div style="padding: 8px 16px; border-top: 1px solid #334155; display: flex; justify-content: flex-end; gap: 6px;">
                                    <button onclick="document.getElementById('modal-{{ $evento->id }}').style.display='none'" style="padding: 6px 16px; background: #334155; border: none; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 600; color: #CBD5E1;">Fechar</button>
                                    <a href="/admin/orcamento-avancado?record={{ $evento->id }}" style="padding: 6px 16px; background: {{ $evento->cor }}; border: none; border-radius: 8px; font-size: 11px; font-weight: 600; color: white; text-decoration: none;">Editar</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
