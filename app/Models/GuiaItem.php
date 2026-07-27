<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GuiaItem extends Model
{
    protected $table = 'guia_itens';
    protected $fillable = ['guia_transporte_id', 'equipamento_id', 'quantidade', 'estado_saida', 'estado_retorno', 'notas'];

    public function guiaTransporte() { return $this->belongsTo(GuiaTransporte::class); }
    public function equipamento() { return $this->belongsTo(Equipamento::class); }
}
