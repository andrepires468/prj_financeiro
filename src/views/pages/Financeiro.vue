<template>

  <ModalExtrato
      :ExtratoSel="ExtratoSel"
      :CategoriaLista="CategoriaLista"
      :CategoriaSel="CategoriaSel"
  />

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-5 pb-2 mb-3 border-bottom">
    <h1>Financeiro</h1>
    <div class="btn-toolbar mb-2 mb-md-0">

<!--      <button type="button" class="btn btn-sm btn-outline-secondary" @click="categorizarExtrato">-->
<!--        Categorizar-->
<!--      </button>-->

      <div class="me-2" style="min-width: 180px">
        <MultselectForm
            :options="CalendarioLista"
            v-model="CalendarioSel"
            :internalSearch="true"
            :searchable="true"
            label="Nome"
            placeholder=""
        />
      </div>

      <div class="btn-group me-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="aplicarFiltroExtrato">
          <b-icon-search class="icon"/>
          Aplicar
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="sincronizarExtrato" :disabled="isSync">
          <b-icon-database-fill-down class="icon" v-if="!isSync"/>
          <div class="spinner-border text-secondary spinner-border-sm" role="status" v-else>
            <span class="visually-hidden">Loading...</span>
          </div>
          Sincronizar Extrato
        </button>
      </div>

    </div>
  </div>
  <br/>
  <div class="row">
    <div class="col-md-6">

      <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
        <div class="col p-4 d-flex flex-column position-static">
          <strong class="d-inline-block mb-2 text-success">Design</strong>
          <h3 class="mb-0">Post title</h3>
          <div class="mb-1 text-muted">Nov 11</div>

          <Bar
              id="my-chart-id"
              :options="chartOptions"
              :data="chartData"
          />




          <a href="#" class="stretched-link">Continue reading</a>
        </div>
        <div class="col-auto d-none d-lg-block">
        </div>
      </div>

    </div>
    <div class="col-md-6">


      <div class="alert alert-warning" v-if="Extrato.filter( i => !i.IdCategoria).length > 0">
        Restam {{ Extrato.filter(i => !i.IdCategoria).length }} item(s) para classificar
      </div>

      <table class="table-financeiro">
        <tbody>
        <tr v-for="(item, index) in Extrato" :key="index"
            data-bs-toggle="modal"
            data-bs-target="#modalExtrato"
            @click="abrirModalExtrato(item, index)">
          <td :class="`item-extrato-data ${(!Extrato[index - 1] || (item.DataDisplay != Extrato[index - 1].DataDisplay)) ? '' : 'item-extrato-data-repet'}`">
            <span>{{ item.DiaDisplay }}</span>
            <span>{{ item.MesDisplay }}</span>
          </td>
          <td>
            <div class="item-extrato-icon" :title="item.CategoriaNome">
              <IconeCategoria :icon="item.IconTag"
                              :color="item.IconColor"
                              :width="30" :height="30"/>
            </div>
          </td>
          <td>
            <p class="m-0 small" :style="`color: ${item.IconColor}`">{{ item.CategoriaNome }}</p>
            <p class="m-0 small" v-if="item.DescricaoPersonalizada">
              <b-icon-pencil-square class="icon"/>
              {{ item.DescricaoPersonalizada }}
            </p>
            <p class="m-0 small">{{ item.Descricao }}</p>
          </td>
          <td class="item-extrato-valor">
            <span :class="`small ${(item.Tipo == 'entrada') ? 'extrato-entrada' : 'extrato-saida'}`">
              {{ item.Valor }}
            </span>
          </td>
        </tr>
        </tbody>
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
import categoriaIconsModel from '../../models/categoria.icons.model'
import moment from "moment";
import ModalExtrato from "@/views/pages/ModalExtrato";
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
  name: "Financeiro",
  components: {ModalExtrato, MultselectForm, IconeCategoria, Bar},
  data() {
    return {
      isSync: false,
      CalendarioLista: [],
      CalendarioSel: null,
      Extrato: [],
      ExtratoSel: null,
      ExtratoSelIndex: null,

      CategoriaLista: [],
      CategoriaSel: null,

      chartData: {
        labels: [ 'January', 'February', 'March' ],
        datasets: [ { data: [40, 20, 12] } ]
      },
      chartOptions: {
        responsive: true
      },

    }
  },
  async mounted() {

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

      await this.getExtrato()

      await this.categoriaLista()

      swalLoading.close()

    } catch (e) {
      Helper.messageExceptionHandler(e)
    }
  },
  methods: {
    // async categorizarExtrato(){
    //
    //   const data = {
    //     mes: this.CalendarioSel.Mes,
    //     ano: this.CalendarioSel.Ano
    //   }
    //
    //   const result = await processService.categorizarExtrato(data)
    //   if (!result.Success)
    //     throw new Error(Helper.getMessageError(result))
    //
    // },
    async categoriaLista() {

      try {

        const result = await categoriaService.listCategoria()
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        this.CategoriaLista = result.Data.map(i => {
          return {
            ...i,
            IconSVG: categoriaIconsModel.find(j => j.IconTag == i.IconTag).IconSVG
          }
        })

      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    },
    async abrirModalExtrato(item, index) {

      this.ExtratoSel = item
      this.ExtratoSelIndex = index
      this.CategoriaSel = this.CategoriaLista.find(i => i.Id == item.IdCategoria) ?? null

    },
    async aplicarFiltroExtrato() {

      try {

        swalLoading.fire('', 'Carregando Extrato, aguarde...')

        await this.getExtrato();

        swalLoading.close()

      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    },
    setarExtrato(dados) {

      this.Extrato = dados.map(i => {
        const dia = moment(i.Data, "YYYY-MM-DDTHH:mm:ss.SSS").format("DD")
        return {
          ...i,
          Data: Helper.formatDate(i.Data),
          Valor: Helper.formatCurrency(i.Valor),
          DiaDisplay: dia,
          MesDisplay: this.CalendarioSel.NomeCurto,
          DataDisplay: dia + '/' + this.CalendarioSel.NomeCurto
        }
      });

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

      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    }
  },
}
</script>

<style scoped>

.table-financeiro tbody tr:hover {
  cursor: pointer;
  background-color: rgba(0, 0, 0, 0.04);
}

.item-extrato-data {
  width: 50px;
  padding: 0 0 0 14px;
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
