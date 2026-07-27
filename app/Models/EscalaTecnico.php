<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EscalaTecnico extends Model
{
    protected $table = 'escalas_tecnicos';
    protected $fillable = ['colaborador_id', 'orcamento_id', 'data_inicio', 'data_fim', 'hora_entrada', 'hora_saida', 'funcao', 'notas'];
    
    public function colaborador() { return $this->belongsTo(Colaborador::class); }
    public function orcamento() { return $this->belongsTo(Orcamento::class); }
}
