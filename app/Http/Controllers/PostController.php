<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;

use App\Models\PostAttachment;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Foundation\Application as ContractApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $post->update($request->validated());
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
}
