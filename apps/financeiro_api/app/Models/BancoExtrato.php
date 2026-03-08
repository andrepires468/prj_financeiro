<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BancoExtrato extends Model
{
  protected $connection = 'home';
  protected $table = 'BancoExtrato';
  protected $primaryKey = 'Id';
  public $timestamps = false;

  protected $fillable = [
    'Id' => 'int',
    'BancoNome' => 'required|string|max:200',
    'IdUsuario' => 'required|int',
    'Default' => 'required|boolean',
    'GmailUser' => 'string',
    'GmailPass' => 'string',
  ];

  protected $hidden = [];

}
