<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Categoria extends Model
{
  protected $connection = 'home';
  protected $table = 'Categoria';
  protected $primaryKey = 'Id';
  public $timestamps = false;

  protected $fillable = [
    'Id' => 'int',
    'CategoriaNome' => 'required|string|max:200',
    'IconTag' => 'required|string|max:40',
    'IconColor' => 'required|string|max:6',
  ];

}
