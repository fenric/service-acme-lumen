<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InvalidPasswordException;
use App\Exceptions\InvalidPasswordRecoveryTokenException;
use App\Exceptions\UserEmailAlreadyExistsException;
use App\Exceptions\UserNotFoundException;
use App\Models\Company;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use DateTime;

use function strcmp;

final class UserManager
{
    public const EMAIL_VALIDATION_RULE = 'required|email|max:128';
    public const PASSWORD_VALIDATION_RULE = 'required|max:255';

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * Constructor of the class
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Gets the manager repository
     *
     * @return UserRepository
     */
    public function getUserRepository() : UserRepository
    {
        return $this->userRepository;
    }

    /**
     * Gets an user by the given email
     *
     * @param string $email
     *
     * @return User
     *
     * @throws UserNotFoundException
     *         If an user with the given email wasn't found.
     */
    public function getByEmail(string $email) : User
    {
        $user = $this->userRepository->findByEmail($email);
        if (null === $user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    /**
     * Gets an user by the given access token
     *
     * @param string $accessToken
     *
     * @return User
     *
     * @throws UserNotFoundException
     *         If an user with the given access token wasn't found.
     */
    public function getByAccessToken(string $accessToken) : User
    {
        $user = $this->userRepository->findByAccessToken($accessToken);
        if (null === $user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    /**
     * Creates a new user from the given data
     *
     * @param array $data
     *
     * @return User
     *
     * @throws ValidationException
     *         If the given data isn't valid.
     *
     * @throws UserEmailAlreadyExistsException
     *         if the given email already is exists.
     *
     * @todo Better to use DTO instead of arrays...
     */
    public function createFromArray(array $data) : User
    {
        $validator = Validator::make($data, [
            'first_name' => 'required|max:64',
            'last_name'  => 'required|max:64',
            'phone'      => 'required|max:16',
            'email'      => self::EMAIL_VALIDATION_RULE,
            'password'   => self::PASSWORD_VALIDATION_RULE,
        ]);

        $validator->validate();

        $user = $this->userRepository->findByEmail($data['email']);
        if ($user instanceof User) {
            throw new UserEmailAlreadyExistsException();
        }

        $data['password'] = Hash::make($data['password']);

        return $this->userRepository->createFromArray([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
    }

    /**
     * Creates a company for the given user from the given data
     *
     * @param User $user
     * @param array $data
     *
     * @return Company
     *
     * @throws ValidationException
     *         If the given data isn't valid.
     *
     * @todo Better to use DTO instead of arrays...
     */
    public function createCompanyFromArray(User $user, array $data) : Company
    {
        $validator = Validator::make($data, [
            'title'       => 'required|max:128',
            'phone'       => 'required|max:16',
            'description' => 'required|max:1024',
        ]);

        $validator->validate();

        return $this->userRepository->createCompanyFromArray($user, [
            'title' => $data['title'],
            'phone' => $data['phone'],
            'description' => $data['description'],
        ]);
    }

    /**
     * Tries to authenticate an user from the given data
     *
     * @param array $data
     *
     * @return User
     *
     * @throws ValidationException
     *         If the given data isn't valid.
     *
     * @throws InvalidPasswordException
     *         If the given password isn't correct.
     *
     * @todo Better to use DTO instead of arrays...
     */
    public function authenticateByArray(array $data) : User
    {
        $validator = Validator::make($data, [
            'email' => self::EMAIL_VALIDATION_RULE,
            'password' => self::PASSWORD_VALIDATION_RULE,
        ]);

        $validator->validate();

        $user = $this->userRepository->findByEmail($data['email']);
        if (null === $user) {
            throw new UserNotFoundException();
        }

        if (!Hash::check($data['password'], $user->password)) {
            throw new InvalidPasswordException();
        }

        $this->userRepository->updateFromArray($user, [
            'access_token' => Str::random(32),
            'access_token_created_at' => new DateTime('now'),
            'last_login_at' => new DateTime('now'),
        ]);

        return $user;
    }

    /**
     * Resets a password recovery token for the given user
     *
     * @param User $user
     *
     * @return void
     */
    public function resetPasswordRecoveryToken(User $user) : void
    {
        $this->userRepository->updateFromArray($user, [
            'password_recovery_token' => Str::random(32),
            'password_recovery_token_created_at' => new DateTime('now'),
        ]);
    }

    /**
     * Verifies the access token for the given user
     *
     * @param User $user
     * @param string $accessToken
     *
     * @return void
     *
     * @throws InvalidPasswordRecoveryTokenException
     *         If the given token isn't valid.
     */
    public function verifyAccessToken(User $user, string $accessToken) : void
    {
        if (null === $user->password_recovery_token) {
            throw new InvalidPasswordRecoveryTokenException();
        }

        if (0 !== strcmp($user->password_recovery_token, $accessToken)) {
            throw new InvalidPasswordRecoveryTokenException();
        }
    }

    /**
     * Sets the given password to the given user
     *
     * @param User $user
     * @param string $newPassword
     *
     * @return void
     */
    public function changePassword(User $user, string $newPassword) : void
    {
        $this->userRepository->updateFromArray($user, [
            'password' => Hash::make($newPassword),
            'password_recovery_token' => null,
            'password_recovery_token_created_at' => null,
            'last_password_change_at' => new DateTime('now'),
        ]);
    }
}
