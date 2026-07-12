<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Insumo;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo_servicio',
        'tamano_papel',
        'color',
        'caras',
        'unidad_cobro',
        'costo_unitario',
        'precio_unitario',
        'descripcion',
        'activo',
    ];

    protected static function booted()
    {
        static::creating(function ($servicio) {
            if (empty($servicio->codigo)) {
                $servicio->codigo = self::generarCodigoServicio(
                    $servicio->tipo_servicio,
                    $servicio->tamano_papel,
                    $servicio->color
                );
            } else {
                $servicio->codigo = strtoupper(trim($servicio->codigo));
            }
        });

        static::updating(function ($servicio) {
            if (!empty($servicio->codigo)) {
                $servicio->codigo = strtoupper(trim($servicio->codigo));
            }
        });
    }

    public static function generarCodigoServicio($tipoServicio, $tamanoPapel, $color)
    {
        $prefijoTipo = self::prefijoTipoServicio($tipoServicio);
        $prefijoTamano = self::prefijoTamano($tamanoPapel);
        $prefijoColor = self::prefijoColor($color);

        $base = $prefijoTipo . '-' . $prefijoTamano . '-' . $prefijoColor;

        $ultimo = self::where('codigo', 'like', $base . '-%')
            ->orderByDesc('id')
            ->first();

        $numero = 1;

        if ($ultimo) {
            $partes = explode('-', $ultimo->codigo);
            $ultimoNumero = end($partes);

            if (is_numeric($ultimoNumero)) {
                $numero = (int) $ultimoNumero + 1;
            } else {
                $numero = $ultimo->id + 1;
            }
        }

        return $base . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public static function prefijoTipoServicio($tipoServicio)
    {
        $tipoServicio = strtolower(trim($tipoServicio));

        $mapa = [
            'impresion' => 'IMP',
            'impresión' => 'IMP',
            'fotocopia' => 'FOT',
            'copia' => 'FOT',
            'escaneo' => 'ESC',
            'scanner' => 'ESC',
            'laser' => 'LAS',
            'láser' => 'LAS',
            'grabado' => 'LAS',
            'corte' => 'LAS',
            'diseño' => 'DIS',
            'diseno' => 'DIS',
            'sublimacion' => 'SUB',
            'sublimación' => 'SUB',
            'encuadernado' => 'ENC',
            'plastificado' => 'PLA',
        ];

        foreach ($mapa as $clave => $prefijo) {
            if (strpos($tipoServicio, $clave) !== false) {
                return $prefijo;
            }
        }

        return 'SER';
    }

    public static function prefijoTamano($tamanoPapel)
    {
        $tamanoPapel = strtolower(trim($tamanoPapel));

        $mapa = [
            'carta' => 'CAR',
            'oficio' => 'OFI',
            'legal' => 'LEG',
            'a4' => 'A4',
            'a3' => 'A3',
            'media carta' => 'MCA',
            'personalizado' => 'PER',
            'no aplica' => 'NA',
            'ninguno' => 'NA',
        ];

        foreach ($mapa as $clave => $prefijo) {
            if (strpos($tamanoPapel, $clave) !== false) {
                return $prefijo;
            }
        }

        return 'PER';
    }

    public static function prefijoColor($color)
    {
        $color = strtolower(trim($color));

        $mapa = [
            'blanco y negro' => 'BN',
            'negro' => 'BN',
            'color' => 'COL',
            'full color' => 'COL',
            'escala de grises' => 'GRI',
            'no aplica' => 'NA',
            'ninguno' => 'NA',
            'sin color' => 'NA',
        ];

        foreach ($mapa as $clave => $prefijo) {
            if (strpos($color, $clave) !== false) {
                return $prefijo;
            }
        }

        return 'NA';
    }

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'servicio_insumos')
            ->withPivot('cantidad_por_unidad')
            ->withTimestamps();
    }

    public function getUtilidadUnitaraAttribute()
    {
        return $this->precio_unitario - $this->costo_unitario;
    }

    public function getMargenPorcentajeAttribute()
    {
        if ($this->costo_unitario <= 0) {
            return 0;
        }

        return (($this->precio_unitario - $this->costo_unitario) / $this->costo_unitario) * 100;
    }
}
