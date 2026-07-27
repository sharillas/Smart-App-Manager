<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Colaborador extends Model
{
    protected $table = 'colaboradores';
    protected $fillable = [
        'nome', 'morada', 'bi_passaporte', 'funcao',
        'competencias', 'idade', 'epis', 'dados_adicionais',
    ];

    public function equipamentos()
    {
        return $this->belongsToMany(Equipamento::class, 'colaborador_equipamento')
            ->withPivot('quantidade', 'data_atribuicao', 'data_devolucao', 'notas', 'numero_serie_id', 'tipo')
            ->withTimestamps();
    }
}
