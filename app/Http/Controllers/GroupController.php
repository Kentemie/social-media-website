<?php

namespace App\Http\Controllers;

use App\Http\Enums\GroupUserRole;
use App\Http\Enums\GroupUserStatus;
use App\Http\Requests\InviteUsersRequest;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupUserResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\User;
use App\Notifications\InvitationApproved;
use App\Notifications\InvitationToGroup;
use App\Notifications\RequestApproved;
use App\Notifications\RequestToJoinGroup;
use App\Notifications\RoleChanged;
use App\Notifications\UserRemovedFromGroup;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class GroupController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        $data = $request->validated();
        $group = Group::create($data);

        $group_user = GroupUser::create([
            'status' => GroupUserStatus::APPROVED->value,
            'role' => GroupUserRole::ADMIN->value,
            'user_id' => $data['user_id'],
            'group_id' => $group->id,
            'created_by' => $data['user_id'],
        ]);

        $group->status = $group_user->status;
        $group->role = $group_user->role;

        return response(new GroupResource($group), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Group $group)
    {
        $group->load('currentUserGroup');
        $userId = Auth::id();

        if ($group->isMember($userId)) {
            $posts = PostResource::collection(
                Post::postsForTimeline($userId)
                    ->where('group_id', '=', $group->id)
                    ->paginate(10)
            );
        } else {
            return Inertia::render('Group/View', [
                'success' => session('success'),
                'group' => new GroupResource($group),
                'posts' => null,
                'users' => [],
                'requests' => [],
            ]);
        }

        if ($request->wantsJson()) {
            return $posts;
        }

        $users = User::query()
            ->select(['users.*', 'gu.role', 'gu.status', 'gu.group_id'])
            ->join('group_users AS gu', 'gu.user_id', '=', 'users.id')
            ->where('gu.group_id', '=', $group->id)
            ->orderBy('users.name')
            ->get();
        $requests = $group->pendingUsers()->orderBy('name')->get();

        return Inertia::render('Group/View', [
            'success' => session('success'),
            'group' => new GroupResource($group),
            'posts' => $posts,
            'users' => GroupUserResource::collection($users),
            'requests' => UserResource::collection($requests),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        $group->update($request->validated());
        return back()->with('success', 'Group updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        //
    }

    /**
     * Update the group`s profile images
     */
    public function updateImage(Request $request, Group $group)
    {
        $this->ensureAdmin($group);

        $data = $request->validate([
            'cover' => ['nullable', 'image'],
            'thumbnail' => ['nullable', 'image'],
        ]);

        /** @var UploadedFile $cover */
        $cover = $data['cover'] ?? null;

        /** @var UploadedFile $thumbnail */
        $thumbnail = $data['thumbnail'] ?? null;

        $success = '';

        if ($cover) {
            if ($group->cover_path) {
                Storage::disk('public')->delete($group->cover_path);
            }
            $path = $cover->store('group-' . $group->id, 'public');
            $group->update(['cover_path' => $path]);
            $success = 'The group`s cover image has been updated.';
        }

        if ($thumbnail) {
            if ($group->thumbnail_path) {
                Storage::disk('public')->delete($group->thumbnail_path);
            }
            $path = $thumbnail->store('group-' . $group->id, 'public');
            $group->update(['thumbnail_path' => $path]);
            $success = 'The group`s thumbnail has been updated.';
        }

        return back()->with('success', $success);
    }

    /**
     * Invite a user to a specified group
     */
    public function inviteUsers(InviteUsersRequest $request, Group $group): RedirectResponse
    {
        $userToInvite = $request->user;
        $groupUser = $request->groupUser;

        $groupUser?->delete();

        $tokenLifespanInHours = 24;
        $token = Str::random(256);

        GroupUser::create([
            'status' => GroupUserStatus::PENDING->value,
            'role' => GroupUserRole::USER->value,
            'token' => $token,
            'token_expiry_date' => Carbon::now()->addHours($tokenLifespanInHours),
            'user_id' => $userToInvite->id,
            'group_id' => $group->id,
            'created_by' => Auth::id(),
        ]);

        $userToInvite->notify(new InvitationToGroup($group, $tokenLifespanInHours, $token));

        return back()->with('success', 'The user has been invited to join the group.');
    }

    /**
     * Confirm the received invitation
     */
    public function approveInvitation(string $token)
    {
        $groupUser = GroupUser::query()
            ->where('token', $token)
            ->first();

        $errorTitle = '';

        if (!$groupUser) {
            $errorTitle = 'The link is invalid';
        } else if ($groupUser->token_use_date) {
            $errorTitle = 'The link has already been used at ' . $groupUser->token_use_date->format('Y-m-d H:i:s');
        } else if ($groupUser->status === GroupUserStatus::APPROVED->value) {
            $errorTitle = 'The group `' . $groupUser->group->name . '` has already been approved.';
        } else if ($groupUser->token_expiry_date < Carbon::now()) {
            $errorTitle = 'The link expired at ' . $groupUser->token_expiry_date->format('Y-m-d H:i:s');
        }

        if ($errorTitle) {
            return \inertia('Error', compact('errorTitle'));
        }

        $groupUser->update([
            'status' => GroupUserStatus::APPROVED->value,
            'token_use_date' => Carbon::now(),
        ]);

        $adminUser = $groupUser->adminUser;

        $adminUser->notify(new InvitationApproved($groupUser->group, $groupUser->user));

        return redirect(route('group.profile', $groupUser->group->slug))->with('success', 'You have been accepted into the "'.$groupUser->group->name.'" group.');
    }

    public function joinGroup(Group $group)
    {
        $user = \request()->user();
        $status = GroupUserStatus::APPROVED->value;
        $success = 'You have joined the "'.$group->name.'" group.';

        if (!$group->auto_approval) {
            $status = GroupUserStatus::PENDING->value;
            $success = 'Your request has been accepted. You will be notified as soon as your request is approved.';

            Notification::send($group->adminUsers, new RequestToJoinGroup($group, $user));
        }

        GroupUser::create([
            'status' => $status,
            'role' => GroupUserRole::USER->value,
            'user_id' => $user->id,
            'group_id' => $group->id,
            'created_by' => $user->id,
        ]);

        return back()->with('success', $success);
    }

    /**
     * Process the received request (approve or reject)
     */
    public function processRequest(Request $request, Group $group)
    {
        $this->ensureAdmin($group);

        $data = $request->validate([
            'user_id' => ['required'],
            'action' => ['required', 'string']
        ]);

        $groupUser = GroupUser::where('user_id', $data['user_id'])
            ->where('group_id', $group->id)
            ->where('status', GroupUserStatus::PENDING->value)
            ->first();

        if ($groupUser) {
            $approved = false;

            if ($data['action'] === 'approve') {
                $approved = true;
                $groupUser->update(['status' => GroupUserStatus::APPROVED->value]);
            } else {
                $groupUser->update(['status' => GroupUserStatus::REJECTED->value]);
            }

            $user = $groupUser->user;

            $user->notify(new RequestApproved($groupUser->group, $user, $approved));
        }

        return back();
    }

    /**
     * Remove a user from a group
     */
    public function removeUser(Request $request, Group $group)
    {
        $this->ensureAdmin($group);

        $data = $request->validate([
            'user_id' => ['required'],
        ]);

        $userId = $data['user_id'];

        if ($group->isOwner($userId)) {
            return response("The owner of the group cannot be removed.", 403);
        }

        $groupUser = GroupUser::where('user_id', $userId)
            ->where('group_id', $group->id)
            ->first();

        if ($groupUser) {
            $groupUser->delete();
            $groupUser->user->notify(new UserRemovedFromGroup($group));
        }

        return back();
    }

    /**
     * Change the role of the resulting user
     */
    public function changeRole(Request $request, Group $group)
    {
        $this->ensureAdmin($group);

        $data = $request->validate([
            'user_id' => ['required'],
            'role' => ['required', Rule::enum(GroupUserRole::class)]
        ]);

        $userId = $data['user_id'];

        if ($group->isOwner($userId)) {
            return response("You cannot change the group owner role.", 403);
        }

        $groupUser = GroupUser::where('user_id', $userId)
            ->where('group_id', $group->id)
            ->first();

        if ($groupUser) {
            $groupUser->update(['role' => $data['role']]);
            $groupUser->user->notify(new RoleChanged($group, $data['role']));
        }

        return back();
    }

    private function ensureAdmin(Group $group)
    {
        if (!$group->isAdmin(Auth::id())) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

}
