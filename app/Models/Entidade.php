<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Entidade extends Model
{
    protected $table = 'entidades';
    protected $fillable = [
        'nome', 'designacao_comercial', 'tipo_entidade', 'nif',
        'pais', 'email', 'telefone', 'morada', 'notas', 'ativo',
    ];
}
