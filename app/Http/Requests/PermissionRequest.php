<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'create_scale' => ['boolean', 'required'],
                'read_scale' => ['boolean', 'required'],
                'update_scale' => ['boolean', 'required'],
                'delete_scale' => ['boolean', 'required'],
                'create_music' => ['boolean', 'required'],
                'read_music' => ['boolean', 'required'],
                'update_music' => ['boolean', 'required'],
                'delete_music' => ['boolean', 'required'],
                'create_role' => ['boolean', 'required'],
                'read_role' => ['boolean', 'required'],
                'update_role' => ['boolean', 'required'],
                'delete_role' => ['boolean', 'required'],
                'create_area' => ['boolean', 'required'],
                'read_area' => ['boolean', 'required'],
                'update_area' => ['boolean', 'required'],
                'delete_area' => ['boolean', 'required'],
                'create_chat' => ['boolean', 'required'],
                'read_chat' => ['boolean', 'required'],
                'update_chat' => ['boolean', 'required'],
                'delete_chat' => ['boolean', 'required'],
                'manage_users' => ['boolean', 'required'],
                'manage_church_settings' => ['boolean', 'required'],
                'manage_app_settings' => ['boolean', 'required'],
        ];
    }
}
