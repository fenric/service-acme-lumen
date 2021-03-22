<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\InvalidPasswordException;
use App\Exceptions\InvalidPasswordRecoveryTokenException;
use App\Exceptions\UserEmailAlreadyExistsException;
use App\Exceptions\UserNotFoundException;
use App\Services\UserManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{

    /**
     * @var UserManager
     */
    private UserManager $userManager;

    /**
     * Constructor of the class
     *
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request) : JsonResponse
    {
        try {
            $user = $this->userManager->createFromArray($request->all());
        } catch (UserEmailAlreadyExistsException $e) {
            return new JsonResponse([
                'error' => 'Email is already exists',
            ], 400);
        }

        return new JsonResponse($user, 200);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function signIn(Request $request) : JsonResponse
    {
        try {
            $user = $this->userManager->authenticateByArray($request->all());
        } catch (UserNotFoundException|InvalidPasswordException $e) {
            return new JsonResponse([
                'error' => 'Unable to find user or wrong password',
            ], 403);
        }

        return new JsonResponse([
            'access_token' => $user->access_token,
        ], 200);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function recoverPassword(Request $request) : JsonResponse
    {
        $this->validate($request, [
            'email' => UserManager::EMAIL_VALIDATION_RULE,
        ]);

        try {
            $user = $this->userManager->getByEmail($request->input('email'));
            $this->userManager->resetPasswordRecoveryToken($user);
        } catch (UserNotFoundException $e) {
            return new JsonResponse([
                'error' => 'Unable to find user',
            ], 400);
        }

        return new JsonResponse([], 200);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function changePassword(Request $request) : JsonResponse
    {
        $this->validate($request, [
            'email'        => UserManager::EMAIL_VALIDATION_RULE,
            'token'        => 'required|max:32',
            'new_password' => UserManager::PASSWORD_VALIDATION_RULE,
        ]);

        try {
            $user = $this->userManager->getByEmail($request->input('email'));
            $this->userManager->verifyAccessToken($user, $request->input('token'));
            $this->userManager->changePassword($user, $request->input('new_password'));
        } catch (UserNotFoundException|InvalidPasswordRecoveryTokenException $e) {
            return new JsonResponse([
                'error' => 'Unable to find user or wrong token',
            ], 403);
        }

        return new JsonResponse([
        ], 200);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createCompany(Request $request) : JsonResponse
    {
        $company = $this->userManager->createCompanyFromArray($request->user(), $request->all());

        return new JsonResponse($company, 201);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listCompanies(Request $request) : JsonResponse
    {
        $companies = $this->userManager->getUserRepository()->getCompanies($request->user());

        return new JsonResponse($companies, 200);
    }
}
