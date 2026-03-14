<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Dados baseados em apps/financeiro.app/src/models/categoria.icons.model.js
     */
    public function up(): void
    {
        $categorias = [
            [1, 'Mercado', 'mercado', '27ae60'],
            [2, 'Aplicação', 'aplicacao', '3498db'],
            [3, 'Bares e Restaurantes', 'bares_restaurantes', 'e74c3c'],
            [4, 'Bônus', 'bonus', 'f39c12'],
            [5, 'Carne', 'carne', 'c0392b'],
            [6, 'Categoria Personalizada', 'categoria_personalizada', '95a5a6'],
            [7, 'Cheque Especial', 'cheque_especial', 'e67e22'],
            [8, 'Compras', 'compras', '9b59b6'],
            [9, 'Contas Residenciais', 'contas_residenciais', '1abc9c'],
            [10, 'Crediário', 'crediario', '8e44ad'],
            [11, 'Crédito Consignado', 'credito_consignado', '2980b9'],
            [12, 'Cuidados Pessoais', 'cuidados_pessoais', 'e91e63'],
            [13, 'Despesas de Trabalho', 'despesas_trabalho', '607d8b'],
            [14, 'Educação', 'educacao', '009688'],
            [15, 'Empregados Domésticos', 'empregados_domesticos', '795548'],
            [16, 'Empréstimo', 'emprestimo', '673ab7'],
            [17, 'Família e Filhos', 'familia_filhos', 'ff9800'],
            [18, 'Impostos', 'impostos', 'd32f2f'],
            [19, 'Juros', 'juros', 'f44336'],
            [20, 'Juros Cartão', 'juros_cartao', 'c62828'],
            [21, 'Lazer', 'lazer', '00bcd4'],
            [22, 'Moradia', 'moradia', '3f51b5'],
            [23, 'Outras Rendas', 'outras_rendas', '4caf50'],
            [24, 'Outros Empréstimos', 'outros_emprestimos', '5c6bc0'],
            [25, 'Outros Gastos', 'outros_gastos', '78909c'],
            [26, 'Pagamento Cartão', 'pagameto_cartao', '1565c0'],
            [27, 'Pets', 'pets', '795548'],
            [28, 'Presentes e Doações', 'presentes_doacoes', 'ec407a'],
            [29, 'Remuneração', 'remuneracao', '2e7d32'],
            [30, 'Rendimentos', 'rendimentos', '43a047'],
            [31, 'Resgate', 'resgate', '0288d1'],
            [32, 'Saques', 'saques', '1976d2'],
            [33, 'Saúde', 'saude', '00838f'],
            [34, 'Sem Categoria', 'sem_categoria', '90a4ae'],
            [35, 'Serviços', 'servicos', '455a64'],
            [36, 'Taxas Bancárias', 'taxas_bancarias', '37474f'],
            [37, 'Transferências', 'transferencias', '0d47a1'],
            [38, 'Transporte', 'transporte', 'ff5722'],
            [39, 'TV, Internet e Telefone', 'tv_internet_telefone', '7e57c2'],
            [40, 'Viagem', 'viagem', '00acc1'],
        ];

        $connection = $this->getConnectionName() ?? 'home';
        $table = 'Categoria';

        foreach ($categorias as [$id, $nome, $iconTag, $iconColor]) {
            DB::connection($connection)->table($table)->updateOrInsert(
                ['Id' => $id],
                [
                    'CategoriaNome' => $nome,
                    'IconTag' => $iconTag,
                    'IconColor' => $iconColor,
                ]
            );
        }
    }

    public function down(): void
    {
        $connection = $this->getConnectionName() ?? 'home';
        $ids = range(1, 40);
        DB::connection($connection)->table('Categoria')->whereIn('Id', $ids)->delete();
    }
};
