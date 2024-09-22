<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Fechamento extends Model
{
  protected $connection = 'home';
  protected $table = 'Fechamento';
  protected $primaryKey = 'Id';
  public $timestamps = false;

  protected $fillable = [
    'Id' => 'int',
    'IdBancoExtrato' => 'required|int',
    'ValorFechado' => 'required|numeric',
    'DataBase' => 'required|date_format:Y-m-d',
    'AlteradoEm' => 'required|date_format:Y-m-d H:i:s',
  ];

  protected $hidden = [];

}
