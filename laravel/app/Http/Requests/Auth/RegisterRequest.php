<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => [
                'required',
                'email',
                'regex:/^[\w\.\+\-]+@[\w\-]+(\.[\w\-]+)+$/i',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/']
        ];
    }

    public function messages(): array
    {
        return [
            'email.regex' => __('validation.regex.email'),
            'password.regex' => __('validation.regex.password'),
        ];
    }
}
