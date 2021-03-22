<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Exceptions\UserNotFoundException;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserManager;
use App\Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UserManagerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @return void
     */
    public function testGetByEmail() : void
    {
        $manager = $this->app->get(UserManager::class);

        $user = User::factory()->create();
        $found = $manager->getByEmail($user->email);
        $this->assertSame($user->id, $found->id);
    }

    /**
     * @return void
     */
    public function testGetByUnknownEmail() : void
    {
        $manager = $this->app->get(UserManager::class);

        $this->expectException(UserNotFoundException::class);
        $manager->getByEmail('foo@bar');
    }

    /**
     * @return void
     */
    public function testGetByAccessToken() : void
    {
        $manager = $this->app->get(UserManager::class);

        $user = User::factory()->create([
            'access_token' => Str::random(32),
        ]);

        $found = $manager->getByAccessToken($user->access_token);
        $this->assertSame($user->id, $found->id);
    }

    /**
     * @return void
     */
    public function testGetByUnknownAccessToken() : void
    {
        $manager = $this->app->get(UserManager::class);

        $this->expectException(UserNotFoundException::class);
        $manager->getByAccessToken('foo');
    }

    /**
     * @return void
     */
    public function testCreateFromArray() : void
    {
        $manager = $this->app->get(UserManager::class);

        $fields = [
            'first_name' => 'foo',
            'last_name' => 'bar',
            'phone' => '1234567890',
            'email' => 'foo@bar',
            'password' => 'Qwe@123',
        ];

        $user = $manager->createFromArray($fields);

        $this->assertSame($fields['first_name'], $user->first_name);
        $this->assertSame($fields['last_name'], $user->last_name);
        $this->assertSame($fields['phone'], $user->phone);
        $this->assertSame($fields['email'], $user->email);
        $this->assertTrue(Hash::check($fields['password'], $user->password));
    }

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @dataProvider invalidFirstNameProvider
     */
    public function testCreateFromArrayWithInvalidFirstName($value) : void
    {
        $manager = $this->app->get(UserManager::class);

        $this->expectException(ValidationException::class);

        $manager->createFromArray([
            'first_name' => $value,
            'last_name' => 'bar',
            'phone' => '1234567890',
            'email' => 'foo@bar',
            'password' => 'Qwe@123',
        ]);
    }

    /**
     * @return array
     */
    public function invalidFirstNameProvider() : array
    {
        return [
            [null],
            [''],
            [Str::random(65)],
        ];
    }

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @dataProvider invalidLastNameProvider
     */
    public function testCreateFromArrayWithInvalidLastName($value) : void
    {
        $manager = $this->app->get(UserManager::class);

        $this->expectException(ValidationException::class);

        $manager->createFromArray([
            'first_name' => 'foo',
            'last_name' => $value,
            'phone' => '1234567890',
            'email' => 'foo@bar',
            'password' => 'Qwe@123',
        ]);
    }

    /**
     * @return array
     */
    public function invalidLastNameProvider() : array
    {
        return [
            [null],
            [''],
            [Str::random(65)],
        ];
    }

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @dataProvider invalidPhoneProvider
     */
    public function testCreateFromArrayWithInvalidPhone($value) : void
    {
        $manager = $this->app->get(UserManager::class);

        $this->expectException(ValidationException::class);

        $manager->createFromArray([
            'first_name' => 'foo',
            'last_name' => 'bar',
            'phone' => $value,
            'email' => 'foo@bar',
            'password' => 'Qwe@123',
        ]);
    }

    /**
     * @return array
     */
    public function invalidPhoneProvider() : array
    {
        return [
            [null],
            [''],
            [Str::random(17)],
        ];
    }

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @dataProvider invalidEmailProvider
     */
    public function testCreateFromArrayWithInvalidEmail($value) : void
    {
        $manager = $this->app->get(UserManager::class);

        $this->expectException(ValidationException::class);

        $manager->createFromArray([
            'first_name' => 'foo',
            'last_name' => 'bar',
            'phone' => '1234567890',
            'email' => $value,
            'password' => 'Qwe@123',
        ]);
    }

    /**
     * @return array
     */
    public function invalidEmailProvider() : array
    {
        return [
            [null],
            [''],
            ['foo'],
        ];
    }

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @dataProvider invalidPasswordProvider
     */
    public function testCreateFromArrayWithInvalidPassword($value) : void
    {
        $manager = $this->app->get(UserManager::class);

        $this->expectException(ValidationException::class);

        $manager->createFromArray([
            'first_name' => 'foo',
            'last_name' => 'bar',
            'phone' => '1234567890',
            'email' => 'foo@bar',
            'password' => $value,
        ]);
    }

    /**
     * @return array
     */
    public function invalidPasswordProvider() : array
    {
        return [
            [null],
            [''],
            [Str::random(256)],
        ];
    }
}
