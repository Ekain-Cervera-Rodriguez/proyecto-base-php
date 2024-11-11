<?php

namespace App\Console\Commands;

use App\Models\Role;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class PermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permisos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza permisos del root';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $roles = Role::query()->select('name')->orderBy('id')->get();

        $procesos = array('Salir');

        foreach ($roles as $r) {
            array_push($procesos, $r->name);
        }

        $proceso = $this->choice('Proceso ?', $procesos, 0);

        if ($proceso == 'Salir') {
            $this->info('BYE');
            dd();
        }

        $rolName = $proceso;
        $this->info('Actualizando permisos para: ' . $rolName);
        $role = Sentinel::findRoleByName($rolName);

        $permisos = [];
        $routeCollection = Route::getRoutes();

        foreach ($routeCollection as $route) {
            $action = $route->getAction();
            $middleware = $action['middleware'] ?? [];

            if (!empty($middleware) && in_array('App\\Http\\Middleware\\SentinelACL', $middleware) && $route->getName() !== "") {
                $permisos[$route->getName()] = true;
            }
        }


        $role->permissions = $permisos;

        $role->save();

        $this->info('Permisos actualizados para rol: ' . $rolName);
    }
}
