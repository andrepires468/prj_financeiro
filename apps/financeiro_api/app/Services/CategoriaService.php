<?php


namespace App\Services;

use App\Helpers\Pagination;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaService extends Service
{

  public function __construct()
  {
    parent::__construct();
  }

  public function List(Request $request)
  {

    $result = Categoria::select()
      ->orderBy('CategoriaNome')
      ->get();

    return $result;

  }

  public function Get(Request $request)
  {

    throw new \Exception('método não implementado');

  }

  public function Post(Request $request)
  {
    throw new \Exception('método não implementado');

  }

  public function Put(Request $request)
  {

    if(!$request->Id)
      throw new \Exception('Id não informado');

    if(!$request->CategoriaNome)
      throw new \Exception('CategoriaNome não informado');

    if(!$request->IconColor)
      throw new \Exception('IconColor não informado');

    $categoria = Categoria::select()->where('Id', '=', $request->Id)->first();

    $categoria->CategoriaNome = $request->CategoriaNome;
    $categoria->IconColor = ltrim($request->IconColor, '#');
    if(array_key_exists('ExibirGraficos', $request->all()))
      $categoria->ExibirGraficos = filter_var($request->ExibirGraficos, FILTER_VALIDATE_BOOLEAN);
    $categoria->save();

  }

  public function Delete(Request $request)
  {

    throw new \Exception('método não implementado');

  }

}
