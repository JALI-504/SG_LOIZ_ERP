<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermisosSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [
            // Dashboard
            'ver dashboard',

            // Ventas
            'ver ventas',
            'crear ventas',
            'ver historial ventas',
            'anular ventas',
            'ver cuentas por cobrar',
            'registrar abonos clientes',
            'anular abonos clientes',
            'imprimir recibos ventas',

            // Clientes
            'ver clientes',
            'crear clientes',
            'editar clientes',
            'eliminar clientes',

            // Productos
            'ver productos',
            'crear productos',
            'editar productos',
            'eliminar productos',

            // Servicios
            'ver servicios',
            'crear servicios',
            'editar servicios',
            'eliminar servicios',

            // Insumos e inventario
            'ver insumos',
            'crear insumos',
            'editar insumos',
            'eliminar insumos',
            'ver inventario',
            'registrar movimientos inventario',

            // Producción
            'ver produccion',
            'crear produccion',
            'ver produccion',
            'crear produccion',
            'anular produccion',

            // Compras
            'ver compras',
            'crear compras',
            'anular compras',
            'ver cuentas por pagar',
            'registrar pagos proveedores',
            'anular pagos proveedores',

            // Proveedores
            'ver proveedores',
            'crear proveedores',
            'editar proveedores',
            'eliminar proveedores',

            // Órdenes de trabajo
            'ver ordenes trabajo',
            'crear ordenes trabajo',
            'editar ordenes trabajo',
            'cambiar estado ordenes trabajo',
            'anular ordenes trabajo',

            // Gastos
            'ver gastos',
            'crear gastos',
            'editar gastos',
            'anular gastos',

            // Reportes
            'ver reportes',
            'ver reporte ventas',
            'ver reporte financiero',
            'ver reporte inventario',
            'ver reporte cuentas',

            // Cotizaciones
            'ver cotizaciones',
            'crear cotizaciones',
            'editar cotizaciones',
            'anular cotizaciones',
            'convertir cotizaciones',
            'imprimir cotizaciones',

            // Cierre de caja
            'ver cierres caja',
            'crear cierres caja',
            'anular cierres caja',
            'imprimir cierres caja',

            // Bitácora / Auditoría
            'ver bitacora',

            // Configuración
            'ver configuracion',
            'editar configuracion',

            // Usuarios y roles
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'desactivar usuarios',
            'cambiar password usuarios',
            'asignar roles usuarios',

            // Roles y permisos
            'ver roles',
            'crear roles',
            'editar roles',
            'eliminar roles',
            'asignar permisos roles',
            
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        $administrador = Role::firstOrCreate(['name' => 'Administrador']);
        $cajero = Role::firstOrCreate(['name' => 'Cajero']);
        $inventario = Role::firstOrCreate(['name' => 'Inventario']);
        $reportes = Role::firstOrCreate(['name' => 'Reportes']);

        $administrador->syncPermissions($permisos);

        $cajero->syncPermissions([
            'ver dashboard',
            'ver ventas',
            'crear ventas',
            'ver historial ventas',
            'ver cuentas por cobrar',
            'registrar abonos clientes',
            'imprimir recibos ventas',
            'ver clientes',
            'crear clientes',
            'editar clientes',
            'ver cierres caja',
            'crear cierres caja',
            'imprimir cierres caja',
        ]);

        $inventario->syncPermissions([
            'ver dashboard',
            'ver productos',
            'crear productos',
            'editar productos',
            'ver servicios',
            'crear servicios',
            'editar servicios',
            'ver insumos',
            'crear insumos',
            'editar insumos',
            'ver inventario',
            'registrar movimientos inventario',
            'ver produccion',
            'crear produccion',
        ]);

        $reportes->syncPermissions([
            'ver dashboard',
            'ver reportes',
            'ver reporte ventas',
            'ver reporte financiero',
            'ver reporte inventario',
            'ver reporte cuentas',
        ]);
    }
}
    