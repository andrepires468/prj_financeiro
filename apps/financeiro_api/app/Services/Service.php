<?php


namespace App\Services;

use DateTime;
use Illuminate\Support\Facades\DB;

class Service
{

  public $date;

  // usuario
  public $idUsuario = null;
  public $usuarioEmail = null;
  public $usuarioNome = null;

  public function __construct()
  {

//    $result = DB::connection('home')->select('select current_timestamp() datetime');
//    $result[0]->datetime

    $this->date = new DateTime();
    //$this->date->format("Y-m-d H:i:s"); // format
    //$this->date->add(new DateInterval('PT1H'))->format('Y-m-d H:i:s'); // add 1 hour

    if($usuarioLogado = AuthService::getUsuario()) {
      $this->idUsuario = $usuarioLogado->IdUsuario;
      $this->idBancoExtrato = $usuarioLogado->IdBancoExtrato;
      $this->usuarioEmail = $usuarioLogado->Email;
      $this->usuarioNome = $usuarioLogado->Nome;

    }

  }

  protected function countModel($model): int
  {

    $query = $model->toSql();
    $bindings = $model->getBindings();

    $fullQuery = vsprintf(str_replace('?', '%s', $query), array_map(function($value) {
      return is_numeric($value) ? $value : "'" . addslashes($value) . "'";
    }, $bindings));

    $result = DB::connection('lms')->select("select count(*) total from ({$fullQuery}) sub");

    if(isset($result[0]))
      return (int)$result[0]->total;

    return 0;

  }

  protected function debugModel($model) : string
  {

    $query = $model->toSql();
    $bindings = $model->getBindings();

    return vsprintf(str_replace('?', '%s', $query), array_map(function($value) {
      return is_numeric($value) ? $value : "'" . addslashes($value) . "'";
    }, $bindings));

  }

}
