<?php

namespace App\Http\Livewire\Cotizaciones;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoDetalle;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\BitacoraSistema;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CotizacionIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $filtroEstado = 'todas';

    public $cliente_id;
    public $cliente_nombre;
    public $cliente_telefono;

    public $fecha;
    public $fecha_validez;
    public $titulo;
    public $descripcion;
    public $estado = 'Pendiente';
    public $descuento = 0;
    public $condiciones;
    public $observacion;

    public $subtotal = 0;
    public $total = 0;

    public $detalles = [];

    public $detalle_tipo_item = 'Servicio';
    public $detalle_producto_id;
    public $detalle_servicio_id;
    public $detalle_descripcion;
    public $detalle_cantidad = 1;
    public $detalle_precio_unitario = 0;
    public $detalle_observacion;

    public $mostrarModalDetalle = false;
    public $cotizacionDetalle = null;

    public $mostrarModalAnulacion = false;
    public $cotizacionAnularId;
    public $motivoAnulacion;

    public $mostrarModalConvertir = false;
    public $cotizacionConvertirId;

    public $estados = [
        'Pendiente',
        'Aprobada',
        'Rechazada',
        'Anulada',
    ];

    protected $messages = [
        'fecha.required' => 'Debe seleccionar la fecha.',
        'fecha.date' => 'La fecha no es válida.',
        'fecha_validez.date' => 'La fecha de validez no es válida.',
        'titulo.required' => 'Debe ingresar el título de la cotización.',
        'titulo.min' => 'El título debe tener al menos 3 caracteres.',
        'titulo.max' => 'El título no debe superar los 150 caracteres.',
        'cliente_nombre.max' => 'El nombre del cliente no debe superar los 150 caracteres.',
        'cliente_telefono.max' => 'El teléfono no debe superar los 30 caracteres.',
        'descripcion.max' => 'La descripción no debe superar los 1000 caracteres.',
        'descuento.numeric' => 'El descuento debe ser numérico.',
        'descuento.min' => 'El descuento no puede ser negativo.',
        'condiciones.max' => 'Las condiciones no deben superar los 1000 caracteres.',
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
        if (!auth()->user()->can('ver cotizaciones')) {
            abort(403, 'No tiene permiso para ver cotizaciones.');
        }

        $this->fecha = now()->format('Y-m-d');
        $this->fecha_validez = now()->addDays(15)->format('Y-m-d');

        $this->condiciones = 'Precios sujetos a cambios según materiales disponibles. Cotización válida hasta la fecha indicada.';
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

        if ($descuento < 0) {
            $descuento = 0;
        }

        if ($descuento > $this->subtotal) {
            $descuento = $this->subtotal;
        }

        $this->descuento = round($descuento, 2);
        $this->total = round(max($this->subtotal - $this->descuento, 0), 2);
    }

    public function registrarCotizacion()
    {
        if (!auth()->user()->can('crear cotizaciones')) {
            abort(403, 'No tiene permiso para crear cotizaciones.');
        }

        $this->validate([
            'fecha' => 'required|date',
            'fecha_validez' => 'nullable|date',
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_nombre' => 'nullable|max:150',
            'cliente_telefono' => 'nullable|max:30',
            'titulo' => 'required|min:3|max:150',
            'descripcion' => 'nullable|max:1000',
            'descuento' => 'required|numeric|min:0',
            'condiciones' => 'nullable|max:1000',
            'observacion' => 'nullable|max:500',
        ]);

        if (count($this->detalles) === 0) {
            session()->flash('error', 'Debe agregar al menos un detalle a la cotización.');
            return;
        }

        $this->calcularTotales();

        DB::transaction(function () {
            $cotizacion = Cotizacion::create([
                'fecha' => $this->fecha,
                'fecha_validez' => $this->fecha_validez,
                'cliente_id' => $this->cliente_id,
                'cliente_nombre' => $this->cliente_nombre,
                'cliente_telefono' => $this->cliente_telefono,
                'titulo' => trim($this->titulo),
                'descripcion' => $this->descripcion,
                'estado' => 'Pendiente',
                'subtotal' => $this->subtotal,
                'descuento' => $this->descuento,
                'total' => $this->total,
                'condiciones' => $this->condiciones,
                'observacion' => $this->observacion,
                'user_id' => auth()->id(),
            ]);

            foreach ($this->detalles as $detalle) {
                CotizacionDetalle::create([
                    'cotizacion_id' => $cotizacion->id,
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
                'Cotizaciones',
                'Registrar',
                'Registró la cotización ' . $cotizacion->codigo . ' por L ' . number_format($cotizacion->total, 2) . '.',
                Cotizacion::class,
                $cotizacion->id,
                null,
                $cotizacion->load('detalles')->toArray()
            );
        });

        $this->resetFormulario();

        session()->flash('message', 'Cotización registrada correctamente.');
    }

    public function verDetalle($id)
    {
        if (!auth()->user()->can('ver cotizaciones')) {
            abort(403, 'No tiene permiso para ver cotizaciones.');
        }

        $this->cotizacionDetalle = Cotizacion::with([
            'cliente',
            'detalles.producto',
            'detalles.servicio',
            'usuario',
            'usuarioAnulacion',
            'ordenTrabajo',
        ])->findOrFail($id);

        $this->mostrarModalDetalle = true;
    }

    public function cerrarModalDetalle()
    {
        $this->mostrarModalDetalle = false;
        $this->cotizacionDetalle = null;
    }

    public function abrirAnular($id)
    {
        if (!auth()->user()->can('anular cotizaciones')) {
            abort(403, 'No tiene permiso para anular cotizaciones.');
        }

        $cotizacion = Cotizacion::findOrFail($id);

        if ($cotizacion->estado === 'Anulada') {
            session()->flash('error', 'Esta cotización ya está anulada.');
            return;
        }

        if ($cotizacion->orden_trabajo_id) {
            session()->flash('error', 'No se puede anular una cotización convertida en orden de trabajo.');
            return;
        }

        $this->cotizacionAnularId = $cotizacion->id;
        $this->motivoAnulacion = '';
        $this->mostrarModalAnulacion = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function cerrarModalAnulacion()
    {
        $this->mostrarModalAnulacion = false;
        $this->cotizacionAnularId = null;
        $this->motivoAnulacion = '';

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function confirmarAnular()
    {
        if (!auth()->user()->can('anular cotizaciones')) {
            abort(403, 'No tiene permiso para anular cotizaciones.');
        }

        $this->validate([
            'cotizacionAnularId' => 'required|exists:cotizaciones,id',
            'motivoAnulacion' => 'required|min:5|max:500',
        ]);

        $cotizacion = Cotizacion::findOrFail($this->cotizacionAnularId);

        if ($cotizacion->orden_trabajo_id) {
            session()->flash('error', 'No se puede anular una cotización convertida en orden de trabajo.');
            return;
        }

        $datosAnteriores = $cotizacion->toArray();

        $cotizacion->update([
            'estado' => 'Anulada',
            'fecha_anulacion' => now(),
            'anulado_por' => auth()->id(),
            'motivo_anulacion' => $this->motivoAnulacion,
        ]);

        BitacoraSistema::registrar(
            'Cotizaciones',
            'Anular',
            'Anuló la cotización ' . $cotizacion->codigo . '. Motivo: ' . $this->motivoAnulacion,
            Cotizacion::class,
            $cotizacion->id,
            $datosAnteriores,
            $cotizacion->fresh()->toArray()
        );

        $this->cerrarModalAnulacion();

        session()->flash('message', 'Cotización anulada correctamente.');
    }

    public function abrirConvertir($id)
    {
        if (!auth()->user()->can('convertir cotizaciones')) {
            abort(403, 'No tiene permiso para convertir cotizaciones.');
        }

        $cotizacion = Cotizacion::findOrFail($id);

        if ($cotizacion->estado === 'Anulada') {
            session()->flash('error', 'No se puede convertir una cotización anulada.');
            return;
        }

        if ($cotizacion->orden_trabajo_id) {
            session()->flash('error', 'Esta cotización ya fue convertida en orden de trabajo.');
            return;
        }

        $this->cotizacionConvertirId = $cotizacion->id;
        $this->mostrarModalConvertir = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function cerrarModalConvertir()
    {
        $this->mostrarModalConvertir = false;
        $this->cotizacionConvertirId = null;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function confirmarConvertir()
    {
        if (!auth()->user()->can('convertir cotizaciones')) {
            abort(403, 'No tiene permiso para convertir cotizaciones.');
        }

        $this->validate([
            'cotizacionConvertirId' => 'required|exists:cotizaciones,id',
        ]);

        try {
            DB::transaction(function () {
                $cotizacion = Cotizacion::with(['detalles'])
                    ->lockForUpdate()
                    ->findOrFail($this->cotizacionConvertirId);

                $datosAnterioresCotizacion = $cotizacion->toArray();

                if ($cotizacion->estado === 'Anulada') {
                    throw new \Exception('No se puede convertir una cotización anulada.');
                }

                if ($cotizacion->orden_trabajo_id) {
                    throw new \Exception('Esta cotización ya fue convertida en orden de trabajo.');
                }

                if ($cotizacion->detalles->count() === 0) {
                    throw new \Exception('La cotización no tiene detalles para convertir.');
                }

                $orden = OrdenTrabajo::create([
                    'fecha' => now()->format('Y-m-d'),
                    'fecha_entrega' => null,
                    'cliente_id' => $cotizacion->cliente_id,
                    'cliente_nombre' => $cotizacion->cliente_nombre,
                    'cliente_telefono' => $cotizacion->cliente_telefono,
                    'titulo' => $cotizacion->titulo,
                    'descripcion' => $cotizacion->descripcion,
                    'estado' => 'Pendiente',
                    'prioridad' => 'Normal',
                    'subtotal' => $cotizacion->subtotal,
                    'descuento' => $cotizacion->descuento,
                    'total' => $cotizacion->total,
                    'abono' => 0,
                    'saldo' => $cotizacion->total,
                    'observacion' => 'Orden generada desde cotización ' . $cotizacion->codigo . '. ' . ($cotizacion->observacion ?? ''),
                    'user_id' => auth()->id(),
                ]);

                foreach ($cotizacion->detalles as $detalle) {
                    OrdenTrabajoDetalle::create([
                        'orden_trabajo_id' => $orden->id,
                        'tipo_item' => $detalle->tipo_item,
                        'producto_id' => $detalle->producto_id,
                        'servicio_id' => $detalle->servicio_id,
                        'descripcion' => $detalle->descripcion,
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'subtotal' => $detalle->subtotal,
                        'observacion' => $detalle->observacion,
                    ]);
                }

                $cotizacion->update([
                    'estado' => 'Aprobada',
                    'orden_trabajo_id' => $orden->id,
                ]);

                BitacoraSistema::registrar(
                    'Cotizaciones',
                    'Convertir',
                    'Convirtió la cotización ' . $cotizacion->codigo . ' en la orden de trabajo ' . $orden->codigo . '.',
                    Cotizacion::class,
                    $cotizacion->id,
                    $datosAnterioresCotizacion,
                    $cotizacion->fresh()->load('ordenTrabajo')->toArray()
                );

                BitacoraSistema::registrar(
                    'Órdenes de trabajo',
                    'Registrar',
                    'Creó la orden de trabajo ' . $orden->codigo . ' desde la cotización ' . $cotizacion->codigo . '.',
                    OrdenTrabajo::class,
                    $orden->id,
                    null,
                    $orden->load('detalles')->toArray()
                );
                
            });
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        $this->cerrarModalConvertir();

        session()->flash('message', 'Cotización convertida en orden de trabajo correctamente.');
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
        $this->fecha_validez = now()->addDays(15)->format('Y-m-d');
        $this->titulo = '';
        $this->descripcion = '';
        $this->estado = 'Pendiente';
        $this->descuento = 0;
        $this->condiciones = 'Precios sujetos a cambios según materiales disponibles. Cotización válida hasta la fecha indicada.';
        $this->observacion = '';

        $this->subtotal = 0;
        $this->total = 0;

        $this->detalles = [];

        $this->resetDetalle();
    }

    public function render()
    {
        if (!auth()->user()->can('ver cotizaciones')) {
            abort(403, 'No tiene permiso para ver cotizaciones.');
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

        $cotizaciones = Cotizacion::with(['cliente', 'usuario', 'ordenTrabajo'])
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

        return view('livewire.cotizaciones.cotizacion-index', [
            'clientes' => $clientes,
            'productos' => $productos,
            'servicios' => $servicios,
            'cotizaciones' => $cotizaciones,
        ]);
    }
}
