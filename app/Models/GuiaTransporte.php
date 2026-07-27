<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GuiaTransporte extends Model
{
    protected $table = 'guia_transportes';
    protected $fillable = [
        'numero', 'orcamento_id', 'tipo', 'estado',
        'responsavel', 'observacoes',
    ];

    public function orcamento() { return $this->belongsTo(Orcamento::class); }
    public function itens() { return $this->hasMany(GuiaItem::class); }
}
