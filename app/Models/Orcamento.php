<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Observers\OrcamentoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([OrcamentoObserver::class])]
class Orcamento extends Model
{
    protected $fillable = [
        'numero', 'cliente_nome', 'cliente_email', 'cliente_telefone',
        'evento_nome', 'evento_local', 'data_inicio', 'data_fim',
        'estado', 'valor_total', 'notas',
    ];

    public function itens() { return $this->hasMany(OrcamentoItem::class); }
    public function guias() { return $this->hasMany(GuiaTransporte::class); }
}
