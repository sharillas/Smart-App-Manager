<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reparação #{{ $reparacao->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { color: #EF4444; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #EF4444; color: white; }
    </style>
</head>
<body>
    <h1>Smartchoice Event Manager - Reparação</h1>
    <p><strong>Equipamento:</strong> {{ $reparacao->equipamento->nome ?? 'N/A' }}</p>
    <p><strong>Avaria:</strong> {{ $reparacao->descricao_avaria }}</p>
    <p><strong>Estado:</strong> {{ $reparacao->estado }}</p>
    <p><strong>Técnico:</strong> {{ $reparacao->tecnico }}</p>
    <p><strong>Custo:</strong> {{ number_format($reparacao->custo_reparacao, 2) }}€</p>
    <p><strong>Data Entrada:</strong> {{ $reparacao->data_entrada }}</p>
    <p><strong>Data Saída:</strong> {{ $reparacao->data_saida }}</p>
</body>
</html>
