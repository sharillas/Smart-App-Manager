<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Guia {{ $guia->numero }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { color: #3B82F6; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #3B82F6; color: white; }
    </style>
</head>
<body>
    <h1>Smartchoice Event Manager - Guia de Transporte</h1>
    <p><strong>Nº:</strong> {{ $guia->numero }}</p>
    <p><strong>Tipo:</strong> {{ $guia->tipo }}</p>
    <p><strong>Estado:</strong> {{ $guia->estado }}</p>
    <p><strong>Responsável:</strong> {{ $guia->responsavel }}</p>
    <p><strong>Orçamento:</strong> {{ $guia->orcamento->numero ?? 'N/A' }}</p>

    @if($guia->itens->count() > 0)
    <table>
        <tr><th>Equipamento</th><th>Qtd</th><th>Estado Saída</th><th>Estado Retorno</th></tr>
        @foreach($guia->itens as $item)
        <tr>
            <td>{{ $item->equipamento->nome ?? 'N/A' }}</td>
            <td>{{ $item->quantidade }}</td>
            <td>{{ $item->estado_saida }}</td>
            <td>{{ $item->estado_retorno }}</td>
        </tr>
        @endforeach
    </table>
    @endif
</body>
</html>
