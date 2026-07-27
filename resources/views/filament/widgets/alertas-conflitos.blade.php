<div>
@php
    $conflitos = $this->getConflitos();
@endphp

@if(count($conflitos) > 0)
<div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 16px; padding: 20px 24px; margin-bottom: 16px;">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
        <span style="font-size: 20px;">⚠️</span>
        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #991B1B;">Alertas de Conflito</h3>
        <span style="background: #DC2626; color: white; padding: 2px 10px; border-radius: 10px; font-size: 12px; font-weight: 600;">{{ count($conflitos) }}</span>
    </div>
    
    <div style="display: flex; flex-direction: column; gap: 8px;">
        @foreach($conflitos as $c)
        <div style="background: white; padding: 10px 14px; border-radius: 10px; border: 1px solid #FECACA; font-size: 12px;">
            <div style="color: #991B1B; font-weight: 600;">
                {{ $c['orcamento1'] }} ↔ {{ $c['orcamento2'] }}
            </div>
            <div style="color: #64748B; margin-top: 2px;">
                📅 {{ $c['periodo'] }} · 🔧 {{ $c['equipamentos'] }} equipamentos em comum
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
</div>
