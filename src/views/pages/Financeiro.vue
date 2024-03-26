<template>

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-5 pb-2 mb-3 border-bottom">
    <h1 class="h2">Financeiro</h1>
    <div class="btn-toolbar mb-2 mb-md-0">

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
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="getExtrato()">Aplicar</button>
        <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
      </div>

    </div>
  </div>
  <div class="row">
    <div class="col-md-7">

      <table class="table-financeiro">
        <tbody>
        <tr v-for="(item, index) in Extrato" :key="index">
          <td style="width: 50px">
            <b style="padding: 0 0 0 10px">{{ item.DataDisplay }}</b>
          </td>
          <td style="width: 60px">
            <div style="padding: 15px 20px 15px 20px" :title="item.CategoriaNome">
              <iconMercado :icon="item.IconTag" :color="item.IconColor" :width="30" :height="30"/>
            </div>
          </td>
          <td>
            <p class="m-0 small" :style="`color: ${item.IconColor}`">{{ item.CategoriaNome }}</p>
            <p class="m-0 small">{{ item.Descricao }}</p>
          </td>
          <td style="width: 80px">
            <span style="padding: 0 10px 0 10px " :class="`small ${(item.Valor > 0)? 'text-success' : 'text-danger'}`">{{ item.Valor }}</span>
          </td>
        </tr>
        </tbody>
      </table>

    </div>
    <div class="col-md-5">

      -- graficos

    </div>
  </div>


</template>

<script>
import MultselectForm from "@/components/MultselectForm";
import financeiroService from "@/services/Financeiro.service";
import Helper from "@/helpers/helper.service";
import iconMercado from '../../components/IconeCategoria'
import mesesModel from '../../models/meses.model'
import moment from "moment";

export default {
  name: "Financeiro",
  components: {MultselectForm, iconMercado},
  data() {
    return {
      CalendarioLista: [],
      CalendarioSel: null,
      Extrato: []
    }
  },
  async mounted() {
    try {

      swalLoading.fire('', 'Aguarde...')

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
        this.CalendarioSel = JSON.parse(window.localStorage.getItem('CalendarioSel'));

      } else {
        this.CalendarioSel = this.CalendarioLista[0];
      }

      await this.getExtrato();

      swalLoading.close()

    } catch (e) {
      Helper.messageExceptionHandler(e)
    }
  },
  methods: {
    async getExtrato() {

      try {

        swalLoading.fire('', 'Carregando Extrato...')

        if (!this.CalendarioSel)
          throw new Error('Selecione um mês|warning');

        window.localStorage.setItem('CalendarioSel', JSON.stringify(this.CalendarioSel))

        const result = await financeiroService.listExtrato(null, null, this.CalendarioSel.Mes, this.CalendarioSel.Ano)
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        this.Extrato = result.Data.map(i => {
          return {
            ...i,
            DataDisplay: moment(i.Data, "YYYY-MM-DDTHH:mm:ss.SSS").format("DD") + '/' + this.CalendarioSel.NomeCurto
          }
        });

        swalLoading.close()

      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    }
  },
  // watch: {
  //   MesSel(valor, ant): {
  //     if (valor && valor?.Id != ant?.Id) {
  //       await Extrato()
  //     }
  //   }
  // }
}
</script>

<style scoped>
.table-financeiro tbody tr:hover{
  cursor: pointer;
  background-color: rgba(0,0,0,0.04);
}

</style>
