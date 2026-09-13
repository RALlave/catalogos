<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FilesRestoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Whether the archive really is a zip —and whether it carries the backup
     * directories— is settled when it is opened, not here.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'extensions:zip',
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
            'file.extensions' => 'El archivo debe tener extensión .zip',
        ];
    }
}
