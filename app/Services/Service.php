<?php


namespace App\Services;

use DateTime;

class Service
{

  public $date;

  public function __construct()
  {

    $this->date = new DateTime();

  }

}
