<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsersRequest;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $orderBy = $request->input('sortBy', 'users.id');
        $order = $request->input('order', 'desc'); //asc
        $deleted = $request->input('deleted', 0);

        $campos = [
            'users.id as id',
            DB::raw("CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) as nombre"),
            'telefono as celular',
            'email as correo',
            'roles.name as role',
            'users.deleted_at as borrar'
        ];

        $queryBuilder = $deleted ? User::onlyTrashed() : User::withoutTrashed();

        $queryBuilder = $queryBuilder->select($campos)
            ->join("role_users", "users.id", "=", "role_users.user_id")
            ->join("roles", "roles.id", "=", "role_users.role_id")
            ->orderBy($orderBy, $order);

            if ($query = $request->input('query', false)){
                $queryBuilder->where(function ($q) use($query){
                    $q->where('users.nombre', 'like', '%'.$query.'%')
                    ->orWhere('users.email', 'like', '%'.$query.'%');
                });
            }
            if ($perPage = $request->input('perPage', false)){
                $data = $queryBuilder->paginate($perPage);

            }else{
                $data = $queryBuilder->get();
            }

        return response()->success($data);

        /*$queryBuilder->select($campos)
            ->join('role_users', 'role_users.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_users.role_id')
            ->orderBy($orderBy, $order);

        if ($query = $request->get('query', false)) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('nombre', 'like', '%' . $query . '%');
            });
        }


        if ($perPage = $request->input('perPage', false)) {
            $data = $queryBuilder->paginate($perPage);
        } else {
            $data = $queryBuilder->get();
        }


        return response()->success(['data' => $data]);*/
    }

    public function store(UsersRequest $request)
    {
        $data = $request->except('huesped');

        DB::beginTransaction();
        try {

            $credentials = [
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ];

            $user = Sentinel::registerAndActivate($credentials);

            $usuario = User::query()->find($user->id);
            $usuario->update([
                'nombre' => $data['nombre'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => array_key_exists('apellido_materno', $data) ? $data['apellido_materno'] : NULL,
                'password' => bcrypt($data['password']),
                'telefono' => $data['telefono'],
            ]);

            $role = Role::query()->find($data['role_id']);

            RoleUser::query()->create([
                'user_id' => $user->id,
                'role_id' => $role->id
            ]);


            DB::commit();
            return response()->success($usuario);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->unprocessable('Error', [$e->getMessage()]);
        }
    }

    public function update(UsersRequest $request, $id)
    {

        $user = User::query()->findOrFail($id);
        $data = $request->all();

        if (!empty(trim($data['password']))) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        DB::beginTransaction();
        try {
            $user->update($data);
            RoleUser::query()->where('user_id', $id)->update(['role_id' => $data['role_id']]);

            DB::commit();
            return response()->success($user);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->unprocessable('Error', ['Error al actualizar el usuario.']);
        }
    }

    public function show($id)
    {
        $usuario = User::select('users.*', 'roles.name as role', 'roles.id as role_id')
            ->leftJoin('role_users', 'role_users.user_id', '=', 'users.id')
            ->leftJoin('roles', 'roles.id', '=', 'role_users.role_id')
            ->where('users.id', $id)
            ->firstOrFail();

        return response()->success($usuario);
    }

    public function destroy($id)
    {
        if ($id == Auth::id()) {
            return response()->unprocessable('Error', ['No es posible eliminar su propio usuario.']);
        }

        $usuario = User::withTrashed()->findOrfail($id);
        if ($usuario->deleted_at) {
            $usuario->restore();
        } else {
            $usuario->delete();
        }
        return response()->success(['result' => 'ok']);
    }

    public function roles()
    {

        $roles = Role::query()->select('id', 'name as nombre')->get();

        return response()->success($roles);

    }


}
