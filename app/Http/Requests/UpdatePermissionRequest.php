<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdatePermissionRequest extends FormRequest
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
            'user_id' => ['required','integer','exists:users,id'],
            'create_scale' => ['boolean', 'sometimes'],
            'read_scale' => ['boolean', 'sometimes'],
            'update_scale' => ['boolean', 'sometimes'],
            'delete_scale' => ['boolean', 'sometimes'],
            'create_music' => ['boolean', 'sometimes'],
            'read_music' => ['boolean', 'sometimes'],
            'update_music' => ['boolean', 'sometimes'],
            'delete_music' => ['boolean', 'sometimes'],
            'create_role' => ['boolean', 'sometimes'],
            'read_role' => ['boolean', 'sometimes'],
            'update_role' => ['boolean', 'sometimes'],
            'delete_role' => ['boolean', 'sometimes'],
            'create_area' => ['boolean', 'sometimes'],
            'read_area' => ['boolean', 'sometimes'],
            'update_area' => ['boolean', 'sometimes'],
            'delete_area' => ['boolean', 'sometimes'],
            'create_chat' => ['boolean', 'sometimes'],
            'read_chat' => ['boolean', 'sometimes'],
            'update_chat' => ['boolean', 'sometimes'],
            'delete_chat' => ['boolean', 'sometimes'],
            'manage_users' => ['boolean', 'sometimes'],
            'manage_church_settings' => ['boolean', 'sometimes'],
            'manage_app_settings' => ['boolean', 'sometimes'],
        ];
    }
}
