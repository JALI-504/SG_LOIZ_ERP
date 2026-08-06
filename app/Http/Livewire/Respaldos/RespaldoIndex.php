<?php

namespace App\Http\Livewire\Respaldos;

use App\Models\BitacoraSistema;
use App\Models\RespaldoSistema;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\Process\Process;

class RespaldoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $generando = false;

    public function mount()
    {
        if (!auth()->user()->can('ver respaldos')) {
            abort(403, 'No tiene permiso para ver respaldos.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function generarRespaldo()
    {
        if (!auth()->user()->can('generar respaldos')) {
            abort(403, 'No tiene permiso para generar respaldos.');
        }

        $this->generando = true;

        try {
            $conexion = config('database.default');
            $config = config('database.connections.' . $conexion);

            if (!$config || ($config['driver'] ?? null) !== 'mysql') {
                session()->flash('error', 'El respaldo automático solo está configurado para MySQL/MariaDB.');
                $this->generando = false;
                return;
            }

            $database = $config['database'];
            $username = $config['username'];
            $password = $config['password'];
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? '3306';

            $mysqldump = $this->obtenerRutaMysqldump();

            if (!$mysqldump) {
                session()->flash('error', 'No se encontró mysqldump. Verifique que MySQL/MariaDB esté instalado o configure la ruta.');
                $this->generando = false;
                return;
            }

            Storage::makeDirectory('respaldos');

            $fecha = now()->format('Ymd_His');
            $nombreArchivo = 'respaldo_' . $database . '_' . $fecha . '.sql';
            $rutaRelativa = 'respaldos/' . $nombreArchivo;
            $rutaCompleta = storage_path('app/' . $rutaRelativa);

            $comando = [
                $mysqldump,
                '--host=' . $host,
                '--port=' . $port,
                '--user=' . $username,
                '--default-character-set=utf8mb4',
                '--single-transaction',
                '--quick',
                '--routines',
                '--triggers',
                '--events',
                '--result-file=' . $rutaCompleta,
                $database,
            ];

            if ($password !== null && $password !== '') {
                array_splice($comando, 4, 0, '--password=' . $password);
            }

            $process = new Process($comando);
            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                session()->flash('error', 'No se pudo generar el respaldo. Detalle: ' . $process->getErrorOutput());
                $this->generando = false;
                return;
            }

            if (!file_exists($rutaCompleta) || filesize($rutaCompleta) <= 0) {
                session()->flash('error', 'El respaldo fue generado vacío o no se encontró el archivo resultante.');
                $this->generando = false;
                return;
            }

            $tamanoMb = round(filesize($rutaCompleta) / 1024 / 1024, 2);

            $respaldo = RespaldoSistema::create([
                'user_id' => auth()->id(),
                'nombre_archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaRelativa,
                'tipo' => 'Base de datos',
                'tamano_mb' => $tamanoMb,
                'estado' => 'Generado',
                'observacion' => 'Respaldo generado manualmente desde el sistema.',
                'fecha_generacion' => now(),
            ]);

            BitacoraSistema::registrar(
                'Respaldos',
                'Generar',
                'Generó el respaldo de base de datos ' . $nombreArchivo . '.',
                RespaldoSistema::class,
                $respaldo->id,
                null,
                $respaldo->toArray()
            );

            session()->flash('message', 'Respaldo generado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al generar respaldo: ' . $e->getMessage());
        }

        $this->generando = false;
    }

    public function descargar($id)
    {
        if (!auth()->user()->can('descargar respaldos')) {
            abort(403, 'No tiene permiso para descargar respaldos.');
        }

        $respaldo = RespaldoSistema::findOrFail($id);

        $rutaCompleta = storage_path('app/' . $respaldo->ruta_archivo);

        if (!file_exists($rutaCompleta)) {
            session()->flash('error', 'El archivo de respaldo ya no existe en el servidor.');
            return;
        }

        BitacoraSistema::registrar(
            'Respaldos',
            'Descargar',
            'Descargó el respaldo de base de datos ' . $respaldo->nombre_archivo . '.',
            RespaldoSistema::class,
            $respaldo->id,
            null,
            [
                'respaldo_id' => $respaldo->id,
                'nombre_archivo' => $respaldo->nombre_archivo,
                'ruta_archivo' => $respaldo->ruta_archivo,
                'tamano_mb' => $respaldo->tamano_mb,
                'descargado_en' => now()->format('Y-m-d H:i:s'),
            ]
        );

        return response()->download($rutaCompleta, $respaldo->nombre_archivo);
    }

    private function obtenerRutaMysqldump()
    {
        $rutaEnv = env('MYSQLDUMP_PATH');

        if ($rutaEnv && file_exists($rutaEnv)) {
            return $rutaEnv;
        }

        $laragonMysql = glob('C:/laragon/bin/mysql/*/bin/mysqldump.exe');

        if ($laragonMysql && count($laragonMysql) > 0) {
            return $laragonMysql[0];
        }

        $posiblesRutas = [
            'C:/xampp/mysql/bin/mysqldump.exe',
            'C:/wamp64/bin/mysql/mysql8.0.31/bin/mysqldump.exe',
            'C:/Program Files/MySQL/MySQL Server 8.0/bin/mysqldump.exe',
        ];

        foreach ($posiblesRutas as $ruta) {
            if (file_exists($ruta)) {
                return $ruta;
            }
        }

        return null;
    }

    public function render()
    {
        if (!auth()->user()->can('ver respaldos')) {
            abort(403, 'No tiene permiso para ver respaldos.');
        }

        $respaldos = RespaldoSistema::with('usuario')
            ->where(function ($query) {
                $query->where('nombre_archivo', 'like', '%' . $this->search . '%')
                    ->orWhere('tipo', 'like', '%' . $this->search . '%')
                    ->orWhere('estado', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.respaldos.respaldo-index', [
            'respaldos' => $respaldos,
        ]);
    }
}
