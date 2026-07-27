<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Reparacao extends Model
{
    protected $fillable = [
        'equipamento_id', 'descricao_avaria', 'estado',
        'tecnico', 'custo_reparacao', 'data_entrada',
        'data_saida', 'notas_tecnicas',
    ];
    public function equipamento()
    {
        return $this->belongsTo(Equipamento::class);
    }
}
