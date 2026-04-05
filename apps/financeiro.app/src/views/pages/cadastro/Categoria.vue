<template>

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-5 pb-2 mb-3 border-bottom">
    <h1>Cadastro / Categoria</h1>
  </div>

  <table class="table table-striped">
    <thead>
    <tr>
      <th>Id</th>
      <th>Categoria Nome</th>
      <th class="text-small">Exibir nos Gráficos</th>
      <th colspan="2">Icone</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(categoria, index) in Categorias" :key="index">
      <td>{{ categoria.Id }}</td>
      <td>
        <input type="text"
               class="form-control w-100"
               style="background-color: transparent; border: none"
               v-model="categoria.CategoriaNome"
               @input="updateCategoria(categoria)"
        >
      </td>
      <td class="text-center align-middle" style="width: 90px">
        <div class="form-check form-switch form-switch-lg d-inline-block mb-0">
          <input :id="'categoria-exibir-graficos-' + categoria.Id"
                 class="form-check-input"
                 type="checkbox"
                 role="switch"
                 v-model="categoria.ExibirGraficos"
                 @change="updateCategoria(categoria, true)"
                 title="Exibir no gráfico Categorias do mês"
          >
          <label class="form-check-label visually-hidden"
                 :for="'categoria-exibir-graficos-' + categoria.Id">
            Exibir no gráfico Categorias do mês
          </label>
        </div>
      </td>
      <td style="width: 40px">
        <IconeCategoria :icon="categoria.IconTag" :color="categoria.IconColor" :width="40" :height="40"/>
      </td>
      <td>

        <color-input v-model="categoria.IconColor" @change="updateCategoria(categoria)"/>

        {{ categoria.IconColor }}

      </td>
    </tr>
    </tbody>
  </table>

</template>

<script>
import categoriaService from "@/services/Categoria.service";
import Helper from "@/helpers/helper.service";
import IconeCategoria from "@/components/IconeCategoria";

export default {
  name: "Categoria",
  components: {IconeCategoria},
  data() {
    return {
      Categorias: [],
      timeOut: null
    }
  },
  setup() {
    const pureColor = null
    const gradientColor = null
    return {pureColor, gradientColor}
  },
  async mounted() {
    try {

      await this.listaCategorias();

    } catch (e) {
      Helper.messageExceptionHandler(e)
    }
  },
  methods: {
    async updateCategoria(item, imediato) {

      try {

        await clearTimeout(this.timeOut)

        const salvar = async () => {

          const data = {
            Id: item.Id,
            CategoriaNome: item.CategoriaNome,
            IconColor: item.IconColor,
            ExibirGraficos: !!item.ExibirGraficos
          }

          const result = await categoriaService.updateCategoria(data)
          if (!result.Success)
            throw new Error(Helper.getMessageError(result))

          swalToast.fire('','categoria atualizada','success')

        }

        if (imediato)
          await salvar()
        else
          this.timeOut = setTimeout(salvar, 2000)

      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    },
    async listaCategorias() {

      swalLoading.fire()

      const result = await categoriaService.listCategoria()
      if (!result.Success)
        throw new Error(Helper.getMessageError(result))

      this.Categorias = result.Data.map(i => {
        return {
          ...i,
          ExibirGraficos: i.ExibirGraficos !== false && i.ExibirGraficos !== 0 && i.ExibirGraficos !== '0'
        }
      });

      swalLoading.close()

    }
  },
}
</script>

<style scoped>
.table-financeiro tbody tr:hover {
  cursor: pointer;
  background-color: rgba(0, 0, 0, 0.04);
}

</style>
