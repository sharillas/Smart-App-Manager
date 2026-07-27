<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Equipamento extends Model
{
    protected $fillable = [
        'nome', 'marca', 'modelo', 'numero_serie',
        'codigo_barras', 'categoria_id', 'estado',
        'quantidade', 'preco_aluguer_dia', 'preco_custo', 
        'notas', 'armazem',
    ];

    public function categoria() { return $this->belongsTo(Categoria::class); }
    public function numerosSerie() { return $this->hasMany(NumeroSerie::class); }
}
