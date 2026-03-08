<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
  use Authenticatable, Authorizable;

  protected $connection = 'home';
  protected $table = 'Usuario';
  protected $primaryKey = 'Id';
  public $timestamps = false;

  protected $fillable = [
    'Id',
    'Nome',
    'Email',
    'Password'
  ];

  protected $hidden = [
    'Password',
  ];


  public function getJWTIdentifier()
  {
    return $this->getKey();
  }

  public function getJWTCustomClaims()
  {
    return [];
  }


}
