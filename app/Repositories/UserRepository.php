<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class UserRepository
{

    /**
     * Creates a new user from the given data
     *
     * @param array $data
     *
     * @return User
     */
    public function createFromArray(array $data) : User
    {
        return User::create($data);
    }

    /**
     * Creates a company for the given user from the given data
     *
     * @param User $user
     * @param array $data
     *
     * @return Company
     */
    public function createCompanyFromArray(User $user, array $data) : Company
    {
        return $user->companies()->create($data);
    }

    /**
     * Updates the given user from the given data
     *
     * @param User $user
     * @param array $data
     *
     * @return void
     */
    public function updateFromArray(User $user, array $data) : void
    {
        $user->update($data);
    }

    /**
     * Finds an user by the given email
     *
     * @param string $email
     *
     * @return null|User
     */
    public function findByEmail(string $email) : ?User
    {
        return User::where('email', '=', $email)->first();
    }

    /**
     * Finds an user by the given access token
     *
     * @param string $accessToken
     *
     * @return null|User
     */
    public function findByAccessToken(string $accessToken) : ?User
    {
        return User::where('access_token', '=', $accessToken)->first();
    }

    /**
     * Gets all companies of the given user
     *
     * @param User $user
     *
     * @return Collection
     */
    public function getCompanies(User $user) : Collection
    {
        return $user->companies()->get();
    }
}
