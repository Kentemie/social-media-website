<?php

namespace App\Http\Controllers;

use App\Http\Enums\GroupUserRole;
use App\Http\Enums\GroupUserStatus;
use App\Http\Requests\InviteUsersRequest;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\GroupUser;
use App\Notifications\InvitationApproved;
use App\Notifications\InvitationToGroup;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

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
    public function show(Group $group): Response
    {
        $group->load('currentUserGroup');

        return Inertia::render('Group/View', [
            'success' => session('success'),
            'group' => new GroupResource($group),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        //
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
        if (!$group->isAdmin(Auth::id())) {
            return response('You do not have permission to perform this action.', 403);
        }

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
}
