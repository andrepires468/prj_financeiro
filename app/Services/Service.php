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

}
