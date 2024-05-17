<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostCommentResource extends JsonResource
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
            'comment' => $this->comment,
            'short_comment' => Str::words($this->comment, 20),
            'number_of_reactions' => $this->reactions_count,
            'number_of_comments' => $this->numberOfComments,
            'current_user_has_reaction' => $this->reactions->count() > 0,
            'comments' => $this->childComments,
            'user' => [
                "id" => $this->user->id,
                "name" => $this->user->name,
                "username" => $this->user->username,
                "avatar_url" => $this->user->avatar_path ? Storage::url($this->user->avatar_path) : null,
            ],
            'created_at' => $this->created_at->format('Y-m-d H-i-s'),
            'updated_at' => $this->updated_at->format('Y-m-d H-i-s'),
        ];
    }
}
