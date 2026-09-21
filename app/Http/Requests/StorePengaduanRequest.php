<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengaduanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'pelapor';
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

    public function messages(): array
    {
        return [
            'kategori_id.required' => 'Pilih kategori pengaduan.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'judul.required' => 'Judul wajib diisi.',
            'judul.max' => 'Judul maksimal :max karakter.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'deskripsi.min' => 'Deskripsi minimal :min karakter.',
            'lokasi.required' => 'Lokasi wajib diisi.',
            'lokasi.max' => 'Lokasi maksimal :max karakter.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus antara -90 dan 90.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus antara -180 dan 180.',
            'foto.max' => 'Maksimal 5 foto per pengaduan.',
            'foto.*.image' => 'File bukti harus berupa gambar.',
            'foto.*.mimes' => 'Foto hanya boleh JPG, JPEG, atau PNG.',
            'foto.*.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
