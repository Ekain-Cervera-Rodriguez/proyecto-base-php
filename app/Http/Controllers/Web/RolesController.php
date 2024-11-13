<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolesRequest;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{

    public function index(Request $request)
    {
        $orderBy = $request->input('sortBy', 'roles.id');
        $order = $request->input('order', 'desc');
        $deleted = $request->input('deleted', 0);

        $campos = [
            'roles.id as id',
            'roles.name as name',
            'roles.slug as slug',
            'roles.deleted_at as deleted',
        ];

        $queryBuilder = $deleted ? Role::onlyTrashed() : Role::withoutTrashed();

        $queryBuilder = $queryBuilder->select($campos)
            ->join('role_users', 'role_users.role_id', '=', 'roles.id')
            ->join('users', 'users.id', '=', 'role_users.user_id')
            ->orderBy($orderBy, $order);

        if ($query = $request->input('perPage', false)) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('roles.name', 'like', '%' . $query . '%')
                    ->orWhere('roles.slug', 'like', '%' . $query . '%');
            });
        }
        if($perPage = $request->query('perPage', false)){
            $data = $queryBuilder->paginate($perPage);
        }else{
            $data = $queryBuilder->get();
        }
        return response()->success($data);
    }


    public function store(RolesRequest $request)
    {
        $data = $request->except('huesped');


        DB::beginTransaction();
        try {

            $role = Sentinel::check($data);
            $rol = Role::query()->find($role->id);
            $rol->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                "permissions" => $data['permissions'],
            ]);

            $user = Role::query()->find($data['role_id']);

            RoleUser::query()->create([
                'role_id' => $role->id,
                'user_id' => $user->id
            ]);


            DB::commit();
            return response()->success($rol);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->unprocessable('Error', [$e->getMessage()]);
        }
    }

    public function update(RolesRequest $request, $id)
    {

        $role = Role::query()->findOrFail($id);
        $data = $request->all();

        DB::beginTransaction();
        try {
            $role->update($data);
            RoleUser::query()->where('role_id', $id)->update(['user_id' => $data['user_id']]);

            DB::commit();
            return response()->success($role);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->unprocessable('Error', ['Error al actualizar el rol.']);
        }
    }

    public function show($id)
    {
        $rol = Role::select('roles.*', 'users.name as users', 'users.id as user_id')
            ->leftJoin('role_users', 'role_users.role_id', '=', 'roles.id')
            ->leftJoin('users', 'users.id', '=', 'role_users.user_id')
            ->where('roles.id', $id)
            ->firstOrFail();

        return response()->success($rol);
    }

    public function destroy($id)
    {
        if ($id == Auth::user()) {
            return response()->unprocessable('Error', ['No es posible eliminar el rol.']);
        }

        $rol = Role::withTrashed()->findOrfail($id);
        if ($rol->deleted_at) {
            $rol->restore();
        } else {
            $rol->delete();
        }
        return response()->success(['result' => 'ok']);
    }
    public function users(){
        $users = User::query()->select('id', 'email')->get();
        return response()->success($users);
    }
}
