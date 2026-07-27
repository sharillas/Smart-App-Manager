<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { 
            size: 50mm 25mm; 
            margin: 1.5mm; 
        }
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100%;
        }
        .etiqueta { 
            display: flex;
            align-items: center;
            gap: 3mm;
            width: 100%;
            height: 100%;
        }
        .qr { 
            width: 18mm; 
            height: 18mm; 
            flex-shrink: 0;
        }
        .info { 
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .info .nome { 
            font-size: 7px; 
            font-weight: 700; 
            color: #1e293b; 
            text-transform: uppercase;
            line-height: 1.2;
        }
        .info .sn { 
            font-size: 7px; 
            color: #ef4444; 
            font-weight: 700; 
            margin-top: 1mm;
        }
        .info .code { 
            font-size: 5px; 
            color: #94a3b8; 
            margin-top: 0.5mm;
        }
    </style>
</head>
<body>
    <div class="etiqueta">
        <img src="data:image/svg+xml;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate($ns->qr_code)) }}" class="qr" alt="QR">
        <div class="info">
            <div class="nome">{{ \Illuminate\Support\Str::limit($ns->equipamento->nome, 20) }}</div>
            <div class="sn">S/N: {{ $ns->numero_serie }}</div>
            <div class="code">{{ $ns->qr_code }}</div>
        </div>
    </div>
</body>
</html>
