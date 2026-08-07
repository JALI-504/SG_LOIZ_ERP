<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivoFijo extends Model
{
    use HasFactory;

    protected $table = 'activos_fijos';

    protected $fillable = [
        'codigo',
        'categoria_activo_id',
        'nombre',
        'descripcion',
        'fecha_compra',
        'fecha_inicio_uso',
        'valor_compra',
        'valor_residual',
        'valor_depreciable',
        'vida_util_meses',
        'depreciacion_mensual',
        'depreciacion_acumulada',
        'valor_en_libros',
        'ubicacion',
        'responsable',
        'proveedor',
        'documento_compra',
        'numero_serie',
        'marca',
        'modelo',
        'estado',
        'fecha_baja',
        'motivo_baja',
        'observacion',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activo) {
            if (!$activo->codigo) {
                $categoria = CategoriaActivo::find($activo->categoria_activo_id);

                $prefijo = 'GEN';

                if ($categoria && $categoria->prefijo_codigo) {
                    $prefijo = strtoupper(trim($categoria->prefijo_codigo));
                }

                $ultimoCodigo = self::where('codigo', 'like', 'AF-' . $prefijo . '-%')
                    ->orderByDesc('id')
                    ->value('codigo');

                $siguienteNumero = 1;

                if ($ultimoCodigo) {
                    $partes = explode('-', $ultimoCodigo);
                    $ultimoNumero = end($partes);
                    $siguienteNumero = ((int) $ultimoNumero) + 1;
                }

                $activo->codigo = 'AF-' . $prefijo . '-' . str_pad($siguienteNumero, 6, '0', STR_PAD_LEFT);
            }

            self::calcularValoresDepreciacion($activo);
        });;

        static::updating(function ($activo) {
            self::calcularValoresDepreciacion($activo);
        });
    }

    public static function calcularValoresDepreciacion($activo)
    {
        $valorCompra = (float) ($activo->valor_compra ?? 0);
        $valorResidual = (float) ($activo->valor_residual ?? 0);
        $vidaUtilMeses = (int) ($activo->vida_util_meses ?? 0);

        if ($valorResidual < 0) {
            $valorResidual = 0;
        }

        if ($valorResidual > $valorCompra) {
            $valorResidual = $valorCompra;
        }

        $valorDepreciable = $valorCompra - $valorResidual;

        if ($vidaUtilMeses > 0) {
            $depreciacionMensual = $valorDepreciable / $vidaUtilMeses;
        } else {
            $depreciacionMensual = 0;
        }

        $depreciacionAcumulada = (float) ($activo->depreciacion_acumulada ?? 0);

        if ($depreciacionAcumulada > $valorDepreciable) {
            $depreciacionAcumulada = $valorDepreciable;
        }

        $valorEnLibros = $valorCompra - $depreciacionAcumulada;

        if ($valorEnLibros < $valorResidual) {
            $valorEnLibros = $valorResidual;
        }

        $activo->valor_residual = round($valorResidual, 2);
        $activo->valor_depreciable = round($valorDepreciable, 2);
        $activo->depreciacion_mensual = round($depreciacionMensual, 2);
        $activo->depreciacion_acumulada = round($depreciacionAcumulada, 2);
        $activo->valor_en_libros = round($valorEnLibros, 2);
    }

    public function categoriaActivo()
    {
        return $this->belongsTo(CategoriaActivo::class, 'categoria_activo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEstadoClaseAttribute()
    {
        if ($this->estado === 'Activo') {
            return 'success';
        }

        if ($this->estado === 'En mantenimiento') {
            return 'warning';
        }

        if ($this->estado === 'Dañado') {
            return 'danger';
        }

        if ($this->estado === 'Vendido') {
            return 'info';
        }

        if ($this->estado === 'Dado de baja') {
            return 'secondary';
        }

        return 'secondary';
    }
}
