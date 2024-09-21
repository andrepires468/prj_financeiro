<template>

  <div class="modal fade" id="modalExtrato" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">

      <div class="settings-widget">
        <div class="settings-inner-blk p-0">
          <div class="sell-course-head comman-space">

            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Extrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
              </div>

              <div class="modal-body" v-if="ExtratoSel">

                <p>
                  <strong>{{ ExtratoSel.BancoNome }}</strong>
                  <span class="float-right">{{ ExtratoSel.Data }}</span>
                </p>

                <div class="row">
                  <div class="col-9 small">
                    <p>{{ ExtratoSel.Descricao }}</p>
                  </div>
                  <div class="col-3">
                    <p :class="`${(ExtratoSel.Tipo == 'entrada') ? 'extrato-entrada' : 'extrato-saida'} text-align-end`">
                      {{ ExtratoSel.Valor }}
                    </p>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-control-label">
                    <b-icon-tag class="icon"/>
                    Categoria Item Extrato</label>
                  <input type="text" class="form-control" id="pesquisaCategoria" v-model="pesquisaCategoria">
                  <div class="categoria-lista">
                    <div class="row categoria-lista-item p-1"
                         v-for="(categoria, index) in ((pesquisaCategoria) ? CategoriaLista.filter( i => i.IconTag.includes(pesquisaCategoria.toLowerCase()) || i.CategoriaNome.toLowerCase().includes(pesquisaCategoria.toLowerCase())) : CategoriaLista)"
                         :key="index"
                         @click="CategoriaSel = categoria">
                      <div class="col-2">
                        <IconeCategoria :icon="categoria.IconTag"
                                        :color="categoria.IconColor"
                                        :width="30" :height="30"/>
                      </div>
                      <div class="col-10 p-0 pt-1" :style="`color: ${categoria.IconColor}`">
                        {{ categoria.CategoriaNome }}
                      </div>
                    </div>
                  </div>
                </div>


                <div class="form-group" v-if="CategoriaSel">
                  <div class="row p-2">
                    <div class="col-2">
                      <IconeCategoria :icon="CategoriaSel.IconTag"
                                      :color="CategoriaSel.IconColor"
                                      :width="30" :height="30"/>
                    </div>
                    <div class="col-6" :style="`color: ${CategoriaSel.IconColor}`">
                      {{ CategoriaSel.CategoriaNome }}
                    </div>
                    <div class="col-4">
                      <a class="btn btn-sm btn-fill btn-outline-danger" @click="CategoriaSel = null">
                        <b-icon-x class="icon"/>
                        Remover
                      </a>
                    </div>
                  </div>
                </div>
                <div v-else>
                  <p class="text-muted small align-center">Categoria não selecionada</p>
                </div>


                <div class="form-group">
                  <label class="form-control-label">
                    <b-icon-pencil-square class="icon"/>
                    Descrição Personalizada</label>
                  <textarea class="form-control" v-model="descricaoPersonalizada"/>
                </div>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal" id="fecharModal">Cancelar</button>
                <button type="button" class="btn btn-primary" @click="updateExtratoSel">Salvar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script>
import financeiroService from "@/services/Financeiro.service";
import Helper from "@/helpers/helper.service";
import IconeCategoria from '../../components/IconeCategoria'

export default {
  name: "ModalExtrato",
  components: {IconeCategoria},
  props: {
    ExtratoSel: null,
    CategoriaLista: null,
    CategoriaSel: null,
  },
  data() {
    return {
      pesquisaCategoria: null,
      descricaoPersonalizada: null
    }
  },
  methods: {
    async updateExtratoSel() {

      try {

        document.getElementById('fecharModal').click();

        const dados = {
          IdExtrato: this.ExtratoSel.Id,
          IdCategoria: this.CategoriaSel?.Id ?? null,
          DescricaoPersonalizada: this.descricaoPersonalizada
        }

        const result = await financeiroService.updateExtrato(dados)
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        this.ExtratoSel.DescricaoPersonalizada = this.descricaoPersonalizada;
        this.ExtratoSel.IdCategoria = this.CategoriaSel?.Id ?? null
        this.ExtratoSel.CategoriaNome = this.CategoriaSel?.CategoriaNome ?? null
        this.ExtratoSel.IconTag = this.CategoriaSel?.IconTag ?? null
        this.ExtratoSel.IconColor = this.CategoriaSel?.IconColor ?? null

        swalToast.fire('', 'extrato atualizado', 'success')

      } catch (e) {
        swalToast.fire('', 'erro ao atualizar extrato', 'error')
        Helper.messageExceptionHandler(e)
      }

    },
  },
  watch: {
    ExtratoSel() {
      this.pesquisaCategoria = null
      this.descricaoPersonalizada = this.ExtratoSel.DescricaoPersonalizada

      this.timeOut = setTimeout(async () => {
        document.getElementById('pesquisaCategoria').focus();
      }, 150)

    }
  }
}
</script>

<style scoped>
.categoria-lista {
  margin-top: 4px;
  height: 200px;
  overflow-y: auto;
  overflow-x: hidden;
  border: 1px solid #cccccc;
  border-radius: 3px;
}

.categoria-lista .categoria-lista-item {
  cursor: pointer;
}

.categoria-lista .categoria-lista-item:hover {
  background-color: rgba(0, 0, 0, 0.08);
}

</style>