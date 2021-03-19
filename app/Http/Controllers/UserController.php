<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DateTime;

use function strcmp;

class UserController extends Controller
{
    private const EMAIL_VALIDATION_RULE = 'required|email|max:128';
    private const PASSWORD_VALIDATION_RULE = 'required|max:255';

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request) : JsonResponse
    {
        $this->validate($request, [
            'first_name' => 'required|max:64',
            'last_name'  => 'required|max:64',
            'phone'      => 'required|max:16',
            'email'      => self::EMAIL_VALIDATION_RULE,
            'password'   => self::PASSWORD_VALIDATION_RULE,
        ]);

        $user = User::where('email', '=', $request->post('email'))->first();
        if ($user instanceof User) {
            return response()->json([
                'error' => 'Email is already taken',
            ], 400);
        }

        User::create([
            'first_name' => $request->post('first_name'),
            'last_name' => $request->post('last_name'),
            'phone' => $request->post('phone'),
            'email' => $request->post('email'),
            'password' => Hash::make($request->post('password')),
        ]);

        return response()->json([
        ], 200);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function signIn(Request $request) : JsonResponse
    {
        $this->validate($request, [
            'email' => self::EMAIL_VALIDATION_RULE,
            'password' => self::PASSWORD_VALIDATION_RULE,
        ]);

        $error = function () : JsonResponse {
            return response()->json([
                'error' => 'Unable to find user or wrong password',
            ], 403);
        };

        $user = User::where('email', '=', $request->post('email'))->first();
        if (!($user instanceof User)) {
            return $error();
        }

        if (!Hash::check($request->post('password'), $user->password)) {
            return $error();
        }

        $user->access_token = Str::random(32);
        $user->access_token_created_at = new DateTime('now');
        $user->save();

        return response()->json([
            'access_token' => $user->access_token,
        ], 200);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function generatePasswordRecoveryToken(Request $request) : JsonResponse
    {
        $this->validate($request, [
            'email' => self::EMAIL_VALIDATION_RULE,
        ]);

        $user = User::where('email', '=', $request->post('email'))->first();
        if (!($user instanceof User)) {
            return response()->json([
                'error' => 'Unable to find user',
            ], 400);
        }

        $user->password_recovery_token = Str::random(32);
        $user->password_recovery_token_created_at = new DateTime('now');
        $user->save();

        return response()->json([
        ], 200);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function changePassword(Request $request) : JsonResponse
    {
        $this->validate($request, [
            'email'        => self::EMAIL_VALIDATION_RULE,
            'token'        => 'required|max:32',
            'new_password' => self::PASSWORD_VALIDATION_RULE,
        ]);

        $error = function () : JsonResponse {
            return response()->json([
                'error' => 'Unable to find user or wrong token',
            ], 403);
        };

        $user = User::where('email', '=', $request->post('email'))->first();
        if (!($user instanceof User)) {
            return $error();
        }

        if (null === $user->password_recovery_token) {
            return $error();
        }

        if (0 !== strcmp($user->password_recovery_token, $request->post('token'))) {
            return $error();
        }

        $user->password = Hash::make($request->post('new_password'));
        $user->password_recovery_token = null;
        $user->password_recovery_token_created_at = null;
        $user->save();

        return response()->json([
        ], 200);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createCompany(Request $request) : JsonResponse
    {
        $this->validate($request, [
            'title'       => 'required|max:128',
            'phone'       => 'required|max:16',
            'description' => 'required|max:1024',
        ]);

        $user = $request->user();
        $user->companies()->create($request->post());

        return response()->json([
        ], 201);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function readCompanies(Request $request) : JsonResponse
    {
        $user = $request->user();
        $companies = $user->companies()->get();

        return response()->json($companies, 200);
    }
}
