<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitacoraAcceso extends Model
{
    use HasFactory;

    protected $table = 'bitacora_accesos';

    protected $fillable = [
        'user_id',
        'descripcion'
    ];
}
