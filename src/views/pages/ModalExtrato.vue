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

                <h6><strong>{{ ExtratoSel.BancoNome }}</strong> - {{ ExtratoSel.Data }}</h6>
                <p class="text-muted small">{{ ExtratoSel.Identificador }}</p>

                <div class="form-group">
                  <label class="form-control-label">Categoria</label>
                  <input type="text" class="form-control" id="pesquisaCategoria" v-model="pesquisaCategoria">
                  <div class="categoria-lista">
                    <div class="row categoria-lista-item p-2"
                         v-for="(categoria, index) in ((pesquisaCategoria) ? CategoriaLista.filter( i => i.IconTag.includes(pesquisaCategoria.toLowerCase()) || i.CategoriaNome.toLowerCase().includes(pesquisaCategoria.toLowerCase())) : CategoriaLista)"
                         :key="index"
                         @click="CategoriaSel = categoria">
                      <div class="col-2">
                        <IconeCategoria :icon="categoria.IconTag"
                                     :color="categoria.IconColor"
                                     :width="30" :height="30"/>
                      </div>
                      <div class="col-10 p-2" :style="`color: ${categoria.IconColor}`">
                        {{ categoria.CategoriaNome }}
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="CategoriaSel">
                  <div class="row">
                    <div class="col-2">
                      <IconeCategoria :icon="CategoriaSel.IconTag"
                                   :color="CategoriaSel.IconColor"
                                   :width="30" :height="30"/>
                    </div>
                    <div class="col-10 p-2" :style="`color: ${CategoriaSel.IconColor}`">
                      {{ CategoriaSel.CategoriaNome }}
                    </div>
                  </div>
                </div>
                <div v-else>
                  <p class="text-muted small">Categoria não selecionada</p>
                </div>

                <hr/>

                <div class="form-group">
                  <label class="form-control-label">Descrição Personalizada</label>
                  <input type="text" class="form-control" v-model="descricaoPersonalizada">
                </div>

                <div class="row">
                  <div class="col-8 small">
                    {{ ExtratoSel.Descricao }}
                  </div>
                  <div class="col-4 text-danger">
                    {{ ExtratoSel.Valor }}
                  </div>
                </div>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal" id="fecharModal">Fechar</button>
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
      descricaoPersonalizada: null,
    }
  },
  methods: {
    async updateExtratoSel() {

      try {

        if (!this.ExtratoSel)
          throw new Error('Extrato não selecionado')

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
        if (this.CategoriaSel) {
          this.ExtratoSel.IdCategoria = this.CategoriaSel.Id
          this.ExtratoSel.CategoriaNome = this.CategoriaSel.CategoriaNome
          this.ExtratoSel.IconTag = this.CategoriaSel.IconTag
          this.ExtratoSel.IconColor = this.CategoriaSel.IconColor
        }

        swalToast.fire('', 'extrato atualizado', 'success')

      } catch (e) {
        swalToast.fire('', 'erro ao atualizar extrato', 'error')
        Helper.messageExceptionHandler(e)
      }

    },
  },
  watch:{
    ExtratoSel (){
      this.pesquisaCategoria = null
      this.descricaoPersonalizada =  this.ExtratoSel.DescricaoPersonalizada;
    }
  }
}
</script>

<style scoped>
.categoria-lista {
  height: 170px;
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