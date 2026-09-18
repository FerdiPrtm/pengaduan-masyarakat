<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isPetugas();
    }

    public function rules(): array
    {
        return [
            'hasil' => ['required', 'in:valid,tidak_valid,butuh_info'],
            'catatan' => ['nullable', 'string', 'required_if:hasil,tidak_valid,butuh_info'],
        ];
    }
}
