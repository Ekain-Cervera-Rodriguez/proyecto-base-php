<?php

namespace Database\Seeders;

use App\Models\Role;
use Carbon\Carbon;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        $credentials = [
            [
                'role' => 'usuario',
                'credentials' => [
                    'nombre' => 'Usuario',
                    'apellido_paterno' => 'Informática',
                    'apellido_materno' => 'Electoral',
                    'email' => 'user@iemail.com',
                    'password' => 'secret',
                    'telefono' => '1234567890',
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            ],
            [
                'role' => 'administrador',
                'credentials' => [
                    'nombre' => 'Administrador',
                    'apellido_paterno' => 'Informática',
                    'apellido_materno' => 'Electoral',
                    'email' => 'admin@iemail.com',
                    'password' => 'secret',
                    'telefono' => '9876543210',
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            ]
        ];

        foreach ($credentials as $credential) {
            $user = Sentinel::registerAndActivate($credential['credentials']);
            $role = Role::query()->where('slug', $credential['role'])->first();
            $role = Sentinel::findRoleByName($role->name);
            $role->users()->attach($user);
        }
    }
}
