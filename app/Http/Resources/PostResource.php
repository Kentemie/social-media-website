<?php

namespace App\Http\Resources;

use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'body' => $this->body,
            'user' => new UserResource($this->user),
            'group' => $this->group,
            'attachments' => PostAttachmentResource::collection($this->attachments),
            'number_of_reactions' => $this->reactions_count,
            'current_user_has_reaction' => $this->reactions->count() > 0,
            'comments' => self::convertCommentsToTree($this->comments),
            'number_of_comments' => count($this->comments),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param PostComment[] $comments
     * @param $parentId
     * @return array
     */
        private static function convertCommentsToTree($comments, $parentId = null): array
        {
            $commentTree = [];

            foreach ($comments as $comment) {
                if ($comment->parent_id === $parentId) {
                    $children = self::convertCommentsToTree($comments, $comment->id);
                    $comment->childComments = $children;
                    $comment->numberOfComments = collect($children)->sum('numberOfComments') + count($children);
                    $commentTree[] = new PostCommentResource($comment);
                }
            }

            return $commentTree;
        }
}
