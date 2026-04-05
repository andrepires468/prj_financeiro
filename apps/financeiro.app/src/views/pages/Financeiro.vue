<template>

  <ModalExtrato :ExtratoSel="ExtratoSel" :CategoriaLista="CategoriaLista" :CategoriaSel="CategoriaSel"
    :ocultarValores="ocultarValores" @updateExtrato="updateExtrato()" />

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-5 pb-2 mb-3 border-bottom">
    <h1>Financeiro</h1>

    <div class="btn-toolbar mb-2 mb-md-0">

      <div class="btn-group me-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="navegarFiltro('next')">
          <b-icon-chevron-compact-left class="icon" />
        </button>
        <div class="" style="min-width: 210px">
          <MultselectForm :options="CalendarioLista" v-model="CalendarioSel" :internalSearch="true" :searchable="true"
            label="Nome" placeholder="" />
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="navegarFiltro('previous')">
          <b-icon-chevron-compact-right class="icon" />
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="aplicarFiltroExtrato">
          <b-icon-search class="icon" />
          Aplicar
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="sincronizarExtrato" :disabled="isSync">

          <span v-if="isSync">
            <div class="spinner-border text-secondary spinner-border-sm" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            Sincronizando Extrato...
          </span>

          <span v-if="!isSync">
            <b-icon-database-fill-down class="icon" /> Sincronizar Extrato
          </span>

        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="toggleOcultarValores"
          :title="ocultarValores ? 'Exibir valores' : 'Ocultar valores'">
          <b-icon-eye v-if="!ocultarValores" class="icon" />
          <b-icon-eye-slash v-else class="icon" />
          {{ ocultarValores ? 'Exibir valores' : 'Ocultar valores' }}
        </button>
      </div>

    </div>
  </div>
  <br />
  <div class="row">
    <div class="col-md-6">

      <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
        <div class="col p-4 d-flex flex-column position-static">
          <strong class="d-inline-block mb-2 text-success">Categorias Mês</strong>
          <Bar v-if="ChartCategoriasMes" :key="'categorias-mes-' + ocultarValores" id="chart-categorias-mes"
            :options="chartBarraInvertidaOptions" :data="ChartCategoriasMes" />
          <div v-else class="text-align-center p-4">Carregando...</div>
        </div>
        <div class="col-auto d-none d-lg-block">
        </div>
      </div>

      <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
        <div class="col p-4 d-flex flex-column position-static">
          <strong class="d-inline-block mb-2 text-success">
            Comparativo Categoria (últimos {{ QtdMeses_ChartCategoria }} meses)
          </strong>

          <div class="me-2">
            <MultselectForm :options="CategoriaLista" v-model="CategoriaFiltroSel" :internalSearch="true"
              :searchable="true" label="CategoriaNome" placeholder="" showCategoryIcon openDirection="below" />
          </div>

          <Bar v-if="ChartCategoriaUltimosMeses" :key="'categoria-ultimos-' + ocultarValores"
            id="chart-categoria-ultimos-meses" :options="chartBarraOptions" :data="ChartCategoriaUltimosMeses" />

          <div v-if="CategoriaUltimosMesesMedia" class="text-align-center mt-2 text-sm">
            média de <b>{{ ocultarValores ? '••••••' : CategoriaUltimosMesesMedia }}</b> dos últimos
            {{ ChartCategoriaUltimosMeses?.labels?.length }} mêses
          </div>

          <div v-else class="text-align-center p-4">
            {{ CategoriaFiltroSel ? 'Carregando...' : 'Selecione uma Categoria' }}
          </div>
        </div>
        <div class="col-auto d-none d-lg-block">
        </div>
      </div>

      <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
        <div class="col p-4 d-flex flex-column position-static">
          <strong class="d-inline-block mb-2 text-success">
            Entras x Saídas (últimos {{ QtdMeses_ChartEntradaSaida }} meses)
          </strong>
          <Bar v-if="ChartUltimosMeses" :key="'ultimos-meses-' + ocultarValores" id="chart-ultimos-meses"
            :options="chartBarraOptions" :data="ChartUltimosMeses" />
          <div v-else class="text-align-center p-4">Carregando...</div>
        </div>
        <div class="col-auto d-none d-lg-block">
        </div>
      </div>

    </div>
    <div class="col-md-6">

      <div
        class="alert alert-warning text-align-center d-flex flex-wrap align-items-center justify-content-center gap-2"
        v-if="Extrato.filter(i => !i.IdCategoria).length > 0">
        <span>Restam {{Extrato.filter(i => !i.IdCategoria).length}} item(s) para classificar</span>
        <button type="button" class="btn btn-sm btn-outline-primary" :disabled="!CalendarioSel || isCategorizandoIA"
          @click="categorizarComIA" title="Categorizar automaticamente com IA (Gemini)">
          <span v-if="isCategorizandoIA">
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            Categorizando…
          </span>
          <span v-else>
            Categorizar com IA
          </span>
        </button>
      </div>

      <div class="row g-2 align-items-center mb-2">
        <div class="col-md-3">
          <MultselectForm :options="TipoExtratoLista" v-model="TipoFiltroExtratoSel" :internalSearch="true"
            :searchable="true" label="Nome" placeholder="Todos os tipos"  />
        </div>
        <div class="col-md-5">
          <MultselectForm :options="CategoriaLista" v-model="CategoriaFiltroExtratoSel" :internalSearch="true"
            :searchable="true" label="CategoriaNome" placeholder="Todas as categorias" showCategoryIcon
            />
        </div>
        <div class="col-md-3">
          <input type="text" class="form-control form-control-md" id="pesquisaCategoria" v-model="pesquisaExtrato"
            placeholder="Filtrar extrato">
        </div>
        <div class="col-auto">
          <button type="button" class="btn btn-md btn-outline-secondary" :disabled="!temFiltroExtrato"
            @click="limparFiltrosExtrato" title="Limpar filtros da tabela">
            <b-icon-x-circle class="icon" />
          </button>
        </div>
      </div>

      <table class="table-financeiro w-100">
        <tbody>
          <tr v-for="(item, index) in ExtratoFiltrado" :key="index" data-bs-toggle="modal"
            data-bs-target="#modalExtrato" @click="abrirModalExtrato(item, indiceExtrato(item))">
            <td
              style="width: 30px;"
              :class="`item-extrato-data ${(!ExtratoFiltrado[index - 1] || (item.DataDisplay != ExtratoFiltrado[index - 1].DataDisplay)) ? '' : 'item-extrato-data-repet'}`">
              <span>{{ item.DiaDisplay }}</span>
              <span>{{ item.MesDisplay }}</span>
            </td>
            <td style="width: 50px;">
              <div class="item-extrato-icon" :title="item.CategoriaNome">
                <IconeCategoria :icon="item.IconTag" :color="item.IconColor" :width="30" :height="30" />
              </div>
            </td>
            <td>
              <p class="m-0 small" :style="`width: 50px; color: ${item.IconColor}`">{{ item.CategoriaNome }}</p>
              <p class="m-0 small" v-if="item.DescricaoPersonalizada">
                <b-icon-pencil-square class="icon" />
                {{ item.DescricaoPersonalizada }}
              </p>
              <p class="m-0 small">
                <span class="m-0 pe-1" v-if="item.TipoExtratoNome" :class="{ 'text-muted': !item.TipoExtratoCor }"
                  :style="item.TipoExtratoCor ? { color: item.TipoExtratoCor } : null">
                  {{ item.TipoExtratoNome }}
                </span>
                {{ item.Descricao }}
              </p>

            </td>
            <td class="item-extrato-valor" style="width: 120px;">
              <span :class="`small ${(item.Tipo == 'entrada') ? 'extrato-entrada' : 'extrato-saida'}`">
                {{ ocultarValores ? '••••••' : item.Valor }}
              </span>
            </td>
          </tr>
        </tbody>
        <tfoot v-if="Extrato.length">
          <tr class="extrato-total-linha extrato-total-linha--entrada">
            <td colspan="3" class="text-end small fw-semibold align-middle">
              Entradas ({{ extratoFiltradoQtdEntradas }} {{ extratoFiltradoQtdEntradas === 1 ? 'item' : 'itens' }})
            </td>
            <td class="item-extrato-valor align-middle">
              <span class="small extrato-entrada">
                {{ ocultarValores ? '••••••' : extratoFiltradoEntradasFormatado }}
              </span>
            </td>
          </tr>
          <tr class="extrato-total-linha extrato-total-linha--saida">
            <td colspan="3" class="text-end small fw-semibold align-middle">
              Saídas ({{ extratoFiltradoQtdSaidas }} {{ extratoFiltradoQtdSaidas === 1 ? 'item' : 'itens' }})
            </td>
            <td class="item-extrato-valor align-middle">
              <span class="small extrato-saida">
                {{ ocultarValores ? '••••••' : extratoFiltradoSaidasFormatado }}
              </span>
            </td>
          </tr>
        </tfoot>
      </table>

    </div>
  </div>

</template>

<script>
import MultselectForm from "@/components/MultselectForm";
import financeiroService from "@/services/Financeiro.service";
import categoriaService from "@/services/Categoria.service";
import processService from "@/services/Process.service";
import Helper from "@/helpers/helper.service";
import IconeCategoria from '../../components/IconeCategoria'
import mesesModel from '../../models/meses.model'
import tipoExtratoModel from '../../models/tipo.extrato.model.json'
import categoriaIconsModel from '../../models/categoria.icons.model'
import moment from "moment";
import ModalExtrato from "@/views/pages/ModalExtrato";
import { getTheme, THEME } from '@/helpers/theme.service'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ChartDataLabels)

export default {
  name: "Financeiro",
  components: { ModalExtrato, MultselectForm, IconeCategoria, Bar },
  data() {
    return {
      isSync: false,
      isCategorizandoIA: false,
      ocultarValores: false,
      CalendarioLista: [],
      CalendarioSel: null,
      Extrato: [],
      ExtratoSel: null,
      ExtratoSelIndex: null,
      pesquisaExtrato: null,

      CategoriaLista: [],
      CategoriaSel: null,
      CategoriaFiltroSel: null,
      /** Filtro de categoria para a tabela de extrato */
      CategoriaFiltroExtratoSel: null,
      /** Filtro por tipo de extrato (modelo tipo.extrato.model.json) */
      TipoFiltroExtratoSel: null,

      ChartCategoriasMes: null,
      QtdMeses_ChartCategoria: 6,
      ChartCategoriaUltimosMeses: null,
      CategoriaUltimosMesesMedia: null,
      QtdMeses_ChartEntradaSaida: 4,
      ChartUltimosMeses: null,
      /** Tema atual para reatividade dos gráficos (light/dark) */
      theme: getTheme(),
    }
  },
  async mounted() {
    const onThemeChanged = (e) => { this.theme = e.detail }
    window.addEventListener('financeiro-theme-changed', onThemeChanged)
    this._themeCleanup = () => window.removeEventListener('financeiro-theme-changed', onThemeChanged)

    try {

      swalLoading.fire('', 'Carregando Extrato, aguarde...')

      const result = await financeiroService.calendarioExtrato()
      if (!result.Success)
        throw new Error(Helper.getMessageError(result))

      this.CalendarioLista = result.Data.map((i, index) => {

        const item = i.split('/')
        const mesItem = mesesModel.find(j => parseInt(j.Id) == parseInt(item[0]))

        return {
          Id: index,
          Mes: parseInt(item[0]),
          Ano: parseInt(item[1]),
          Nome: mesItem.Nome + '/' + item[1],
          NomeCurto: mesItem.NomeCurto
        }

      });

      if (window.localStorage.getItem('CalendarioSel')) {
        this.CalendarioSel = JSON.parse(window.localStorage.getItem('CalendarioSel'))

      } else {
        this.CalendarioSel = this.CalendarioLista[0]
      }

      if (window.localStorage.getItem('financeiro.ocultarValores') === 'true') {
        this.ocultarValores = true
      }

      await this.getExtrato()

      await this.categoriaLista()

      swalLoading.close()

      this.getChartCategoriasMes();
      this.getChartUltimosMeses();

    } catch (e) {
      Helper.messageExceptionHandler(e)
    }
  },
  beforeUnmount() {
    if (typeof this._themeCleanup === 'function') this._themeCleanup()
  },
  computed: {
    /** Opções do filtro de tipo (sincronizadas com tipo.extrato.model.json) */
    TipoExtratoLista() {
      return tipoExtratoModel.map(t => ({ ...t, Id: Number(t.Id) }))
    },
    /** Extrato filtrado por pesquisa (texto), tipo e categoria */
    ExtratoFiltrado() {
      let lista = this.Extrato
      if (this.pesquisaExtrato) {
        const termo = this.normalizarTexto(this.pesquisaExtrato)
        lista = lista.filter(i =>
          this.normalizarTexto(i.CategoriaNome + ' ' + i.Descricao).includes(termo)
        )
      }
      if (this.TipoFiltroExtratoSel && this.TipoFiltroExtratoSel.Id != null) {
        const idTipo = Number(this.TipoFiltroExtratoSel.Id)
        lista = lista.filter(i => Number(i.IdTipoExtrato) === idTipo)
      }
      if (this.CategoriaFiltroExtratoSel && this.CategoriaFiltroExtratoSel.Id != null) {
        lista = lista.filter(i => i.IdCategoria === this.CategoriaFiltroExtratoSel.Id)
      }
      return lista
    },
    /**
     * Linhas do extrato que entram nos totais do rodapé: mesmos filtros da tabela,
     * excluindo itens cuja categoria tenha ExibirGraficos desmarcado (itens sem
     * categoria continuam, alinhado ao gráfico Entradas x Saídas na API).
     */
    ExtratoFiltradoParaTotais() {
      return this.ExtratoFiltrado.filter(i => this.extratoItemContaExibirGraficos(i))
    },
    /** Indica se há algum filtro ativo na tabela de extrato */
    temFiltroExtrato() {
      return (this.TipoFiltroExtratoSel != null && this.TipoFiltroExtratoSel.Id != null) ||
        (this.CategoriaFiltroExtratoSel != null && this.CategoriaFiltroExtratoSel.Id != null) ||
        (this.pesquisaExtrato != null && this.pesquisaExtrato.trim() !== '')
    },
    extratoFiltradoSomaEntradas() {
      return this.ExtratoFiltradoParaTotais.reduce((acc, i) => {
        const v = Number(i.ValorNumerico ?? 0)
        return acc + (v > 0 ? v : 0)
      }, 0)
    },
    extratoFiltradoSomaSaidas() {
      return this.ExtratoFiltradoParaTotais.reduce((acc, i) => {
        const v = Number(i.ValorNumerico ?? 0)
        return acc + (v < 0 ? v : 0)
      }, 0)
    },
    extratoFiltradoEntradasFormatado() {
      return Helper.formatCurrency(this.extratoFiltradoSomaEntradas)
    },
    extratoFiltradoSaidasFormatado() {
      return Helper.formatCurrency(this.extratoFiltradoSomaSaidas)
    },
    extratoFiltradoQtdEntradas() {
      return this.ExtratoFiltradoParaTotais.filter(i => Number(i.ValorNumerico ?? 0) > 0).length
    },
    extratoFiltradoQtdSaidas() {
      return this.ExtratoFiltradoParaTotais.filter(i => Number(i.ValorNumerico ?? 0) < 0).length
    },
    chartBarraInvertidaOptions() {
      const ocultar = this.ocultarValores
      const isDark = this.theme === THEME.DARK
      const textColor = isDark ? '#e2e2e2' : '#000'
      return {
        indexAxis: 'y',
        layout: {
          padding: {
            top: 0,
            right: 70,
            bottom: 0,
            left: 0
          }
        },
        plugins: {
          datalabels: {
            display: !ocultar,
            formatter: function (value) {
              return ocultar ? '••••••' : 'R$' + value.toFixed(2).replace('.', ',')
            },
            color: textColor,
            anchor: 'end',
            align: 'right'
          },
          legend: {
            labels: { color: textColor },
            padding: { bottom: 16 }
          },
          tooltip: {
            callbacks: {
              label: (context) => ocultar ? '••••••' : ('R$ ' + Number(context.raw).toFixed(2).replace('.', ','))
            }
          }
        },
        scales: {
          x: {
            display: false,
            ticks: {
              color: textColor,
              callback: function (value) {
                return 'R$' + value.toFixed(2).replace('.', ',')
              }
            }
          },
          y: {
            beginAtZero: true,
            ticks: { color: textColor }
          }
        }
      }
    },
    chartBarraOptions() {
      const ocultar = this.ocultarValores
      const isDark = this.theme === THEME.DARK
      const textColor = isDark ? '#e2e2e2' : '#5a5a5a'
      return {
        responsive: true,
        layout: {
          padding: {
            top: 0,
            right: 0,
            bottom: 0,
            left: 0
          }
        },
        scales: {
          y: {
            display: false,
          },
          x: {
            ticks: { color: textColor }
          }
        },
        plugins: {
          datalabels: {
            display: !ocultar,
            formatter: function (value) {
              return ocultar ? '••••••' : 'R$' + value.toFixed(2).replace('.', ',')
            },
            color: textColor,
            anchor: 'end',
            align: 'top'
          },
          legend: {
            labels: { color: textColor },
            padding: { bottom: 16 }
          },
          tooltip: {
            callbacks: {
              label: (context) => ocultar ? '••••••' : ('R$ ' + Number(context.raw).toFixed(2).replace('.', ','))
            }
          }
        },
      }
    },
  },
  methods: {
    toggleOcultarValores() {
      this.ocultarValores = !this.ocultarValores
      window.localStorage.setItem('financeiro.ocultarValores', this.ocultarValores)
    },
    normalizarTexto(texto) {
      return Helper.normalizarTexto(texto);
    },
    async categoriaLista() {

      try {

        const result = await categoriaService.listCategoria()
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        this.CategoriaLista = result.Data.map(i => {
          return {
            ...i,
            Id: parseInt(i.Id),
            IconSVG: categoriaIconsModel.find(j => j.IconTag == i.IconTag).IconSVG
          }
        })

        if (this.CategoriaLista) {
          const savedId = window.localStorage.getItem('financeiro.CategoriaFiltroComparativo')
          if (savedId) {
            const found = this.CategoriaLista.find(i => i.Id === parseInt(savedId, 10))
            this.CategoriaFiltroSel = found ?? this.CategoriaLista.find(i => i.Id == 1)
          } else {
            this.CategoriaFiltroSel = this.CategoriaLista.find(i => i.Id == 1)
          }
        }

      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    },
    extratoItemContaExibirGraficos(item) {
      const id = item.IdCategoria
      if (id == null || id === '') return true
      if (!this.CategoriaLista.length) return true
      const cat = this.CategoriaLista.find(c => c.Id === Number(id))
      if (!cat) return false
      const ex = cat.ExibirGraficos
      return ex !== false && ex !== 0 && ex !== '0'
    },
    limparFiltrosExtrato() {
      this.TipoFiltroExtratoSel = null
      this.CategoriaFiltroExtratoSel = null
      this.pesquisaExtrato = ''
    },
    indiceExtrato(item) {
      return this.Extrato.indexOf(item)
    },
    async abrirModalExtrato(item, index) {

      this.ExtratoSel = item
      this.ExtratoSelIndex = index
      this.CategoriaSel = this.CategoriaLista.find(i => i.Id == item.IdCategoria) ?? null

    },
    findAdjacentId(list, currentId, direction) {

      // Encontrar o índice do item atual
      const currentIndex = list.findIndex(item => item.Id === currentId);

      if (currentIndex === -1) {
        throw new Error('ID atual não encontrado na lista.');
      }

      // Determinar o índice do próximo ou anterior
      let newIndex;
      if (direction === 'next') {
        newIndex = currentIndex + 1;
      } else if (direction === 'previous') {
        newIndex = currentIndex - 1;
      } else {
        throw new Error('Direção inválida. Use "next" ou "previous".');
      }

      // Retornar o ID do próximo ou anterior, se existir
      return list[newIndex] ? list[newIndex] : null;

    },
    async navegarFiltro(nav) {

      try {

        if (this.CalendarioSel) {

          // console.log(this.CalendarioLista)
          // console.log(this.CalendarioSel)

          if (!['next', 'previous'].includes(nav))
            return

          const novo = this.findAdjacentId(this.CalendarioLista, this.CalendarioSel.Id, nav)
          if (novo) this.CalendarioSel = novo

        }

      } catch (e) {
        console.error(e);
      }

    },
    async aplicarFiltroExtrato() {

      try {

        swalLoading.fire('', 'Carregando Extrato, aguarde...')

        this.pesquisaExtrato = null

        await this.getExtrato();

        swalLoading.close()

        this.getChartCategoriasMes();
        this.getChartCategoriaUltimosMeses();

      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    },
    setarExtrato(dados) {

      this.Extrato = dados.map(i => {
        const dia = moment(i.Data, "YYYY-MM-DDTHH:mm:ss.SSS").format("DD")
        const tipoExtrato = tipoExtratoModel.find(t => Number(t.Id) === Number(i.IdTipoExtrato))
        return {
          ...i,
          TipoExtratoNome: i.TipoExtratoNome ?? null,
          TipoExtratoCor: tipoExtrato?.Cor ?? null,
          ValorNumerico: parseFloat(i.Valor),
          Data: Helper.formatDate(i.Data),
          Valor: Helper.formatCurrency(i.Valor),
          DiaDisplay: dia,
          MesDisplay: this.CalendarioSel.NomeCurto,
          DataDisplay: dia + '/' + this.CalendarioSel.NomeCurto
        }
      });

    },
    async updateExtrato() {

      this.getChartCategoriasMes();
      this.getChartCategoriaUltimosMeses();

    },
    async getExtrato() {

      if (!this.CalendarioSel)
        throw new Error('Selecione um mês|warning');

      window.localStorage.setItem('CalendarioSel', JSON.stringify(this.CalendarioSel))

      const result = await financeiroService.listaExtrato(null, null, this.CalendarioSel.Mes, this.CalendarioSel.Ano)
      if (!result.Success)
        throw new Error(Helper.getMessageError(result))

      this.setarExtrato(result.Data)

    },
    async sincronizarExtrato() {

      try {

        //swalLoading.fire('', 'Sincronizando Extrato, aguarde...')
        this.isSync = true;

        const result = await processService.updateExtrato({})
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        if (!result.Data.emails_lidos) {

          swalToast.fire('', 'Nenhum novo extrato encontrado', 'info')

        } else {

          await swalAlert.fire({
            title: 'Extrato Sincronizado',
            html: `<p>${result.Data.emails_lidos} emails,
                   ${result.Data.itens_listados} itens encontrados,
                   <b>${result.Data.itens_incluidos} novos itens importados</b></p>`,
            icon: 'success',
            confirmButtonText: 'OK',
          }).then(async (result) => {
            if (result.isConfirmed) {

              swalLoading.fire('', 'Carregando Extrato...')
              await this.getExtrato()
              swalLoading.close();

            }
          })
        }

        this.isSync = false;

        this.getChartCategoriasMes();
        this.getChartUltimosMeses();

      } catch (e) {
        Helper.messageExceptionHandler(e)
      } finally {
        this.isSync = false;
      }

    },
    async categorizarComIA() {
      if (!this.CalendarioSel || this.Extrato.filter(i => !i.IdCategoria).length === 0) return;
      try {
        this.isCategorizandoIA = true;
        const result = await processService.categorizarExtrato({
          mes: this.CalendarioSel.Mes,
          ano: this.CalendarioSel.Ano
        });
        if (!result.Success) throw new Error(Helper.getMessageError(result));
        const n = result.Data?.atualizados ?? 0;
        await this.getExtrato();
        this.getChartCategoriasMes();
        this.getChartCategoriaUltimosMeses();
        swalToast.fire('', n > 0 ? n + ' item(ns) categorizado(s) com IA' : 'Nenhum item para categorizar', 'success');
      } catch (e) {
        swalToast.fire('', 'Erro ao categorizar com IA: ' + (e?.message || 'tente novamente'), 'error');
        Helper.messageExceptionHandler(e);
      } finally {
        this.isCategorizandoIA = false;
      }
    },
    async getChartCategoriasMes(qtdMeses) {

      const dataBase = this.CalendarioSel.Ano + '-' + this.CalendarioSel.Mes.toString().padStart(2, '0') + '-01'

      try {

        this.ChartCategoriasMes = null

        const result = await financeiroService.ChartCategoriasMes(dataBase)
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        this.ChartCategoriasMes = result.Data

      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    },
    async getChartCategoriaUltimosMeses() {


      try {

        this.ChartCategoriaUltimosMeses = null

        if (this.CategoriaFiltroSel?.Id) {

          const result = await financeiroService.ChartCategoriaUltimosMeses(this.CategoriaFiltroSel?.Id, this.QtdMeses_ChartCategoria)
          if (!result.Success)
            throw new Error(Helper.getMessageError(result))

          this.ChartCategoriaUltimosMeses = result.Data

          const valores = this.ChartCategoriaUltimosMeses.datasets[0].data;
          const total = valores.reduce((sum, num) => sum + num, 0)

          const media = total / valores.length;
          this.CategoriaUltimosMesesMedia = Helper.formatCurrency(media)

        }

      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    },
    async getChartUltimosMeses() {

      try {

        this.ChartUltimosMeses = null

        const result = await financeiroService.chartUltimosMeses(this.QtdMeses_ChartEntradaSaida)
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        this.ChartUltimosMeses = result.Data


      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    }
  },
  watch: {
    async CategoriaFiltroSel(value) {
      if (value?.Id != null) {
        window.localStorage.setItem('financeiro.CategoriaFiltroComparativo', String(value.Id))
      }
      this.getChartCategoriaUltimosMeses()
    }
  }
}
</script>

<style scoped>
.table-financeiro tbody tr:hover {
  cursor: pointer;
  background-color: rgba(0, 0, 0, 0.04);
}

.table-financeiro tfoot .extrato-total-linha td {
  padding-top: 0.5rem;
}

.table-financeiro tfoot .extrato-total-linha--entrada td {
  border-top: 2px solid var(--bs-border-color, #dee2e6);
  padding-top: 0.75rem;
}

.table-financeiro tfoot .extrato-total-linha {
  cursor: default;
}

.item-extrato-data {
  width: 50px;
  padding: 0 0 0 8px;
  font-weight: bold;
}

.item-extrato-data-repet {
  font-weight: normal;
  color: var(--bs-gray-400);
  font-size: 14px;
}

.item-extrato-data span {
  text-align: center;
  display: block;
}

.item-extrato-data span:last-child {
  margin-top: -8px;
}

.item-extrato-icon {
  padding: 20px 20px 20px 20px;
}

.item-extrato-valor {
  width: 110px;
  padding: 0 14px 0 14px;
}
</style>
