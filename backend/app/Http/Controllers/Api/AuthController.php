<?php

namespace App\Http\Controllers\Api;

use App\Domain\Member\Models\Member;
use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\MemberResource;
use App\Http\Resources\OrganisationResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'data'    => new UserResource($user),
            'token'   => $token,
            'message' => 'Registration successful',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Account is disabled.'], 403);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('api')->plainTextToken;

        $response = [
            'data'  => new UserResource($user),
            'token' => $token,
        ];

        // Resolve the organisation context from the subdomain (primary, via route model binding)
        // or fall back to an explicit 'organisation' body param (seo_name or UUID).
        $org = $request->route('subdomain');

        if (! $org && $request->filled('organisation')) {
            $org = Organisation::where('seo_name', $request->organisation)
                ->orWhere('id', $request->organisation)
                ->first();
        }

        if ($org) {
            $member = Member::where('user_id', $user->id)
                ->where('organisation_id', $org->id)
                ->first();

            $response['organisation'] = new OrganisationResource($org);
            $response['member']       = $member ? new MemberResource($member) : null;
        }

        return response()->json($response);
    }

    public function organisations(Request $request): JsonResponse
    {
        $members = Member::with('organisation')
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->get();

        $data = $members->map(fn (Member $m) => [
            'organisation' => new OrganisationResource($m->organisation),
            'member'       => new MemberResource($m),
        ]);

        return response()->json(['data' => $data]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out from all devices']);
    }
}
