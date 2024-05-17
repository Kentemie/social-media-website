<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'short_description' => Str::words($this->description, 10),
            'slug' => $this->slug,
            'auto_approval' => $this->auto_approval,
            'cover_url' => $this->cover_path ? Storage::url($this->cover_path) : '/images/default_cover_image.jpg',
            'thumbnail_url' => $this->thumbnail_path ? Storage::url($this->thumbnail_path) : '/images/no_image.png',
            'user_id' => $this->user_id,
//            'deleted_by' => $this->deleted_by,
//            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->currentUserGroup?->status,
            'role' => $this->currentUserGroup?->role,
        ];
    }
}
