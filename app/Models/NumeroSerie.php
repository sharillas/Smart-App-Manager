<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NumeroSerie extends Model
{
    protected $table = 'numeros_serie';
    protected $fillable = ['equipamento_id', 'numero_serie', 'qr_code', 'estado'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->qr_code) {
                $model->qr_code = 'EQ-' . strtoupper(uniqid());
            }
        });
    }

    public function equipamento() { return $this->belongsTo(Equipamento::class); }
}
