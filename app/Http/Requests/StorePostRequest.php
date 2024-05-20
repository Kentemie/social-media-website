<?php

namespace App\Http\Requests;

use App\Http\Enums\GroupUserStatus;
use App\Models\GroupUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File;

class StorePostRequest extends FormRequest
{
    public static array $extensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string'],
            'user_id' => ['numeric', 'exists:users,id'],
            'group_id' => [
                'nullable',
                'numeric',
                'exists:groups,id',
                function ($attribute, $value, \Closure $fail) {
                    $groupUser = GroupUser::where('user_id', '=', Auth::id())
                        ->where('group_id', '=', $value)
                        ->where('status', '=', GroupUserStatus::APPROVED->value)
                        ->exists();
                    if (!$groupUser) {
                        $fail('You are not allowed to create a post in this group.');
                    }
                }
            ],
            'attachments' => [
                'array',
                'max:50',
                function ($attribute, $value, \Closure $fail) {
                    $totalSize = collect($value)->sum(fn (UploadedFile $file) => $file->getSize());

                    if ($totalSize > 1_073_741_824) {
                        $fail('The total size of all files must not exceed 1GB.');
                    }
                }
            ],
            'attachments.*' => [
                'file',
                File::types(self::$extensions),
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
            'attachments.*.file' => 'Each attachment must be a file.',
            'attachments.*.mimes' => 'Invalid file type for attachments.',
        ];
    }
}
