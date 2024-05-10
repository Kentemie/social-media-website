<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;

use App\Models\PostAttachment;
use App\Models\User;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Foundation\Application as ContractApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
