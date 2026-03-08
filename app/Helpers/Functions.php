<?php

namespace App\Helpers;

use DateTime;

class Functions
{

  public static function formatDate($dataString)
  {

    // Criar um objeto DateTime a partir da string de data
    $dataObjeto = DateTime::createFromFormat('d/m/Y', $dataString);

    if(!$dataObjeto)
      throw new \Exception('erro');

    // Formatar a data no novo formato
    return $dataObjeto->format('Y-m-d');

  }


  public static function getStackTrace($trace)
  {

    foreach($trace as $t) {

      if(isset($t['file'])) {

        $array = explode('\\', $t['file']);
        $class = str_replace(".php", "", end($array));

        if(str_contains($class, 'Controller')) {

          return [
            $class,
            $t['function'] ?? null
          ];

        }

      }

    }

    return [];
  }

  public static function gerarSenha($tamanho, $maiusculas, $minusculas, $numeros, $simbolos)
  {
    $ma = "ABCDEFGHIJKLMNOPQRSTUVYXWZ"; // $ma contem as letras maiúsculas
    $mi = "abcdefghijklmnopqrstuvyxwz"; // $mi contem as letras minusculas
    $nu = "0123456789"; // $nu contem os números
    $si = "!@#$%¨&*()_+="; // $si contem os símbolos

    $senha = "";

    if($maiusculas) {
      // se $maiusculas for "true", a variável $ma é embaralhada e adicionada para a variável $senha
      $senha .= str_shuffle($ma);
    }

    if($minusculas) {
      // se $minusculas for "true", a variável $mi é embaralhada e adicionada para a variável $senha
      $senha .= str_shuffle($mi);
    }

    if($numeros) {
      // se $numeros for "true", a variável $nu é embaralhada e adicionada para a variável $senha
      $senha .= str_shuffle($nu);
    }

    if($simbolos) {
      // se $simbolos for "true", a variável $si é embaralhada e adicionada para a variável $senha
      $senha .= str_shuffle($si);
    }

    // retorna a senha embaralhada com "str_shuffle" com o tamanho definido pela variável $tamanho
    return substr(str_shuffle($senha), 0, $tamanho);
  }


  public static function gerarGuid()
  {
    mt_srand((double)microtime() * 10000);
    $charid = strtoupper(md5(uniqid(rand(), true)));
    $hyphen = chr(45);
    $uuid = substr($charid, 0, 8) . $hyphen
      . substr($charid, 8, 4) . $hyphen
      . substr($charid, 12, 4) . $hyphen
      . substr($charid, 16, 4) . $hyphen
      . substr($charid, 20, 12);
    return $uuid;
  }

  public static function maskEmail($email)
  {

    $emailEx = explode("@", $email);

    $mask = "";
    for($i = 0 ; $i < strlen($emailEx[0]) - 4 ; $i++) {
      $mask .= "*";
    }

    return substr($emailEx[0], 0, 2) . $mask . substr($emailEx[0], strlen($emailEx[0]) - 2, 2) . "@" . $emailEx[1];

  }

  public static function arrayFilter($items, $fieldName, $desiredValue)
  {

    return array_values(array_filter($items, function($item) use ($fieldName, $desiredValue) {
      return $item[$fieldName] === $desiredValue;
    }));

  }

  public static function limparTexto($texto)
  {
    // Remover caracteres não alfanuméricos e converter para minúsculas

    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
    $texto = preg_replace('/[^a-zA-Z0-9\s]/', '', $texto);
    $texto = strtolower($texto);
    // Remover espaços extras
    $texto = trim(preg_replace('/\s+/', ' ', $texto));
    return $texto;
  }

  public static function tokenizarTexto($texto)
  {
    // Tokenização simples usando espaço em branco como delimitador
    $tokens = explode(' ', $texto);
    return $tokens;
  }

  public static function ultimasDatadaBase(int $qtdMeses)
  {

    $dates = [];

    // Data atual
    $currentDate = new \DateTime();

    // Itera pelos últimos 6 meses
    for($i = 0 ; $i < $qtdMeses ; $i++) {

      $currentDate = (new \DateTime())->modify("-$i month");

      $codigo_mes = (int)$currentDate->format('m');
      $data_inicio_mes = $currentDate->format('Y-m-01');
      $data_fim_mes = $currentDate->modify('last day of this month')->format('Y-m-d');

      $dates[] = [
        "codigo_mes" => $codigo_mes,
        "data_inicio_mes" => $data_inicio_mes,
        "data_fim_mes" => $data_fim_mes
      ];


    }

    return $dates;


  }


  public static function getMesNome($monthId, $short = false)
  {
    // Definir o array de meses
    $months = [
      ['Id' => 1, 'Nome' => 'Janeiro', 'NomeCurto' => 'Jan'],
      ['Id' => 2, 'Nome' => 'Fevereiro', 'NomeCurto' => 'Fev'],
      ['Id' => 3, 'Nome' => 'Março', 'NomeCurto' => 'Mar'],
      ['Id' => 4, 'Nome' => 'Abril', 'NomeCurto' => 'Abr'],
      ['Id' => 5, 'Nome' => 'Maio', 'NomeCurto' => 'Mai'],
      ['Id' => 6, 'Nome' => 'Junho', 'NomeCurto' => 'Jun'],
      ['Id' => 7, 'Nome' => 'Julho', 'NomeCurto' => 'Jul'],
      ['Id' => 8, 'Nome' => 'Agosto', 'NomeCurto' => 'Ago'],
      ['Id' => 9, 'Nome' => 'Setembro', 'NomeCurto' => 'Set'],
      ['Id' => 10, 'Nome' => 'Outubro', 'NomeCurto' => 'Out'],
      ['Id' => 11, 'Nome' => 'Novembro', 'NomeCurto' => 'Nov'],
      ['Id' => 12, 'Nome' => 'Dezembro', 'NomeCurto' => 'Dez'],
    ];

    // Percorrer os meses e encontrar o que corresponde ao Id
    foreach($months as $month) {
      if($month['Id'] === $monthId) {
        return $short ? $month['NomeCurto'] : $month['Nome'];
      }
    }

    // Retornar null se o Id não for encontrado
    return null;
  }


}
