<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
        'access_token',
        'access_token_created_at',
        'password_recovery_token',
        'password_recovery_token_created_at',
        'last_login_at',
        'last_password_change_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'access_token',
        'access_token_created_at',
        'password_recovery_token',
        'password_recovery_token_created_at',
        'last_login_at',
        'last_password_change_at',
    ];

    /**
     * Gets the user companies
     *
     * @return Company[]
     */
    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
