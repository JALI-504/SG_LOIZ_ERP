<?php

namespace App\Http\Livewire\OrdenesTrabajo;

use App\Models\Cliente;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoDetalle;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Catalogo;
use App\Models\ConfiguracionEmpresa;
use App\Models\Insumo;
use App\Models\LoteInsumo;
use App\Models\LoteProducto;
use App\Models\MovimientoInventario;
use App\Models\MovimientoInventarioLote;
use App\Models\MovimientoProducto;
use App\Models\MovimientoProductoLote;
use App\Models\PagoVenta;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\BitacoraSistema;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class OrdenTrabajoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $filtroEstado = 'todas';

    public $cliente_id;
    public $cliente_nombre;
    public $cliente_telefono;

    public $errorConversionVenta = null;

    public $fecha;
    public $fecha_entrega;
    public $titulo;
    public $descripcion;
    public $estado = 'Pendiente';
    public $prioridad = 'Normal';
    public $descuento = 0;
    public $abono = 0;
    public $observacion;

    public $subtotal = 0;
    public $total = 0;
    public $saldo = 0;

    public $detalles = [];

    public $detalle_tipo_item = 'Servicio';
    public $detalle_producto_id;
    public $detalle_servicio_id;
    public $detalle_descripcion;
    public $detalle_cantidad = 1;
    public $detalle_precio_unitario = 0;
    public $detalle_observacion;

    public $mostrarModalDetalle = false;
    public $ordenDetalle = null;

    public $mostrarModalAnulacion = false;
    public $ordenAnularId;
    public $motivoAnulacion;

    public $mostrarModalConvertirVenta = false;
    public $ordenConvertirId;
    public $metodoPagoConversion = 'Efectivo';
    public $referenciaPagoConversion;
    public $metodosPagoVenta = [];

    public $estados = [
        'Pendiente',
        'En diseño',
        'En producción',
        'Terminado',
        'Entregada',
    ];

    public $prioridades = [
        'Baja',
        'Normal',
        'Alta',
        'Urgente',
    ];

    protected $messages = [
        'fecha.required' => 'Debe seleccionar la fecha.',
        'fecha.date' => 'La fecha no es válida.',
        'fecha_entrega.date' => 'La fecha de entrega no es válida.',
        'titulo.required' => 'Debe ingresar el título del trabajo.',
        'titulo.min' => 'El título debe tener al menos 3 caracteres.',
        'titulo.max' => 'El título no debe superar los 150 caracteres.',
        'cliente_nombre.max' => 'El nombre del cliente no debe superar los 150 caracteres.',
        'cliente_telefono.max' => 'El teléfono no debe superar los 30 caracteres.',
        'descripcion.max' => 'La descripción no debe superar los 1000 caracteres.',
        'prioridad.required' => 'Debe seleccionar la prioridad.',
        'descuento.numeric' => 'El descuento debe ser numérico.',
        'descuento.min' => 'El descuento no puede ser negativo.',
        'abono.numeric' => 'El abono debe ser numérico.',
        'abono.min' => 'El abono no puede ser negativo.',
        'observacion.max' => 'La observación no debe superar los 500 caracteres.',
        'detalle_descripcion.required' => 'Debe ingresar la descripción del detalle.',
        'detalle_cantidad.required' => 'Debe ingresar la cantidad.',
        'detalle_cantidad.numeric' => 'La cantidad debe ser numérica.',
        'detalle_cantidad.min' => 'La cantidad debe ser mayor que cero.',
        'detalle_precio_unitario.required' => 'Debe ingresar el precio unitario.',
        'detalle_precio_unitario.numeric' => 'El precio unitario debe ser numérico.',
        'detalle_precio_unitario.min' => 'El precio unitario no puede ser negativo.',
        'motivoAnulacion.required' => 'Debe ingresar el motivo de anulación.',
        'motivoAnulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
    ];

    public function mount()
    {
        if (!auth()->user()->can('ver ordenes trabajo')) {
            abort(403, 'No tiene permiso para ver órdenes de trabajo.');
        }

        $this->fecha = now()->format('Y-m-d');

        $this->metodosPagoVenta = Catalogo::opciones('metodo_pago')->pluck('nombre')->toArray();
        $this->metodoPagoConversion = $this->metodosPagoVenta[0] ?? 'Efectivo';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatedClienteId()
    {
        if (!$this->cliente_id) {
            $this->cliente_nombre = '';
            $this->cliente_telefono = '';
            return;
        }

        $cliente = Cliente::find($this->cliente_id);

        if ($cliente) {
            $this->cliente_nombre = $cliente->nombre_completo ?: 'Cliente #' . $cliente->id;
            $this->cliente_telefono = $cliente->telefono ?? '';
        }
    }

    public function updatedDetalleTipoItem()
    {
        $this->detalle_producto_id = null;
        $this->detalle_servicio_id = null;
        $this->detalle_descripcion = '';
        $this->detalle_precio_unitario = 0;
    }

    public function updatedDetalleProductoId()
    {
        if (!$this->detalle_producto_id) {
            return;
        }

        $producto = Producto::find($this->detalle_producto_id);

        if ($producto) {
            $this->detalle_descripcion = $producto->nombre;
            $this->detalle_precio_unitario = $producto->precio_venta;
        }
    }

    public function updatedDetalleServicioId()
    {
        if (!$this->detalle_servicio_id) {
            return;
        }

        $servicio = Servicio::find($this->detalle_servicio_id);

        if ($servicio) {
            $this->detalle_descripcion = $servicio->nombre;
            $this->detalle_precio_unitario = $servicio->precio_unitario;
        }
    }

    public function updatedDescuento()
    {
        $this->calcularTotales();
    }

    public function updatedAbono()
    {
        $this->calcularTotales();
    }

    public function agregarDetalle()
    {
        $this->validate([
            'detalle_tipo_item' => 'required|in:Producto,Servicio,Otro',
            'detalle_descripcion' => 'required|max:200',
            'detalle_cantidad' => 'required|numeric|min:0.01',
            'detalle_precio_unitario' => 'required|numeric|min:0',
            'detalle_observacion' => 'nullable|max:500',
        ]);

        if ($this->detalle_tipo_item === 'Producto' && !$this->detalle_producto_id) {
            $this->addError('detalle_producto_id', 'Debe seleccionar un producto.');
            return;
        }

        if ($this->detalle_tipo_item === 'Servicio' && !$this->detalle_servicio_id) {
            $this->addError('detalle_servicio_id', 'Debe seleccionar un servicio.');
            return;
        }

        $cantidad = (float) $this->detalle_cantidad;
        $precioUnitario = (float) $this->detalle_precio_unitario;
        $subtotal = round($cantidad * $precioUnitario, 2);

        $this->detalles[] = [
            'tipo_item' => $this->detalle_tipo_item,
            'producto_id' => $this->detalle_tipo_item === 'Producto' ? $this->detalle_producto_id : null,
            'servicio_id' => $this->detalle_tipo_item === 'Servicio' ? $this->detalle_servicio_id : null,
            'descripcion' => trim($this->detalle_descripcion),
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal' => $subtotal,
            'observacion' => $this->detalle_observacion,
        ];

        $this->resetDetalle();
        $this->calcularTotales();
    }

    public function eliminarDetalle($index)
    {
        if (isset($this->detalles[$index])) {
            unset($this->detalles[$index]);
            $this->detalles = array_values($this->detalles);
        }

        $this->calcularTotales();
    }

    private function calcularTotales()
    {
        $this->subtotal = 0;

        foreach ($this->detalles as $detalle) {
            $this->subtotal += (float) $detalle['subtotal'];
        }

        $descuento = (float) $this->descuento;
        $abono = (float) $this->abono;

        $this->total = round(max($this->subtotal - $descuento, 0), 2);
        $this->saldo = round(max($this->total - $abono, 0), 2);
    }

    public function registrarOrden()
    {
        if (!auth()->user()->can('crear ordenes trabajo')) {
            abort(403, 'No tiene permiso para crear órdenes de trabajo.');
        }

        $this->validate([
            'fecha' => 'required|date',
            'fecha_entrega' => 'nullable|date',
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_nombre' => 'nullable|max:150',
            'cliente_telefono' => 'nullable|max:30',
            'titulo' => 'required|min:3|max:150',
            'descripcion' => 'nullable|max:1000',
            'prioridad' => 'required|in:Baja,Normal,Alta,Urgente',
            'descuento' => 'required|numeric|min:0',
            'abono' => 'required|numeric|min:0',
            'observacion' => 'nullable|max:500',
        ]);

        if (count($this->detalles) === 0) {
            session()->flash('error', 'Debe agregar al menos un detalle a la orden de trabajo.');
            return;
        }

        $this->calcularTotales();

        if ((float) $this->descuento > (float) $this->subtotal) {
            session()->flash('error', 'El descuento no puede ser mayor al subtotal.');
            return;
        }

        if ((float) $this->abono > (float) $this->total) {
            session()->flash('error', 'El abono no puede ser mayor al total.');
            return;
        }

        DB::transaction(function () {
            $orden = OrdenTrabajo::create([
                'fecha' => $this->fecha,
                'fecha_entrega' => $this->fecha_entrega,
                'cliente_id' => $this->cliente_id,
                'cliente_nombre' => $this->cliente_nombre,
                'cliente_telefono' => $this->cliente_telefono,
                'titulo' => trim($this->titulo),
                'descripcion' => $this->descripcion,
                'estado' => 'Pendiente',
                'prioridad' => $this->prioridad,
                'subtotal' => $this->subtotal,
                'descuento' => $this->descuento,
                'total' => $this->total,
                'abono' => $this->abono,
                'saldo' => $this->saldo,
                'observacion' => $this->observacion,
                'user_id' => auth()->id(),
            ]);

            foreach ($this->detalles as $detalle) {
                OrdenTrabajoDetalle::create([
                    'orden_trabajo_id' => $orden->id,
                    'tipo_item' => $detalle['tipo_item'],
                    'producto_id' => $detalle['producto_id'],
                    'servicio_id' => $detalle['servicio_id'],
                    'descripcion' => $detalle['descripcion'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal'],
                    'observacion' => $detalle['observacion'],
                ]);
            }

            BitacoraSistema::registrar(
                'Órdenes de trabajo',
                'Registrar',
                'Registró la orden de trabajo ' . $orden->codigo . ' por L ' . number_format($orden->total, 2) . '.',
                OrdenTrabajo::class,
                $orden->id,
                null,
                $orden->fresh()->load('detalles')->toArray()
            );
        });

        $this->resetFormulario();

        session()->flash('message', 'Orden de trabajo registrada correctamente.');
    }

    public function cambiarEstado($id, $nuevoEstado)
    {
        if (!auth()->user()->can('cambiar estado ordenes trabajo')) {
            abort(403, 'No tiene permiso para cambiar estado de órdenes de trabajo.');
        }

        if (!in_array($nuevoEstado, $this->estados)) {
            session()->flash('error', 'El estado seleccionado no es válido.');
            return;
        }

        $orden = OrdenTrabajo::findOrFail($id);

        if ($orden->estado === 'Anulada') {
            session()->flash('error', 'No puede cambiar el estado de una orden anulada.');
            return;
        }

        $datosAnteriores = $orden->toArray();
        $estadoAnterior = $orden->estado;

        $orden->update([
            'estado' => $nuevoEstado,
        ]);

        BitacoraSistema::registrar(
            'Órdenes de trabajo',
            'Actualizar',
            'Cambió el estado de la orden ' . $orden->codigo . ' de ' . $estadoAnterior . ' a ' . $orden->fresh()->estado . '.',
            OrdenTrabajo::class,
            $orden->id,
            $datosAnteriores,
            $orden->fresh()->toArray()
        );

        session()->flash('message', 'Estado de la orden actualizado correctamente.');
    }

    public function verDetalle($id)
    {
        if (!auth()->user()->can('ver ordenes trabajo')) {
            abort(403, 'No tiene permiso para ver órdenes de trabajo.');
        }

        $this->ordenDetalle = OrdenTrabajo::with([
            'cliente',
            'detalles.producto',
            'detalles.servicio',
            'usuario',
            'usuarioAnulacion',
        ])->findOrFail($id);

        $this->mostrarModalDetalle = true;
    }

    public function cerrarModalDetalle()
    {
        $this->mostrarModalDetalle = false;
        $this->ordenDetalle = null;
    }

    public function abrirAnular($id)
    {
        if (!auth()->user()->can('anular ordenes trabajo')) {
            abort(403, 'No tiene permiso para anular órdenes de trabajo.');
        }

        $orden = OrdenTrabajo::findOrFail($id);

        if ($orden->estado === 'Anulada') {
            session()->flash('error', 'Esta orden ya está anulada.');
            return;
        }

        $this->ordenAnularId = $orden->id;
        $this->motivoAnulacion = '';
        $this->mostrarModalAnulacion = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function cerrarModalAnulacion()
    {
        $this->mostrarModalAnulacion = false;
        $this->ordenAnularId = null;
        $this->motivoAnulacion = '';

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function confirmarAnular()
    {
        if (!auth()->user()->can('anular ordenes trabajo')) {
            abort(403, 'No tiene permiso para anular órdenes de trabajo.');
        }

        $this->validate([
            'ordenAnularId' => 'required|exists:ordenes_trabajo,id',
            'motivoAnulacion' => 'required|min:5|max:500',
        ]);

        $orden = OrdenTrabajo::findOrFail($this->ordenAnularId);

        if ($orden->estado === 'Anulada') {
            session()->flash('error', 'Esta orden ya está anulada.');
            return;
        }

        $datosAnteriores = $orden->toArray();

        $orden->update([
            'estado' => 'Anulada',
            'fecha_anulacion' => now(),
            'anulado_por' => auth()->id(),
            'motivo_anulacion' => $this->motivoAnulacion,
        ]);

        BitacoraSistema::registrar(
            'Órdenes de trabajo',
            'Anular',
            'Anuló la orden de trabajo ' . $orden->codigo . '. Motivo: ' . $this->motivoAnulacion,
            OrdenTrabajo::class,
            $orden->id,
            $datosAnteriores,
            $orden->fresh()->toArray()
        );

        $this->cerrarModalAnulacion();

        session()->flash('message', 'Orden de trabajo anulada correctamente.');
    }

    public function abrirConvertirVenta($id)
    {
        if (!auth()->user()->can('crear ventas')) {
            abort(403, 'No tiene permiso para crear ventas.');
        }

        $orden = OrdenTrabajo::findOrFail($id);

        if ($orden->estado === 'Anulada') {
            session()->flash('error', 'No se puede convertir una orden anulada.');
            return;
        }

        if ($orden->venta_id) {
            session()->flash('error', 'Esta orden ya fue convertida a venta.');
            return;
        }

        $this->ordenConvertirId = $orden->id;
        $this->metodoPagoConversion = $this->metodosPagoVenta[0] ?? 'Efectivo';
        $this->referenciaPagoConversion = 'Orden ' . $orden->codigo;
        $this->mostrarModalConvertirVenta = true;
        $this->errorConversionVenta = null;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function cerrarModalConvertirVenta()
    {
        $this->mostrarModalConvertirVenta = false;
        $this->ordenConvertirId = null;
        $this->metodoPagoConversion = $this->metodosPagoVenta[0] ?? 'Efectivo';
        $this->referenciaPagoConversion = null;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function confirmarConvertirVenta()
    {
        if (!auth()->user()->can('crear ventas')) {
            abort(403, 'No tiene permiso para crear ventas.');
        }

        $this->validate([
            'ordenConvertirId' => 'required|exists:ordenes_trabajo,id',
            'metodoPagoConversion' => 'required|max:50',
            'referenciaPagoConversion' => 'nullable|max:100',
        ]);

        try {
            DB::transaction(function () {
                $orden = OrdenTrabajo::with(['detalles.producto', 'detalles.servicio'])
                    ->lockForUpdate()
                    ->findOrFail($this->ordenConvertirId);

                $datosAnterioresOrden = $orden->toArray();

                if ($orden->estado === 'Anulada') {
                    throw new \Exception('No se puede convertir una orden anulada.');
                }

                if ($orden->venta_id) {
                    throw new \Exception('Esta orden ya fue convertida a venta.');
                }

                if ($orden->detalles->count() === 0) {
                    throw new \Exception('La orden no tiene detalles para convertir a venta.');
                }

                $configuracion = ConfiguracionEmpresa::actual();

                $datosVenta = $this->prepararDetallesVentaDesdeOrden($orden, $configuracion);

                $this->validarDisponibilidadVentaOrden($datosVenta['detalles']);

                $clienteIdVenta = $this->crearClienteDesdeOrdenManual($orden);

                $totalVenta = (float) $datosVenta['total'];
                $abonoOrden = (float) $orden->abono;

                if ($abonoOrden < 0) {
                    $abonoOrden = 0;
                }

                if ($abonoOrden > $totalVenta) {
                    $abonoOrden = $totalVenta;
                }

                $saldoPendiente = round($totalVenta - $abonoOrden, 2);
                $estadoVenta = $saldoPendiente <= 0 ? 'Pagada' : 'Pendiente';

                $venta = Venta::create([
                    'cliente_id' => $clienteIdVenta ?: null,
                    'metodo_pago' => $this->metodoPagoConversion,
                    'estado' => $estadoVenta,

                    'subtotal' => $datosVenta['subtotal'],
                    'descuento' => $datosVenta['descuento'],

                    'subtotal_gravado' => $datosVenta['subtotal_gravado'],
                    'subtotal_exento' => $datosVenta['subtotal_exento'],
                    'subtotal_no_sujeto' => $datosVenta['subtotal_no_sujeto'],

                    'impuesto' => $datosVenta['impuesto'],
                    'isv_15' => $datosVenta['isv_15'],

                    'total' => $totalVenta,
                    'retencion' => 0,
                    'neto_recibido' => $totalVenta,

                    'monto_pagado' => $abonoOrden,
                    'saldo_pendiente' => $saldoPendiente,
                    'observacion' => 'Venta generada desde orden de trabajo ' . $orden->codigo . '. ' . ($orden->observacion ?? ''),
                ]);

                if ($abonoOrden > 0) {
                    PagoVenta::create([
                        'venta_id' => $venta->id,
                        'monto' => $abonoOrden,
                        'metodo_pago' => $this->metodoPagoConversion,
                        'referencia' => $this->referenciaPagoConversion,
                        'observacion' => 'Abono trasladado desde orden de trabajo ' . $orden->codigo . '.',
                        'estado' => 'Activo',
                    ]);
                }

                foreach ($datosVenta['detalles'] as $item) {
                    $costoUnitarioReal = 0;

                    if ($item['tipo_item'] === 'Producto' && $item['item_id']) {
                        $producto = Producto::findOrFail($item['item_id']);
                        $costoUnitarioReal = $this->procesarProductoVentaDesdeOrden($producto, $item['cantidad'], $venta);
                    }

                    if ($item['tipo_item'] === 'Servicio' && $item['item_id']) {
                        $servicio = Servicio::with('insumos')->findOrFail($item['item_id']);
                        $costoUnitarioReal = $this->procesarServicioVentaDesdeOrden($servicio, $item['cantidad'], $venta);
                    }

                    VentaDetalle::create([
                        'venta_id' => $venta->id,
                        'tipo_item' => $item['tipo_item'],
                        'item_id' => $item['item_id'],
                        'codigo' => $item['codigo'],
                        'descripcion' => $item['descripcion'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'],
                        'costo_unitario' => $costoUnitarioReal,

                        'tipo_impuesto' => $item['tipo_impuesto'],
                        'porcentaje_isv' => $item['porcentaje_isv'],

                        'descuento' => $item['descuento'],

                        'subtotal_gravado' => $item['subtotal_gravado'],
                        'subtotal_exento' => $item['subtotal_exento'],
                        'subtotal_no_sujeto' => $item['subtotal_no_sujeto'],
                        'impuesto' => $item['impuesto'],

                        'subtotal' => $item['subtotal'],
                        'total' => $item['total'],
                    ]);
                    
                }

                $orden->update([
                    'venta_id' => $venta->id,
                    'estado' => 'Entregada',
                ]);

                BitacoraSistema::registrar(
                    'Órdenes de trabajo',
                    'Convertir',
                    'Convirtió la orden de trabajo ' . $orden->codigo . ' en la venta ' . $venta->numero . '.',
                    OrdenTrabajo::class,
                    $orden->id,
                    $datosAnterioresOrden,
                    $orden->fresh()->load('detalles')->toArray()
                );

                BitacoraSistema::registrar(
                    'Ventas',
                    'Registrar',
                    'Registró la venta ' . $venta->numero . ' desde la orden de trabajo ' . $orden->codigo . '.',
                    Venta::class,
                    $venta->id,
                    null,
                    $venta->fresh()->load('detalles')->toArray()
                );

            });
        } catch (\Exception $e) {
            $this->errorConversionVenta = $e->getMessage();
            session()->flash('error', $e->getMessage());
            return;
        }

        $this->cerrarModalConvertirVenta();

        session()->flash('message', 'Orden convertida a venta correctamente.');
    }

    private function prepararDetallesVentaDesdeOrden($orden, $configuracion)
    {
        $usaImpuestos = (bool) $configuracion->usa_impuestos;
        $preciosIncluyenIsv = (bool) $configuracion->precios_incluyen_isv;

        $subtotalBruto = 0;

        foreach ($orden->detalles as $detalle) {
            $subtotalBruto += (float) $detalle->cantidad * (float) $detalle->precio_unitario;
        }

        $descuentoGeneral = (float) $orden->descuento;

        if ($descuentoGeneral < 0) {
            $descuentoGeneral = 0;
        }

        if ($descuentoGeneral > $subtotalBruto) {
            $descuentoGeneral = $subtotalBruto;
        }

        $detallesVenta = [];

        $subtotalGravado = 0;
        $subtotalExento = 0;
        $subtotalNoSujeto = 0;
        $isv15 = 0;
        $totalVenta = 0;
        $descuentoAsignado = 0;

        $totalDetalles = $orden->detalles->count();
        $contador = 0;

        foreach ($orden->detalles as $detalle) {
            $contador++;

            $cantidad = (float) $detalle->cantidad;
            $precioUnitario = (float) $detalle->precio_unitario;
            $subtotalItem = round($cantidad * $precioUnitario, 2);

            if ($subtotalBruto > 0) {
                $descuentoLinea = round($descuentoGeneral * ($subtotalItem / $subtotalBruto), 2);
            } else {
                $descuentoLinea = 0;
            }

            if ($contador === $totalDetalles) {
                $descuentoLinea = round($descuentoGeneral - $descuentoAsignado, 2);
            }

            $descuentoAsignado += $descuentoLinea;

            $totalItem = round($subtotalItem - $descuentoLinea, 2);

            if ($totalItem < 0) {
                $totalItem = 0;
            }

            $codigo = 'OTRO';
            $itemId = null;
            $tipoImpuesto = 'Gravado 15%';
            $porcentajeIsv = 15;

            if ($detalle->tipo_item === 'Producto' && $detalle->producto) {
                $codigo = $detalle->producto->codigo;
                $itemId = $detalle->producto->id;
                $tipoImpuesto = $detalle->producto->tipo_impuesto ?? 'Gravado 15%';
                $porcentajeIsv = (float) ($detalle->producto->porcentaje_isv ?? 15);
            }

            if ($detalle->tipo_item === 'Servicio' && $detalle->servicio) {
                $codigo = $detalle->servicio->codigo;
                $itemId = $detalle->servicio->id;
                $tipoImpuesto = $detalle->servicio->tipo_impuesto ?? 'Gravado 15%';
                $porcentajeIsv = (float) ($detalle->servicio->porcentaje_isv ?? 15);
            }

            $subtotalGravadoItem = 0;
            $subtotalExentoItem = 0;
            $subtotalNoSujetoItem = 0;
            $impuestoItem = 0;

            if (!$usaImpuestos) {
                $tipoImpuesto = 'No aplica';
                $porcentajeIsv = 0;
                $impuestoItem = 0;
            } else {
                if ($tipoImpuesto === 'Gravado 15%' && $porcentajeIsv > 0) {
                    $factor = 1 + ($porcentajeIsv / 100);

                    if ($preciosIncluyenIsv) {
                        $subtotalGravadoItem = round($totalItem / $factor, 2);
                        $impuestoItem = round($totalItem - $subtotalGravadoItem, 2);
                    } else {
                        $subtotalGravadoItem = round($totalItem, 2);
                        $impuestoItem = round($subtotalGravadoItem * ($porcentajeIsv / 100), 2);
                        $totalItem = round($subtotalGravadoItem + $impuestoItem, 2);
                    }
                } elseif ($tipoImpuesto === 'Exento') {
                    $subtotalExentoItem = round($totalItem, 2);
                } else {
                    $subtotalNoSujetoItem = round($totalItem, 2);
                }
            }

            $detallesVenta[] = [
                'tipo_item' => $detalle->tipo_item,
                'item_id' => $itemId,
                'codigo' => $codigo,
                'descripcion' => $detalle->descripcion,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'descuento' => $descuentoLinea,
                'subtotal' => $subtotalItem,
                'total' => $totalItem,

                'tipo_impuesto' => $tipoImpuesto,
                'porcentaje_isv' => $porcentajeIsv,
                'subtotal_gravado' => $subtotalGravadoItem,
                'subtotal_exento' => $subtotalExentoItem,
                'subtotal_no_sujeto' => $subtotalNoSujetoItem,
                'impuesto' => $impuestoItem,
            ];

            $subtotalGravado += $subtotalGravadoItem;
            $subtotalExento += $subtotalExentoItem;
            $subtotalNoSujeto += $subtotalNoSujetoItem;
            $isv15 += $impuestoItem;
            $totalVenta += $totalItem;
        }

        return [
            'detalles' => $detallesVenta,
            'subtotal' => round($subtotalBruto, 2),
            'descuento' => round($descuentoGeneral, 2),
            'subtotal_gravado' => round($subtotalGravado, 2),
            'subtotal_exento' => round($subtotalExento, 2),
            'subtotal_no_sujeto' => round($subtotalNoSujeto, 2),
            'impuesto' => round($isv15, 2),
            'isv_15' => round($isv15, 2),
            'total' => round($totalVenta, 2),
        ];
    }

    private function validarDisponibilidadVentaOrden($detallesVenta)
    {
        foreach ($detallesVenta as $item) {
            if ($item['tipo_item'] === 'Producto' && $item['item_id']) {
                $producto = Producto::findOrFail($item['item_id']);

                if ($producto->maneja_inventario && $producto->stock_actual < $item['cantidad']) {
                    throw new \Exception('Stock insuficiente para el producto: ' . $producto->nombre);
                }
            }

            if ($item['tipo_item'] === 'Servicio' && $item['item_id']) {
                $servicio = Servicio::with('insumos')->findOrFail($item['item_id']);

                foreach ($servicio->insumos as $insumo) {
                    $cantidadNecesaria = (float) $insumo->pivot->cantidad_por_unidad * (float) $item['cantidad'];

                    if ($insumo->stock_actual < $cantidadNecesaria) {
                        throw new \Exception(
                            'Stock insuficiente del insumo "' . $insumo->nombre .
                                '" para vender el servicio "' . $servicio->nombre . '".'
                        );
                    }
                }
            }
        }
    }

    private function procesarProductoVentaDesdeOrden(Producto $producto, $cantidad, Venta $venta)
    {
        if (!$producto->maneja_inventario) {
            return (float) $producto->costo_unitario;
        }

        $movimiento = MovimientoProducto::create([
            'producto_id' => $producto->id,
            'tipo_movimiento' => 'Salida venta',
            'cantidad' => $cantidad,
            'costo_unitario' => 0,
            'total' => 0,
            'referencia' => $venta->numero,
            'observacion' => 'Salida automática por venta generada desde orden de trabajo.',
        ]);

        $cantidadPendiente = (float) $cantidad;
        $totalCostoSalida = 0;

        $lotes = LoteProducto::where('producto_id', $producto->id)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($lotes as $lote) {
            if ($cantidadPendiente <= 0) {
                break;
            }

            $cantidadTomada = min($cantidadPendiente, (float) $lote->cantidad_disponible);
            $totalDetalle = round($cantidadTomada * (float) $lote->costo_unitario, 2);

            MovimientoProductoLote::create([
                'movimiento_producto_id' => $movimiento->id,
                'lote_producto_id' => $lote->id,
                'cantidad' => $cantidadTomada,
                'costo_unitario' => $lote->costo_unitario,
                'total' => $totalDetalle,
            ]);

            $nuevaCantidad = round((float) $lote->cantidad_disponible - $cantidadTomada, 4);

            $lote->update([
                'cantidad_disponible' => $nuevaCantidad,
                'activo' => $nuevaCantidad > 0,
            ]);

            $cantidadPendiente = round($cantidadPendiente - $cantidadTomada, 4);
            $totalCostoSalida += $totalDetalle;
        }

        if ($cantidadPendiente > 0) {
            throw new \Exception('No hay lotes suficientes para el producto: ' . $producto->nombre);
        }

        $costoUnitario = $cantidad > 0 ? round($totalCostoSalida / $cantidad, 4) : 0;

        $movimiento->update([
            'costo_unitario' => $costoUnitario,
            'total' => round($totalCostoSalida, 2),
        ]);

        $this->actualizarCostoActualPepsProductoDesdeOrden($producto);

        return $costoUnitario;
    }

    private function procesarServicioVentaDesdeOrden(Servicio $servicio, $cantidad, Venta $venta)
    {
        if ($servicio->insumos->count() === 0) {
            return (float) $servicio->costo_unitario;
        }

        $totalCostoServicio = 0;

        foreach ($servicio->insumos as $insumo) {
            $cantidadNecesaria = (float) $insumo->pivot->cantidad_por_unidad * (float) $cantidad;

            $totalCostoServicio += $this->descontarInsumoPorServicioDesdeOrden(
                $insumo,
                $cantidadNecesaria,
                $venta,
                $servicio
            );
        }

        return $cantidad > 0 ? round($totalCostoServicio / $cantidad, 4) : 0;
    }

    private function descontarInsumoPorServicioDesdeOrden(Insumo $insumo, $cantidad, Venta $venta, Servicio $servicio)
    {
        $movimiento = MovimientoInventario::create([
            'insumo_id' => $insumo->id,
            'tipo_movimiento' => 'Salida venta',
            'cantidad' => $cantidad,
            'costo_unitario' => 0,
            'total' => 0,
            'referencia' => $venta->numero,
            'observacion' => 'Salida automática por venta del servicio desde orden de trabajo: ' . $servicio->nombre,
        ]);

        $cantidadPendiente = (float) $cantidad;
        $totalCostoSalida = 0;

        $lotes = LoteInsumo::where('insumo_id', $insumo->id)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($lotes as $lote) {
            if ($cantidadPendiente <= 0) {
                break;
            }

            $cantidadTomada = min($cantidadPendiente, (float) $lote->cantidad_disponible);
            $totalDetalle = round($cantidadTomada * (float) $lote->costo_unitario, 2);

            MovimientoInventarioLote::create([
                'movimiento_inventario_id' => $movimiento->id,
                'lote_insumo_id' => $lote->id,
                'cantidad' => $cantidadTomada,
                'costo_unitario' => $lote->costo_unitario,
                'total' => $totalDetalle,
            ]);

            $nuevaCantidad = round((float) $lote->cantidad_disponible - $cantidadTomada, 4);

            $lote->update([
                'cantidad_disponible' => $nuevaCantidad,
                'activo' => $nuevaCantidad > 0,
            ]);

            $cantidadPendiente = round($cantidadPendiente - $cantidadTomada, 4);
            $totalCostoSalida += $totalDetalle;
        }

        if ($cantidadPendiente > 0) {
            throw new \Exception('No hay lotes suficientes para el insumo: ' . $insumo->nombre);
        }

        $costoUnitario = $cantidad > 0 ? round($totalCostoSalida / $cantidad, 4) : 0;

        $movimiento->update([
            'costo_unitario' => $costoUnitario,
            'total' => round($totalCostoSalida, 2),
        ]);

        $this->actualizarCostoActualPepsInsumoDesdeOrden($insumo);

        return round($totalCostoSalida, 2);
    }

    private function actualizarCostoActualPepsProductoDesdeOrden(Producto $producto)
    {
        $stockActual = LoteProducto::where('producto_id', $producto->id)
            ->where('activo', true)
            ->sum('cantidad_disponible');

        $proximoLote = LoteProducto::where('producto_id', $producto->id)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->first();

        $producto->stock_actual = round($stockActual, 2);

        if ($proximoLote) {
            $producto->costo_unitario = round($proximoLote->costo_unitario, 4);
        }

        $producto->save();
    }

    private function actualizarCostoActualPepsInsumoDesdeOrden(Insumo $insumo)
    {
        $stockActual = LoteInsumo::where('insumo_id', $insumo->id)
            ->where('activo', true)
            ->sum('cantidad_disponible');

        $proximoLote = LoteInsumo::where('insumo_id', $insumo->id)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->first();

        $insumo->stock_actual = round($stockActual, 2);

        if ($proximoLote) {
            $insumo->costo_unitario_base = round($proximoLote->costo_unitario, 4);
            $insumo->costo_unitario_real = round($proximoLote->costo_unitario, 4);
        }

        $insumo->save();
    }

    private function crearClienteDesdeOrdenManual(OrdenTrabajo $orden)
    {
        if ($orden->cliente_id) {
            return $orden->cliente_id;
        }

        $nombreCompleto = trim((string) $orden->cliente_nombre);

        if ($nombreCompleto === '') {
            return null;
        }

        $telefono = preg_replace('/\D/', '', (string) $orden->cliente_telefono);

        /*
    |--------------------------------------------------------------------------
    | Teléfono obligatorio
    |--------------------------------------------------------------------------
    | En tu tabla clientes, telefono no permite NULL ni valor por defecto.
    | Si la orden manual no tiene teléfono, asignamos un número interno único.
    */
        if ($telefono === '') {
            $telefono = '99' . str_pad($orden->id, 6, '0', STR_PAD_LEFT);
        }

        $clienteExistente = Cliente::where('telefono', $telefono)->first();

        if ($clienteExistente) {
            $orden->update([
                'cliente_id' => $clienteExistente->id,
            ]);

            return $clienteExistente->id;
        }

        $departamento = Departamento::orderBy('id')->first();

        if (!$departamento) {
            throw new \Exception('No se puede crear el cliente automático porque no hay departamentos registrados.');
        }

        $municipio = Municipio::where('departamento_id', $departamento->id)
            ->orderBy('id')
            ->first();

        if (!$municipio) {
            $municipio = Municipio::orderBy('id')->first();
        }

        if (!$municipio) {
            throw new \Exception('No se puede crear el cliente automático porque no hay municipios registrados.');
        }

        $partes = preg_split('/\s+/', $nombreCompleto);

        $primerNombre = $partes[0] ?? 'Cliente';
        $segundoNombre = null;
        $primerApellido = 'No definido';
        $segundoApellido = null;

        if (count($partes) === 2) {
            $primerApellido = $partes[1];
        }

        if (count($partes) === 3) {
            $segundoNombre = $partes[1];
            $primerApellido = $partes[2];
        }

        if (count($partes) >= 4) {
            $segundoNombre = $partes[1];
            $primerApellido = $partes[count($partes) - 2];
            $segundoApellido = $partes[count($partes) - 1];
        }

        $dniInterno = '9999' . str_pad($orden->id, 9, '0', STR_PAD_LEFT);
        
        $cliente = Cliente::create([
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
            'primer_apellido' => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'codigo_pais' => '+504',
            'telefono' => $telefono,
            'correo' => null,
            'dni' => $dniInterno,
            'rtn' => null,
            'tipo_cliente' => 'Natural',
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion_referencia' => 'No especificada',
            'notas' => 'Cliente creado automáticamente desde orden de trabajo ' . $orden->codigo . '.',
            'activo' => true,
        ]);

        $orden->update([
            'cliente_id' => $cliente->id,
        ]);

        return $cliente->id;
    }

    public function convertirOrdenAVenta($id)
    {
        if (!auth()->user()->can('crear ventas')) {
            abort(403, 'No tiene permiso para crear ventas.');
        }

        $orden = OrdenTrabajo::findOrFail($id);

        if ($orden->estado === 'Anulada') {
            session()->flash('error', 'No se puede convertir una orden anulada.');
            return;
        }

        if ($orden->venta_id) {
            session()->flash('error', 'Esta orden ya fue convertida a venta.');
            return;
        }

        $this->ordenConvertirId = $orden->id;
        $this->metodoPagoConversion = $this->metodosPagoVenta[0] ?? 'Efectivo';
        $this->referenciaPagoConversion = 'Orden ' . $orden->codigo;
        $this->mostrarModalConvertirVenta = false;

        $this->confirmarConvertirVenta();
    }

    private function resetDetalle()
    {
        $this->detalle_tipo_item = 'Servicio';
        $this->detalle_producto_id = null;
        $this->detalle_servicio_id = null;
        $this->detalle_descripcion = '';
        $this->detalle_cantidad = 1;
        $this->detalle_precio_unitario = 0;
        $this->detalle_observacion = '';

        $this->resetErrorBag();
        $this->resetValidation();
    }

    private function resetFormulario()
    {
        $this->cliente_id = null;
        $this->cliente_nombre = '';
        $this->cliente_telefono = '';

        $this->fecha = now()->format('Y-m-d');
        $this->fecha_entrega = null;
        $this->titulo = '';
        $this->descripcion = '';
        $this->estado = 'Pendiente';
        $this->prioridad = 'Normal';
        $this->descuento = 0;
        $this->abono = 0;
        $this->observacion = '';

        $this->subtotal = 0;
        $this->total = 0;
        $this->saldo = 0;

        $this->detalles = [];

        $this->resetDetalle();
    }

    public function render()
    {
        if (!auth()->user()->can('ver ordenes trabajo')) {
            abort(403, 'No tiene permiso para ver órdenes de trabajo.');
        }

        $clientes = Cliente::where('activo', true)
            ->orderBy('primer_nombre')
            ->orderBy('primer_apellido')
            ->get();

        $productos = Producto::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $servicios = Servicio::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $ordenes = OrdenTrabajo::with(['cliente', 'usuario'])
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhere('titulo', 'like', '%' . $this->search . '%')
                    ->orWhere('cliente_nombre', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtroEstado !== 'todas', function ($query) {
                $query->where('estado', $this->filtroEstado);
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.ordenes-trabajo.orden-trabajo-index', [
            'clientes' => $clientes,
            'productos' => $productos,
            'servicios' => $servicios,
            'ordenes' => $ordenes,
        ]);
    }
}
