<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrintJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $maxSizeKb = (int) config('print.upload_max_size_mb', 50) * 1024;
        $allowedMimes = implode(',', config('print.allowed_extensions'));

        return [
            'document' => [
                'required',
                'file',
                "max:{$maxSizeKb}",
                "mimes:{$allowedMimes}",
            ],
            'copies' => ['required', 'integer', 'min:1', 'max:99'],
            'paper_size' => ['required', Rule::in(['A4', 'Legal', 'F4'])],
            'orientation' => ['required', Rule::in(['portrait', 'landscape'])],
            'duplex' => ['required', Rule::in(['none', 'long-edge', 'short-edge'])],
            'color_mode' => ['required', Rule::in(['grayscale', 'color'])],
            'page_range' => ['nullable', 'string', 'max:100', 'regex:/^[0-9,\-\s]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'File dokumen wajib diunggah.',
            'document.file' => 'File dokumen tidak valid.',
            'document.max' => 'Ukuran file melebihi batas maksimal.',
            'document.mimes' => 'Format file tidak didukung.',
            'copies.required' => 'Jumlah copy wajib diisi.',
            'copies.min' => 'Jumlah copy minimal 1.',
            'copies.max' => 'Jumlah copy maksimal 99.',
            'page_range.regex' => 'Format range halaman tidak valid. Contoh: 1-5 atau 1,3,5-10.',
        ];
    }
}
