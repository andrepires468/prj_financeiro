<?php


namespace App\Services;

use App\Helpers\Pagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Extrato;

class FinanceiroService extends Service
{

  public function __construct()
  {
    parent::__construct();
  }


  public function CalendarioExtrato(Request $request)
  {

    // Definindo o mês e ano de início
    $mes_inicio = 1;
    $ano_inicio = 2024;

    // Obtendo o mês e ano atuais
    $mes_atual = date('n'); // Retorna o mês sem o zero à esquerda
    $ano_atual = date('Y'); // Retorna o ano com quatro dígitos

    // Array para armazenar os meses/anos
    $meses_anos = array();

    // Loop para gerar os meses/anos
    for($ano = $ano_inicio ; $ano <= $ano_atual ; $ano++) {
      // Define o mês inicial do primeiro ano e o mês final do último ano
      $mes_inicio_loop = ($ano == $ano_inicio) ? $mes_inicio : 1;
      $mes_final_loop = ($ano == $ano_atual) ? $mes_atual : 12;

      for($mes = $mes_inicio_loop ; $mes <= $mes_final_loop ; $mes++) {
        $meses_anos[] = sprintf("%02d/%d", $mes, $ano); // Formatação do mês/ano (com zero à esquerda se necessário)
      }
    }

    return array_reverse($meses_anos);

  }

  public function ListaExtrato(Request $request)
  {

    if(!$request->mes)
      throw new \Exception('mes não passado');

    if(!$request->ano)
      throw new \Exception('ano não passado');

    $mes = $request->mes;
    $ano = $request->ano;

    // Obtém a data do primeiro dia do mês
    $data_inicio = date('Y-m-01', strtotime("first day of {$ano}-{$mes}"));

    // Obtém a data do último dia do mês
    $data_fim = date('Y-m-t', strtotime("last day of {$ano}-{$mes}"));


    $model = Extrato::selectRaw("
      Extrato.*,
      (case when Extrato.Valor > 0 then 'entrada' else 'saida' end) Tipo,
      BancoExtrato.BancoNome,
      BancoExtrato.IdUsuario,
      BancoExtrato.Default BancoDefault,
      Categoria.CategoriaNome,
      Categoria.IconTag,
      Categoria.IconColor")
      ->join('BancoExtrato', 'BancoExtrato.Id', '=', 'Extrato.IdBancoExtrato')
      ->leftJoin('Categoria', 'Categoria.Id', '=', 'Extrato.IdCategoria')
      ->whereBetween('Extrato.Data', [$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59'])
      ->where('BancoExtrato.IdUsuario', '=', $this->idUsuario)
      ->orderByDesc('Extrato.Data');

    if($request->page && $request->pageSize) {
      $count = $model->count();
      $items = $model->skip(($request->page - 1) * $request->pageSize)->take($request->pageSize)->get();
      return Pagination::Paginate($items, $count, $request->page, $request->pageSize);
    }

    return $model->get();

  }

  public function UpdateExtrato(Request $request)
  {

    if(!$request->IdExtrato)
      throw new \Exception('IdExtrato não passado');

    $extrato = Extrato::select()
      ->where('Id', '=', $request->IdExtrato)
      ->first();

    if(!$extrato)
      throw new \Exception('Extrato não encontrado');

    $extrato->IdCategoria = $request->IdCategoria ?? null;
    $extrato->DescricaoPersonalizada = $request->DescricaoPersonalizada ?? null;
    $extrato->save();


  }


}
