<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterChurchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Se quiser permitir sempre
        return true;
    }

    public function rules(): array
    {
        return [
            // dados do usuário
            'user_name'      => ['required', 'string', 'max:255'],
            'user_email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'user_password'  => ['required', 'string', 'min:8', 'confirmed'],
            'user_birthday'  => ['required', 'date'],

            // dados da igreja
            'church_name'       => ['required', 'string', 'max:120'],
            'church_cep'        => ['required', 'regex:/^\d{5}-?\d{3}$/'],
            'church_street'     => ['required', 'string', 'max:160'],
            'church_number'     => ['required', 'string', 'max:10'],
            'church_complement' => ['nullable', 'string', 'max:160'],
            'church_quarter'    => ['required', 'string', 'max:120'],
            'church_city'       => ['required', 'string', 'max:120'],
            'church_state'      => ['required', "in:" . implode(',', $this->ufs())],
        ];
    }

    /**
     * Retorna as UFs válidas
     */
    private function ufs(): array
    {
        return [
            'AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG',
            'PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO',
        ];
    }
}
