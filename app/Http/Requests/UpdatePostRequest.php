<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePostRequest extends StorePostRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->route('post')->user_id === Auth::id();
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'deleted_attachment_ids' => ['array'],
            'deleted_attachment_ids.*' => ['numeric'],
        ]);
    }

}
