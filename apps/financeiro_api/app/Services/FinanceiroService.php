<?php


namespace App\Services;

use App\Helpers\Functions;
use App\Helpers\Pagination;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BancoExtrato;
use App\Models\Extrato;
use App\Models\FechamentoCategoria;
use Illuminate\Support\Facades\DB;

class FinanceiroService extends Service
{

  protected $processService;

  public function __construct(ProcessService $processService)
  {
    parent::__construct();
    $this->processService = $processService;
  }

  public function CalendarioExtrato(Request $request)
  {

    $mes_inicio = env('MES_INICIO');
    $ano_inicio = env('ANO_INICIO');

    if($this->idBancoExtrato) {
      $banco = BancoExtrato::select('MesInicio', 'AnoInicio')->find($this->idBancoExtrato);
      if($banco && $banco->MesInicio !== null && $banco->AnoInicio !== null) {
        $mes_inicio = $banco->MesInicio;
        $ano_inicio = $banco->AnoInicio;
      }
    }

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
      Categoria.IconColor,
      TipoExtrato.Nome as TipoExtratoNome")
      ->join('BancoExtrato', 'BancoExtrato.Id', '=', 'Extrato.IdBancoExtrato')
      ->leftJoin('TipoExtrato', 'TipoExtrato.Id', '=', 'Extrato.IdTipoExtrato')
      ->leftJoin('Categoria', 'Categoria.Id', '=', 'Extrato.IdCategoria')
      ->whereBetween('Extrato.Data', [$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59'])
      ->where('Extrato.IdBancoExtrato', '=', $this->idBancoExtrato)
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

    $categoria_atual = $request->IdCategoria ?? null;

    $extrato = Extrato::select()
      ->where('Id', '=', $request->IdExtrato)
      ->first();

    if(!$extrato)
      throw new \Exception('Extrato não encontrado');

    if((int)$extrato->IdBancoExtrato !== (int)$this->idBancoExtrato)
      throw new \Exception('Extrato não encontrado');

    $categoria_ant = $extrato->IdCategoria;

    $extrato->IdCategoria = $categoria_atual;
    $extrato->DescricaoPersonalizada = $request->DescricaoPersonalizada ?? null;
    $extrato->save();

    //se a categoria esta sendo alterada, refaz o fechamento da categoria anterior e da categoria atual
    if($categoria_ant != $categoria_atual) {

      $dataExtrato = new \DateTime($extrato->Data);
      $dataBase = $dataExtrato->modify('first day of this month')->format('Y-m-d');

      if($categoria_ant)
        $this->processService->procFechamentoCategoria($categoria_ant, $dataBase);

      if($categoria_atual)
        $this->processService->procFechamentoCategoria($categoria_atual, $dataBase);

    }

  }

  public function ChartCategoriasMes(Request $request)
  {

    if(!$request->dataBase)
      throw new \Exception('$dataBase não passado');

    $dataBase = $request->dataBase;

    $date = new \DateTime($dataBase);
    $codigo_mes = (int)$date->format('m');

    $idBancoExtrato = $this->idBancoExtrato;


    $dados = FechamentoCategoria::selectRaw("
      Categoria.CategoriaNome,
      Categoria.IconColor,
      coalesce(FechamentoCategoria.ValorFechado, 0)  ValorCategoria")
      ->join('Categoria', 'Categoria.Id', '=', 'FechamentoCategoria.IdCategoria')
      ->where('FechamentoCategoria.IdBancoExtrato', '=', $idBancoExtrato)
      ->where('FechamentoCategoria.DataBase', '=', $dataBase)
      ->where('FechamentoCategoria.ValorFechado', '<', 0)
      // categorias que não serão exibidas nos gráficos como Resgate e Aplicacao de valores
      ->where('Categoria.ExibirGraficos', '=', 1)
      ->orderByRaw('FechamentoCategoria.ValorFechado, Categoria.CategoriaNome')
      ->get();


    $result = [];
    $result['labels'] =[];
    $result['datasets'][] = [
      'label' => 'Values',
      'backgroundColor' => [],
      'data' => []
    ];

    foreach($dados as $item) {

      array_push($result['labels'], $item->CategoriaNome);

      $backgroundColor = $item->IconColor;
      if ($backgroundColor && strpos($backgroundColor, '#') !== 0) {
        $backgroundColor = '#' . $backgroundColor;
      }
      array_push($result['datasets'][0]['backgroundColor'], $backgroundColor);

      array_push($result['datasets'][0]['data'], ((float)$item->ValorCategoria < 0) ? (float)$item->ValorCategoria * -1 :(float) $item->ValorCategoria);

    }

    return $result;

  }


  public function ChartCategoriaUltimosMeses(Request $request)
  {

    if(!$request->qtdMeses)
      throw new \Exception('qtdMeses não passado');

    if(!$request->idCategoria)
      throw new \Exception('qtdMeses não passado');


    $idUsuario = $this->idUsuario;
    $idBancoExtrato = $this->idBancoExtrato;
    $idCategoria = $request->idCategoria;

    $categoria = Categoria::where('Id', '=', $idCategoria)->first();
    if(!$categoria)
      throw new \Exception('Categoria não encontrada');


    $datasBase = Functions::ultimasDatadaBase($request->qtdMeses);

    // inverte a lista de datas
    $reversedDataBase = array_reverse($datasBase);

    $dados = [];

    foreach($reversedDataBase as $data) {

      $fechamento = FechamentoCategoria::selectRaw("
        ? 'DataBase',
        ? 'CodigoMes',
        coalesce(FechamentoCategoria.ValorFechado, 0) ValorCategoria",
        [$data['data_inicio_mes'], $data['codigo_mes']])
        ->where('FechamentoCategoria.IdBancoExtrato', '=', $idBancoExtrato)
        ->where('FechamentoCategoria.IdCategoria', '=', $idCategoria)
        ->where('FechamentoCategoria.DataBase', '=', $data['data_inicio_mes'])
        ->first();

      $dados[] = $fechamento;

    }

    $result = [];
    $result['labels'] = [];

    foreach($reversedDataBase as $item) {
      array_push($result['labels'], Functions::getMesNome($item['codigo_mes']));
    }

    $backgroundColor = $categoria->IconColor;
    if ($backgroundColor && strpos($backgroundColor, '#') !== 0) {
      $backgroundColor = '#' . $backgroundColor;
    }
    $result['datasets'][] = [
      'label' => $categoria->CategoriaNome,
      'backgroundColor' => $backgroundColor,
      'data' => []
    ];

    foreach($dados as $item) {
      $valorCategoria = isset($item->ValorCategoria) ? (float)$item->ValorCategoria : 0;
      array_push($result['datasets'][0]['data'], $valorCategoria * -1);
    }

    return $result;

  }


  public function ChartUltimosMeses(Request $request)
  {

    if(!$request->qtdMeses)
      throw new \Exception('qtdMeses não passado');

    $idBancoExtrato = $this->idBancoExtrato;
    $datasBase = Functions::ultimasDatadaBase($request->qtdMeses);

    // inverte a lista de datas
    $reversedDataBase = array_reverse($datasBase);

    $dados = [];

    foreach($reversedDataBase as $data) {

      $result = DB::connection('home')->select("
        select
        ? 'DataBase',
        ? 'CodigoMes',
        (
          select
          coalesce(sum(Extrato.Valor), 0)
          from Extrato
          where
          Extrato.IdBancoExtrato = ?
          and Extrato.Valor > 0
          and Extrato.Data between ? and ?
          and (
            Extrato.IdCategoria is null
            -- categorias que não serão exibidas nos gráficos como Resgate e Aplicacao de valores
            or Extrato.IdCategoria in (select Id from Categoria where ExibirGraficos = 1)
          )
        ) Entrada,
        (
          select
          coalesce(sum(Extrato.Valor), 0)
          from Extrato
          where
          Extrato.IdBancoExtrato = ?
          and Extrato.Valor < 0
          and (
            Extrato.IdCategoria is null
            -- categorias que não serão exibidas nos gráficos como Resgate e Aplicacao de valores
            or Extrato.IdCategoria in (select Id from Categoria where ExibirGraficos = 1)
          )
          and Extrato.Data between ? and ?
        ) Saida",
        [
          $data['data_inicio_mes'],
          $data['codigo_mes'],
          $idBancoExtrato,
          $data['data_inicio_mes'] . ' 00:00:00',
          $data['data_fim_mes'] . ' 23:59:59',
          $idBancoExtrato,
          $data['data_inicio_mes'] . ' 00:00:00',
          $data['data_fim_mes'] . ' 23:59:59'
        ]
      );

      $dados[] = $result[0];

    }

    $result = [];
    $result['labels'] = [];

    foreach($reversedDataBase as $item) {
      array_push($result['labels'], Functions::getMesNome($item['codigo_mes']));
    }

    $result['datasets'][] = [
      'label' => 'Entrada',
      'backgroundColor' => '#4da945',
      'data' => []
    ];

    $result['datasets'][] = [
      'label' => 'Saída',
      'backgroundColor' => '#f87979',
      'data' => []
    ];

    foreach($dados as $item) {
      array_push($result['datasets'][0]['data'], (float)$item->Entrada);
      array_push($result['datasets'][1]['data'], (float)$item->Saida * -1);
    }

    return $result;

  }

}
