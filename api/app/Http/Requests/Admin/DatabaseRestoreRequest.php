<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DatabaseRestoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The extension is checked and not the mime type: a .sql dump is plain
     * text, and every browser reports it under a different name.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'extensions:sql',
                'max:'.config('backup.max_upload'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.extensions' => 'El archivo debe tener extensión .sql',
        ];
    }
}
