<?php

namespace App\Http\Livewire\OrdenesTrabajo;

use App\Models\Cliente;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoDetalle;
use App\Models\Producto;
use App\Models\Servicio;
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
            $this->cliente_nombre = $cliente->nombre_cliente
                ?? $cliente->nombre_completo
                ?? $cliente->razon_social
                ?? $cliente->cliente
                ?? 'Cliente #' . $cliente->id;

            $this->cliente_telefono = $cliente->telefono
                ?? $cliente->celular
                ?? $cliente->telefono_cliente
                ?? '';
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

        $orden->update([
            'estado' => $nuevoEstado,
        ]);

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

        $orden->update([
            'estado' => 'Anulada',
            'fecha_anulacion' => now(),
            'anulado_por' => auth()->id(),
            'motivo_anulacion' => $this->motivoAnulacion,
        ]);

        $this->cerrarModalAnulacion();

        session()->flash('message', 'Orden de trabajo anulada correctamente.');
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
            ->orderBy('id')
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
