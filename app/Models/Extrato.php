<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Extrato extends Model
{
  protected $connection = 'home';
  protected $table = 'Extrato';
  protected $primaryKey = 'Id';
  public $timestamps = false;

  protected $fillable = [
    'Id' => 'int',
    'Data' => 'required|date_format:Y-m-d',
    'Valor' => 'required|numeric',
    'Identificador' => 'required|string|max:40',
    'Descricao' => 'required|string|max:420',
    'IdTipoExtrato' => 'required|int',
    'IdBancoExtrato' => 'required|int',
    'IdCategoria' => 'int',
    'DescricaoPersonalizada' => 'string|max:200',
    'CriadoEm' => 'date_format:Y-m-d H:i:s',
  ];

  protected $hidden = [
    'CriadoEm',
  ];

}
