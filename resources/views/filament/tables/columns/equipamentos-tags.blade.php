<div style="display: flex; flex-wrap: wrap; gap: 4px; align-items: flex-start;">
    @php
        $equipamentos = $getRecord()->equipamentos()->take(5)->get();
    @endphp
    @foreach($equipamentos as $eq)
        @php
            $cor = match($eq->estado) {
                'disponivel' => '#22c55e',
                'alugado' => '#f59e0b',
                'manutencao' => '#ef4444',
                'reservado' => '#3b82f6',
                'abatido' => '#6b7280',
                default => '#6b7280'
            };
            $opacidade = $eq->estado === 'abatido' ? '0.5' : '1';
        @endphp
        <a href="/admin/equipamentos/{{ $eq->id }}/edit" 
           style="display: inline-flex; align-items: center; gap: 3px; background: {{ $cor }}15; border: 1px solid {{ $cor }}40; padding: 2px 8px; border-radius: 20px; font-size: 11px; text-decoration: none; color: #F1F5F9; white-space: nowrap; opacity: {{ $opacidade }}; {{ $eq->estado === 'abatido' ? 'text-decoration: line-through;' : '' }}">
            <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $cor }};"></span>
            {{ \Illuminate\Support\Str::limit($eq->nome, 25) }} <b>x{{ $eq->quantidade }}</b>
            @if($eq->estado === 'abatido') 🚫 @endif
        </a>
    @endforeach
    @if($getRecord()->equipamentos()->count() > 5)
        <span style="color: #3b82f6; font-size: 11px;">+{{ $getRecord()->equipamentos()->count() - 5 }}</span>
    @endif
    @if($equipamentos->count() === 0)
        <span style="color: #9ca3af; font-size: 12px;">—</span>
    @endif
</div>
