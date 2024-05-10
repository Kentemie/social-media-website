<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StorePostRequest extends FormRequest
{
    public static array $extensions = [
        'gif', 'webp',
        'mp3', 'mp4', 'wav',
        'doc', 'docx', 'pdf', 'csv', 'xls', 'xlsx',
        'zip', 'rar',
    ];

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['numeric'],
            'body' => ['nullable', 'string'],
            'attachments' => ['array', 'max:50'],
            'attachments.*' => [
                'file',
                File::types(self::$extensions)->max(500 * 1024 * 1024)
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->user()->id,
            'body' => $this->input('body') ?: '',
        ]);
    }

    public function messages(): array
    {
        return [
            'attachments.*' => 'Invalid file extension',
        ];
    }
}
