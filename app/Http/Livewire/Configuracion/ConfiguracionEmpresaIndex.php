<?php

namespace App\Http\Livewire\Configuracion;

use App\Models\ConfiguracionEmpresa;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ConfiguracionEmpresaIndex extends Component
{
    use WithFileUploads;

    public $configuracion;

    public $configuracion_id;

    public $nombre_comercial;
    public $nombre_legal;
    public $rtn;

    public $telefono;
    public $whatsapp;
    public $correo;
    public $direccion;

    public $descripcion_negocio;
    public $logo;
    public $logoNuevo;

    public $usa_facturacion_fiscal = false;

    public $cai;
    public $rango_desde;
    public $rango_hasta;
    public $fecha_limite_emision;

    public $prefijo_recibo = 'REC';
    public $numero_actual_recibo = 0;

    public $mensaje_recibo;

    public $modo_fiscal = 'Interno';
    public $documento_venta_activo = 'Recibo interno';

    public $usa_impuestos = false;
    public $usa_retenciones = false;
    public $precios_incluyen_isv = true;
    public $porcentaje_isv_general = 15;

    public $establecimiento = '000';
    public $punto_emision = '001';
    public $tipo_documento_fiscal = '01';
    public $numero_actual_factura = 0;

    public $modosFiscales = [
        'Interno',
        'Fiscal',
    ];

    public $documentosVenta = [
        'Recibo interno',
        'Factura',
    ];
    

    public function mount()
    {
        $configuracion = ConfiguracionEmpresa::actual();

        $this->configuracion = $configuracion;

        $this->configuracion_id = $configuracion->id;

        $this->nombre_comercial = $configuracion->nombre_comercial;
        $this->nombre_legal = $configuracion->nombre_legal;
        $this->rtn = $configuracion->rtn;

        $this->telefono = $configuracion->telefono;
        $this->whatsapp = $configuracion->whatsapp;
        $this->correo = $configuracion->correo;
        $this->direccion = $configuracion->direccion;

        $this->descripcion_negocio = $configuracion->descripcion_negocio;
        $this->logo = $configuracion->logo;

        $this->usa_facturacion_fiscal = (bool) $configuracion->usa_facturacion_fiscal;

        $this->cai = $configuracion->cai;
        $this->rango_desde = $configuracion->rango_desde;
        $this->rango_hasta = $configuracion->rango_hasta;
        $this->fecha_limite_emision = $configuracion->fecha_limite_emision;

        $this->prefijo_recibo = $configuracion->prefijo_recibo ?: 'REC';
        $this->numero_actual_recibo = $configuracion->numero_actual_recibo ?? 0;

        $this->mensaje_recibo = $configuracion->mensaje_recibo;

        $this->modo_fiscal = $this->configuracion->modo_fiscal ?? 'Interno';
        $this->documento_venta_activo = $this->configuracion->documento_venta_activo ?? 'Recibo interno';

        $this->usa_impuestos = (bool) $this->configuracion->usa_impuestos;
        $this->usa_retenciones = (bool) $this->configuracion->usa_retenciones;
        $this->precios_incluyen_isv = (bool) $this->configuracion->precios_incluyen_isv;
        $this->porcentaje_isv_general = $this->configuracion->porcentaje_isv_general ?? 15;

        $this->establecimiento = $this->configuracion->establecimiento ?? '000';
        $this->punto_emision = $this->configuracion->punto_emision ?? '001';
        $this->tipo_documento_fiscal = $this->configuracion->tipo_documento_fiscal ?? '01';
        $this->numero_actual_factura = $this->configuracion->numero_actual_factura ?? 0;
    }

    protected function rules()
    {
        return [
            'nombre_comercial' => 'required|max:150',
            'nombre_legal' => 'nullable|max:150',
            'rtn' => 'nullable|max:30',

            'telefono' => 'nullable|max:30',
            'whatsapp' => 'nullable|max:30',
            'correo' => 'nullable|email|max:100',
            'direccion' => 'nullable|max:1000',

            'descripcion_negocio' => 'nullable|max:200',
            'logoNuevo' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',

            'usa_facturacion_fiscal' => 'boolean',

            'cai' => 'nullable|max:100',
            'rango_desde' => 'nullable|max:50',
            'rango_hasta' => 'nullable|max:50',
            'fecha_limite_emision' => 'nullable|date',

            'prefijo_recibo' => 'required|max:10',
            'numero_actual_recibo' => 'required|integer|min:0',

            'mensaje_recibo' => 'nullable|max:1000',

            'modo_fiscal' => 'required|max:30',
            'documento_venta_activo' => 'required|max:50',

            'usa_impuestos' => 'boolean',
            'usa_retenciones' => 'boolean',
            'precios_incluyen_isv' => 'boolean',
            'porcentaje_isv_general' => 'required|numeric|min:0|max:100',

            'establecimiento' => 'required|max:3',
            'punto_emision' => 'required|max:3',
            'tipo_documento_fiscal' => 'required|max:2',
            'numero_actual_factura' => 'required|numeric|min:0',
        ];
    }

    public function guardar()
    {
        $this->validate();

        $configuracion = ConfiguracionEmpresa::findOrFail($this->configuracion_id);

        $rutaLogo = $this->logo;

        if ($this->logoNuevo) {
            if ($configuracion->logo && Storage::disk('public')->exists($configuracion->logo)) {
                Storage::disk('public')->delete($configuracion->logo);
            }

            $rutaLogo = $this->logoNuevo->store('logos', 'public');
        }

        if ($this->modo_fiscal === 'Interno') {
            $this->usa_facturacion_fiscal = false;
            $this->documento_venta_activo = 'Recibo interno';
            $this->usa_retenciones = false;
        }

        if ($this->modo_fiscal === 'Fiscal') {
            $this->usa_facturacion_fiscal = true;
            $this->documento_venta_activo = 'Factura';
            $this->usa_impuestos = true;
        }

        $configuracion->update([
            'nombre_comercial' => $this->nombre_comercial,
            'nombre_legal' => $this->nombre_legal,
            'rtn' => $this->rtn,

            'telefono' => $this->telefono,
            'whatsapp' => $this->whatsapp,
            'correo' => $this->correo,
            'direccion' => $this->direccion,

            'descripcion_negocio' => $this->descripcion_negocio,
            'logo' => $rutaLogo,

            'usa_facturacion_fiscal' => $this->usa_facturacion_fiscal,

            'cai' => $this->cai,
            'rango_desde' => $this->rango_desde,
            'rango_hasta' => $this->rango_hasta,
            'fecha_limite_emision' => $this->fecha_limite_emision ?: null,

            'prefijo_recibo' => strtoupper($this->prefijo_recibo),
            'numero_actual_recibo' => $this->numero_actual_recibo,

            'mensaje_recibo' => $this->mensaje_recibo,
            'activo' => true,

            'modo_fiscal' => $this->modo_fiscal,
            'documento_venta_activo' => $this->documento_venta_activo,

            'usa_impuestos' => $this->usa_impuestos,
            'usa_retenciones' => $this->usa_retenciones,
            'precios_incluyen_isv' => $this->precios_incluyen_isv,
            'porcentaje_isv_general' => $this->porcentaje_isv_general,

            'establecimiento' => str_pad($this->establecimiento ?: '000', 3, '0', STR_PAD_LEFT),
            'punto_emision' => str_pad($this->punto_emision ?: '001', 3, '0', STR_PAD_LEFT),
            'tipo_documento_fiscal' => str_pad($this->tipo_documento_fiscal ?: '01', 2, '0', STR_PAD_LEFT),
            'numero_actual_factura' => $this->numero_actual_factura ?: 0,
        ]);

        $this->logo = $rutaLogo;
        $this->logoNuevo = null;
        $this->prefijo_recibo = strtoupper($this->prefijo_recibo);

        $this->configuracion = $configuracion->fresh();

        session()->flash('message', 'Configuración del negocio actualizada correctamente.');
    }

    public function eliminarLogo()
    {
        $configuracion = ConfiguracionEmpresa::findOrFail($this->configuracion_id);

        if ($configuracion->logo && Storage::disk('public')->exists($configuracion->logo)) {
            Storage::disk('public')->delete($configuracion->logo);
        }

        $configuracion->update([
            'logo' => null,
        ]);

        $this->logo = null;
        $this->logoNuevo = null;

        session()->flash('message', 'Logo eliminado correctamente.');
    }

    public function render()
    {
        return view('livewire.configuracion.configuracion-empresa-index');
    }

    public function updatedModoFiscal()
    {
        if ($this->modo_fiscal === 'Fiscal') {
            $this->usa_facturacion_fiscal = true;
            $this->documento_venta_activo = 'Factura';
            $this->usa_impuestos = true;
        } else {
            $this->usa_facturacion_fiscal = false;
            $this->documento_venta_activo = 'Recibo interno';
            $this->usa_retenciones = false;
        }
    }

}
