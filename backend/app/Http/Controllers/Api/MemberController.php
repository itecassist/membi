<?php

namespace App\Http\Controllers\Api;

use App\Domain\Member\Models\Member;
use App\Domain\Member\Services\MemberService;
use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MemberController extends Controller
{
    public function __construct(private readonly MemberService $service) {}

    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('member.read'), 403);

        return MemberResource::collection(
            $this->service->listForOrganisation($organisation)
        );
    }

    public function store(StoreMemberRequest $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('member.create'), 403);

        $member = $this->service->create($organisation, $request->validated());

        return response()->json([
            'data'    => new MemberResource($member),
            'message' => 'Member created',
        ], 201);
    }

    public function show(Organisation $organisation, Member $member): JsonResponse
    {
        abort_unless($member->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('member.read'), 403);

        return response()->json([
            'data' => new MemberResource($member),
        ]);
    }

    public function update(UpdateMemberRequest $request, Organisation $organisation, Member $member): JsonResponse
    {
        abort_unless($member->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('member.update'), 403);

        $member = $this->service->update($member, $request->validated());

        return response()->json([
            'data'    => new MemberResource($member),
            'message' => 'Member updated',
        ]);
    }

    public function destroy(Organisation $organisation, Member $member): JsonResponse
    {
        abort_unless($member->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('member.delete'), 403);

        $this->service->delete($member);

        return response()->json(['message' => 'Member deleted']);
    }
}
