<?php

namespace App\Http\Controllers\Api;

use App\Domain\Member\Models\Group;
use App\Domain\Member\Services\GroupService;
use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Group\StoreGroupRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\MemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GroupController extends Controller
{
    public function __construct(private readonly GroupService $service) {}

    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('group.read'), 403);

        return GroupResource::collection(
            $this->service->listForOrganisation($organisation)
        );
    }

    public function store(StoreGroupRequest $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('group.create'), 403);

        $group = $this->service->create($organisation, $request->validated());

        return response()->json([
            'data'    => new GroupResource($group),
            'message' => 'Group created',
        ], 201);
    }

    public function show(Organisation $organisation, Group $group): JsonResponse
    {
        abort_unless($group->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('group.read'), 403);

        $group->loadCount('members');

        return response()->json([
            'data' => new GroupResource($group),
        ]);
    }

    public function update(StoreGroupRequest $request, Organisation $organisation, Group $group): JsonResponse
    {
        abort_unless($group->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('group.update'), 403);

        $group = $this->service->update($group, $request->validated());

        return response()->json([
            'data'    => new GroupResource($group),
            'message' => 'Group updated',
        ]);
    }

    public function destroy(Organisation $organisation, Group $group): JsonResponse
    {
        abort_unless($group->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('group.delete'), 403);

        $this->service->delete($group);

        return response()->json(['message' => 'Group deleted']);
    }

    public function members(Organisation $organisation, Group $group): AnonymousResourceCollection
    {
        abort_unless($group->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('group.read'), 403);

        return MemberResource::collection($group->members()->paginate(50));
    }

    public function addMember(Request $request, Organisation $organisation, Group $group): JsonResponse
    {
        abort_unless($group->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('group.update'), 403);

        $request->validate(['member_id' => 'required|uuid|exists:members,id', 'is_admin' => 'boolean']);

        $this->service->addMember($group, $request->member_id, $request->boolean('is_admin'));

        return response()->json(['message' => 'Member added to group']);
    }

    public function removeMember(Organisation $organisation, Group $group, string $memberId): JsonResponse
    {
        abort_unless($group->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('group.update'), 403);

        $this->service->removeMember($group, $memberId);

        return response()->json(['message' => 'Member removed from group']);
    }
}
