<?php

namespace App\Models;

use App\Models\CierreCaja;
use App\Models\BitacoraSistema;
use App\Models\RespaldoSistema;
use App\Models\AperturaCaja;
use App\Models\CuentaBancaria;
use App\Models\MovimientoBancario;
use App\Models\ConciliacionBancaria;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'activo' => 'boolean',
    ];

    public function cierresCaja()
    {
        return $this->hasMany(CierreCaja::class, 'user_id');
    }

    public function cierresCajaAnulados()
    {
        return $this->hasMany(CierreCaja::class, 'anulado_por');
    }

    public function bitacorasSistema()
    {
        return $this->hasMany(BitacoraSistema::class, 'user_id');
    }

    public function respaldosSistema()
    {
        return $this->hasMany(RespaldoSistema::class, 'user_id');
    }

    public function aperturasCaja()
    {
        return $this->hasMany(AperturaCaja::class, 'user_id');
    }

    public function aperturasCajaAnuladas()
    {
        return $this->hasMany(AperturaCaja::class, 'anulado_por');
    }

    public function cuentasBancarias()
    {
        return $this->hasMany(CuentaBancaria::class, 'user_id');
    }

    public function movimientosBancarios()
    {
        return $this->hasMany(MovimientoBancario::class, 'user_id');
    }

    public function movimientosBancariosAnulados()
    {
        return $this->hasMany(MovimientoBancario::class, 'anulado_por');
    }

    public function conciliacionesBancarias()
    {
        return $this->hasMany(ConciliacionBancaria::class, 'user_id');
    }

    public function conciliacionesBancariasAnuladas()
    {
        return $this->hasMany(ConciliacionBancaria::class, 'anulado_por');
    }
}