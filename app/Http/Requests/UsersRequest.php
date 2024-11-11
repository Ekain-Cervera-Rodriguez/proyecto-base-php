<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->method() == 'PUT') {
            $params = $this->route()->parameters();
            $id = $params['usuario'];

            return [
                'email' => 'string|nullable|email:rfc,dns|unique:users,email,' . $id . ',id',
                'nombre' => 'string|max:191|regex:/^[\pL\s\-]+$/u',
                'password' => 'string|nullable|min:3|nullable',
                'apellido_paterno' => 'string|max:191|regex:/^[\pL\s\-]+$/u',
                'apellido_materno' => 'string|max:191|regex:/^[\pL\s\-]+$/u',
                'telefono' => 'string|digits:10',
                'role_id' => 'integer|exists:roles,id',
            ];
        } else {
            return [
                'email' => 'nullable|string|max:191|email:rfc,dns|unique:users,email',
                'password' => 'nullable|string|min:3|max:30',
                'nombre' => 'required|string|max:191|regex:/^[\pL\s\-]+$/u',
                'apellido_paterno' => 'required|string|max:191|regex:/^[\pL\s\-]+$/u',
                'apellido_materno' => 'string|max:191|regex:/^[\pL\s\-]+$/u',
                'telefono' => 'required|digits:10',
                'role_id' => 'required|integer|exists:roles,id',
            ];
        }
    }
}
