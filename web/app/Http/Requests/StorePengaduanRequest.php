<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengaduanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => ['required', 'exists:kategori_pengaduan,id'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'min:10'],
            'lokasi' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'foto' => ['nullable', 'array', 'max:5'],
            'foto.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
