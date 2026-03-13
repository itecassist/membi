<?php

namespace App\Http\Controllers\Api;

use App\Domain\Communication\Models\EmailTemplate;
use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmailTemplateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmailTemplateController extends Controller
{
    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('communication.manage'), 403);

        return EmailTemplateResource::collection(
            EmailTemplate::where('organisation_id', $organisation->id)->orderBy('name')->get()
        );
    }

    public function store(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('communication.manage'), 403);

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'header'  => ['nullable', 'string'],
            'footer'  => ['nullable', 'string'],
        ]);

        $template = EmailTemplate::create(array_merge($validated, ['organisation_id' => $organisation->id]));

        return response()->json(['data' => new EmailTemplateResource($template), 'message' => 'Email template created.'], 201);
    }

    public function show(Organisation $organisation, EmailTemplate $emailTemplate): JsonResponse
    {
        abort_unless($emailTemplate->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('communication.manage'), 403);

        return response()->json(['data' => new EmailTemplateResource($emailTemplate)]);
    }

    public function update(Request $request, Organisation $organisation, EmailTemplate $emailTemplate): JsonResponse
    {
        abort_unless($emailTemplate->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('communication.manage'), 403);

        $validated = $request->validate([
            'name'    => ['sometimes', 'string', 'max:255'],
            'subject' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'header'  => ['nullable', 'string'],
            'footer'  => ['nullable', 'string'],
        ]);

        $emailTemplate->update($validated);

        return response()->json(['data' => new EmailTemplateResource($emailTemplate->fresh()), 'message' => 'Email template updated.']);
    }

    public function destroy(Organisation $organisation, EmailTemplate $emailTemplate): JsonResponse
    {
        abort_unless($emailTemplate->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('communication.manage'), 403);

        $emailTemplate->delete();

        return response()->json(['message' => 'Email template deleted.']);
    }
}
