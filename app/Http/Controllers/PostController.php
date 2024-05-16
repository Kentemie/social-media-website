<?php

namespace App\Http\Controllers;

use App\Http\Enums\ReactionEnum;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostCommentRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCommentResource;
use App\Models\Post;

use App\Models\PostAttachment;
use App\Models\PostComment;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Foundation\Application as ContractApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PostController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        /** @var UploadedFile[] $files */
        $files = $data['attachments'] ?? [];

        $allFilesPaths = [];

        DB::beginTransaction();
        try {
            $post = Post::create($data);

            $allFilesPaths = $this->storeAttachments($files, $post, $user);

            DB::commit();
        } catch(\Exception $e) {
            foreach ($allFilesPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            DB::rollBack();
        }

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        /** @var UploadedFile[] $files */
        $files = $data['attachments'] ?? [];
        $deleted_ids = $data['deleted_attachment_ids'] ?? [];

        $allFilesPaths = [];

        DB::beginTransaction();
        try {
            $post->update($data);
            $post_attachments = PostAttachment::query()
                ->where('post_id', $post->id)
                ->whereIn('id', $deleted_ids)
                ->get();

            foreach ($post_attachments as $attachment) {
                $attachment->delete();
            }

            $allFilesPaths = $this->storeAttachments($files, $post, $user);

            DB::commit();
        } catch(\Exception $e) {
            foreach ($allFilesPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            DB::rollBack();
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): Application|Response|RedirectResponse|ContractApplication|ResponseFactory
    {
        $id = Auth::id();

        if ($post->user_id !== $id) {
            return response("You are not allowed to delete this post", 403);
        }

        $post->delete();
        return back();
    }

    /**
     * Download the attachment to the specified post.
     */
    public function downloadAttachment(PostAttachment $post_attachment): BinaryFileResponse
    {
        return response()->download(
            Storage::disk('public')->path($post_attachment->path),
            $post_attachment->name
        );
    }

    /**
     * Add a reaction to a specific post.
     */
    public function postReaction(Request $request, Post $post): Application|Response|ContractApplication|ResponseFactory
    {
        $userId = Auth::id();
        $data = $request->validate([
           'reaction' => [Rule::enum(ReactionEnum::class)],
        ]);

        $reaction = Reaction::where('user_id', $userId)
            ->where('object_id', $post->id)
            ->where('object_type', Post::class)
            ->first();

        $hasReaction = $reaction === null;

        if ($reaction) {
            $reaction->delete();
        } else {
            Reaction::create([
                'type' => $data['reaction'],
                'user_id' => $userId,
                'object_id' => $post->id,
                'object_type' => Post::class,
            ]);
        }

        $reactions_count = Reaction::where('object_id', $post->id)->where('object_type', Post::class)->count();

        return response([
            'number_of_reactions' => $reactions_count,
            'current_user_has_reaction' => $hasReaction
        ], 201);
    }

    /**
     * Create a new comment to a specific post.
     */
    public function createComment(Request $request, Post $post): Application|Response|ContractApplication|ResponseFactory
    {
        $data = $request->validate([
            'comment' => ['required'],
            'parent_id' => ['nullable', 'exists:post_comments,id'],
        ]);

        $comment = PostComment::create([
            'comment' => nl2br($data['comment']),
            'user_id' => Auth::id(),
            'post_id' => $post->id,
            'parent_id' => $data['parent_id'],
        ]);

        return response(new PostCommentResource($comment), 201);
    }

    /**
     * Delete a comment from a specific post.
     */
    public function deleteComment(PostComment $comment): Application|Response|ContractApplication|ResponseFactory
    {
        if ($comment->user->id !== Auth::id()) {
            return response("You are not allowed to delete this comment", 403);
        }

        $comment->delete();
        return response(null, 204);
    }


    /**
     * Update a comment on a specific post.
     */
    public function updateComment(UpdatePostCommentRequest $request, PostComment $comment): Application|Response|ContractApplication|ResponseFactory
    {
        $data = $request->validated();
        $comment->update([
            'comment' => nl2br($data['comment']),
        ]);

        return response(new PostCommentResource($comment), 201);
    }

    /**
     * Add a reaction to a specific comment.
     */
    public function commentReaction(Request $request, PostComment $comment): Application|Response|ContractApplication|ResponseFactory
    {
        $userId = Auth::id();
        $data = $request->validate([
            'reaction' => [Rule::enum(ReactionEnum::class)],
        ]);

        $reaction = Reaction::where('user_id', $userId)
            ->where('object_id', $comment->id)
            ->where('object_type', PostComment::class)
            ->first();

        $hasReaction = $reaction === null;

        if ($reaction) {
            $reaction->delete();
        } else {
            Reaction::create([
                'type' => $data['reaction'],
                'user_id' => $userId,
                'object_id' => $comment->id,
                'object_type' => PostComment::class,
            ]);
        }

        $reactions_count = Reaction::where('object_id', $comment->id)->where('object_type', PostComment::class)->count();

        return response([
            'number_of_reactions' => $reactions_count,
            'current_user_has_reaction' => $hasReaction
        ], 201);
    }


    /**
     * Add the specified attachments to the storage.
     */
    private function storeAttachments(array $files, Post $post, User $user): array
    {
        $allFilesPaths = [];

        foreach ($files as $file) {
            $path = $file->store('attachments/' . $post->id, 'public');
            $allFilesPaths[] = $path;
            PostAttachment::create([
                'post_id' => $post->id,
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'created_by' => $user->id,
            ]);
        }

        return $allFilesPaths;
    }
}
