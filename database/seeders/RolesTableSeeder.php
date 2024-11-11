<?php

namespace Database\Seeders;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Administrador',
            'slug' => 'administrador',
        ]);

        Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Usuario',
            'slug' => 'usuario',
        ]);

    }
}
