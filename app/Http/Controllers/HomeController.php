<?php

namespace App\Http\Controllers;

use App\Http\Enums\GroupUserStatus;
use App\Http\Resources\GroupResource;
use App\Http\Resources\PostResource;
use App\Models\Group;
use App\Models\Post;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|Response
    {
        $userId = Auth::id();

        $posts = PostResource::collection(
            Post::query()
                ->withCount('reactions')
                ->with([
                    'comments' => function ($query) use ($userId) {
                        $query->withCount('reactions');
                    },
                    'reactions' => function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    }
                ])
                ->latest()
                ->paginate(20)
        );

        if ($request->wantsJson()) {
            return $posts;
        }

        $groups = GroupResource::collection(
            Group::query()
                ->with('currentUserGroup')
                ->select(['groups.*'])
                ->join('group_users AS gu', 'gu.group_id', '=', 'groups.id')
                ->where('gu.user_id', $userId)
                ->orderBy('gu.role')
                ->orderBy('name', 'desc')
                ->get()
        );

        return Inertia::render('Home', [
            'posts' => $posts,
            'groups' => $groups,
        ]);
    }
}
