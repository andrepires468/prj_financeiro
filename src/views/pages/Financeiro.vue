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

      <div v-for="(item, index) in Extrato" :key="index">

        <div class="row p-3">
          <div class="col-md-1">
            <b-icon-currency-dollar/>
          </div>
          <div class="col-md-9">
            {{ item.Descricao }}
          </div>
          <div class="col-md-2">
            {{ item.Valor }}
          </div>
        </div>

      </div>

    </div>
    <div class="col-md-5">

      -- graficos

    </div>
  </div>


</template>

<script>
import MultselectForm from "@/components/MultselectForm";
import financeiroService from "@/services/Financeiro.service";
import mesesModel from '../../models/meses.model'
import Helper from "@/helpers/helper.service";

export default {
  name: "Financeiro",
  components: {MultselectForm},
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

        const mesNome = mesesModel.find(j => parseInt(j.Id) == parseInt(item[0])).Nome

        return {
          Id: index,
          Mes: parseInt(item[0]),
          Ano: parseInt(item[1]),
          Nome: mesNome + '/' + item[1]
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

        this.Extrato = result.Data;

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

</style>
