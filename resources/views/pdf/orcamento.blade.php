<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orçamento {{ $orcamento->numero }}</title>
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 0; }
        
        /* Header com background */
        .header-azul { 
            background: #1E293B; 
            padding: 20px 28px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            border-radius: 0 0 0 0;
        }
        .logo-img { max-height: 45px; max-width: 220px; }
        .logo-fallback { font-size: 22px; font-weight: 700; color: white; }
        .logo-fallback span { color: #3B82F6; }
        .header-info { text-align: right; }
        .header-info h1 { color: white; margin: 0; font-size: 18px; font-weight: 600; }
        .header-info .num { color: #94a3b8; font-size: 10px; margin-top: 2px; }
        .header-info .estado { 
            display: inline-block; 
            padding: 3px 12px; 
            border-radius: 12px; 
            font-size: 9px; 
            font-weight: 600; 
            margin-top: 4px;
            @php
                $corEstado = match($orcamento->estado) {
                    'confirmado' => '#10B981',
                    'orcamentacao' => '#F59E0B',
                    'draft' => '#3B82F6',
                    'cancelado' => '#EF4444',
                    default => '#6B7280'
                };
            @endphp
            background: {{ $corEstado }}; 
            color: white;
        }
        
        /* Conteúdo */
        .content { padding: 20px 28px; }
        
        .info-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
        .info-item { 
            border: 1px solid #e2e8f0; 
            padding: 8px 12px; 
            border-radius: 8px; 
            min-width: 100px; 
            flex: 1;
            background: white;
        }
        .info-label { font-size: 7px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px; }
        .info-value { font-size: 11px; font-weight: 600; color: #1e293b; }
        
        .categoria-titulo { 
            font-size: 11px; 
            font-weight: 700; 
            color: #3B82F6; 
            margin: 18px 0 8px 0; 
            padding-bottom: 4px; 
            border-bottom: 1px solid #e2e8f0; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th { padding: 6px 8px; text-align: left; font-size: 8px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; }
        td { padding: 6px 8px; border-bottom: 1px solid #f8fafc; font-size: 10px; color: #475569; }
        
        .total-row { 
            text-align: right; 
            font-size: 14px; 
            font-weight: 700; 
            margin-top: 20px; 
            padding: 12px 16px; 
            border: 1px solid #3B82F6; 
            border-radius: 8px; 
            color: #3B82F6;
            background: white;
        }
        
        .footer { 
            margin-top: 24px; 
            font-size: 8px; 
            color: #cbd5e1; 
            text-align: center; 
            border-top: 1px solid #f1f5f9; 
            padding: 12px 0 0 0;
        }
        .sub-tag { display: inline-block; border: 1px solid #F59E0B; color: #F59E0B; padding: 1px 5px; border-radius: 3px; font-size: 8px; font-weight: 600; }
    </style>
</head>
<body>
    {{-- Header escuro com logo --}}
    <div class="header-azul">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" class="logo-img" alt="Smartchoice">
        @else
            <div class="logo-fallback"><span>Smart</span>choice</div>
        @endif
        <div class="header-info">
            <h1>Orçamento</h1>
            <div class="num">{{ $orcamento->numero }}</div>
            <div class="estado">{{ ucfirst($orcamento->estado) }}</div>
        </div>
    </div>
    
    {{-- Conteúdo --}}
    <div class="content">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Cliente</div>
                <div class="info-value">{{ $orcamento->cliente_nome }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $orcamento->cliente_email ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Telefone</div>
                <div class="info-value">{{ $orcamento->cliente_telefone ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Evento</div>
                <div class="info-value">{{ $orcamento->evento_nome ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Local</div>
                <div class="info-value">{{ $orcamento->evento_local ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Período</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($orcamento->data_inicio)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($orcamento->data_fim)->format('d/m/Y') }}</div>
            </div>
        </div>
        
        @php
            $itensAgrupados = [];
            foreach ($orcamento->itens as $item) {
                $cat = $item->equipamento->categoria ?? null;
                $catPai = 'Outros';
                if ($cat) {
                    if ($cat->parent && $cat->parent->parent) $catPai = strtoupper($cat->parent->parent->nome);
                    elseif ($cat->parent) $catPai = strtoupper($cat->parent->nome);
                    else $catPai = strtoupper($cat->nome);
                }
                if (!isset($itensAgrupados[$catPai])) $itensAgrupados[$catPai] = ['itens' => []];
                $itensAgrupados[$catPai]['itens'][] = $item;
            }
        @endphp
        
        @foreach($itensAgrupados as $categoriaNome => $grupo)
            <div class="categoria-titulo">{{ $categoriaNome }}</div>
            <table>
                <tr>
                    <th>Equipamento</th>
                    <th style="text-align:center;width:35px">Qtd</th>
                    <th style="text-align:right;width:65px">Preço/Dia</th>
                    <th style="text-align:center;width:35px">Dias</th>
                    <th style="text-align:right;width:65px">Subtotal</th>
                    <th style="text-align:center;width:35px"></th>
                </tr>
                @foreach($grupo['itens'] as $item)
                <tr>
                    <td>{{ $item->equipamento->nome ?? 'N/A' }}</td>
                    <td style="text-align:center">{{ $item->quantidade }}</td>
                    <td style="text-align:right">{{ number_format($item->preco_unitario, 2) }}€</td>
                    <td style="text-align:center">{{ $item->dias }}</td>
                    <td style="text-align:right">{{ number_format($item->subtotal, 2) }}€</td>
                    <td style="text-align:center">
                        @if($item->subaluguer)<span class="sub-tag">EXT</span>@endif
                    </td>
                </tr>
                @endforeach
            </table>
        @endforeach
        
        <div class="total-row">
            Total {{ number_format($orcamento->valor_total, 2) }}€
        </div>
        
        @if($orcamento->notas)
        <div style="margin-top:14px;padding:10px 14px;border:1px solid #fde68a;border-radius:6px;font-size:9px;color:#92400e;background:white;">
            <strong>Notas:</strong> {{ $orcamento->notas }}
        </div>
        @endif
    </div>
    
    <div class="footer">
        Smartchoice Event Manager · {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
