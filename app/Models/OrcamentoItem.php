<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrcamentoItem extends Model
{
    protected $table = 'orcamento_itens';
    protected $fillable = [
        'orcamento_id', 'equipamento_id', 'quantidade',
        'preco_unitario', 'dias', 'subtotal',
        'subaluguer', 'fornecedor', 'custo_subaluguer',
    ];

    protected $casts = [
        'subaluguer' => 'boolean',
    ];

    public function orcamento() { return $this->belongsTo(Orcamento::class); }
    public function equipamento() { return $this->belongsTo(Equipamento::class); }
}
