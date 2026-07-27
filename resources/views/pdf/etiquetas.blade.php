<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4; margin: 2mm; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; }
        td { 
            width: 50mm; 
            height: 25mm; 
            border: 0.5px dashed #e2e8f0; 
            padding: 1mm 2mm;
            vertical-align: middle;
        }
        .etiqueta-inner { 
            display: flex; 
            align-items: center; 
            gap: 2mm;
            height: 100%;
        }
        .qr-img { width: 15mm; height: 15mm; }
        .info { flex: 1; }
        .nome { font-size: 6px; font-weight: 700; color: #1e293b; text-transform: uppercase; }
        .sn { font-size: 6px; color: #ef4444; font-weight: 700; }
        .code { font-size: 4px; color: #94a3b8; }
    </style>
</head>
<body>
    <table>
        @php $count = 0; @endphp
        @foreach($equipamento->numerosSerie as $ns)
            @if($count % 3 == 0) <tr> @endif
            <td>
                <div class="etiqueta-inner">
                    <img src="data:image/svg+xml;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate($ns->qr_code)) }}" class="qr-img" alt="QR">
                    <div class="info">
                        <div class="nome">{{ \Illuminate\Support\Str::limit($equipamento->nome, 16) }}</div>
                        <div class="sn">S/N: {{ $ns->numero_serie }}</div>
                    </div>
                </div>
            </td>
            @php $count++; @endphp
            @if($count % 3 == 0) </tr> @endif
        @endforeach
        @if($count % 3 != 0) </tr> @endif
    </table>
</body>
</html>
