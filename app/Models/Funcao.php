<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Funcao extends Model
{
    protected $table = 'funcoes';
    protected $fillable = ['nome', 'departamento_id', 'descricao', 'ativo'];

    public function departamento()
    {
        return $this->belongsTo(Categoria::class, 'departamento_id');
    }
}
