<template>

  <BaseModal id="modalExtrato" :title="modo === 'create' ? 'Item Manual' : 'Extrato'" dialog-class="modal-md">
    <template #body>
      <div class="modal-body" v-if="modo === 'create' || ExtratoSel">

        <div v-if="modo === 'edit'" class="modal-extrato-topo-acoes mb-3 d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-sm btn-outline-danger" :disabled="processando" @click="excluirExtratoSel">
            <FontAwesomeIcon icon="trash" class="icon"/>
            Excluir
          </button>
          <button
            v-if="isExtratoManual"
            type="button"
            class="btn btn-sm"
            :class="editandoManual ? 'btn-outline-warning' : 'btn-outline-secondary'"
            :title="editandoManual ? 'Bloquear edição do lançamento' : 'Editar data, valor, tipo e descrição'"
            @click="alternarEdicaoManual"
          >
            <FontAwesomeIcon v-if="editandoManual" icon="unlock" class="icon" />
            <FontAwesomeIcon v-else icon="lock" class="icon" />
            {{ editandoManual ? 'Edição liberada' : 'Editar lançamento' }}
          </button>
        </div>

        <template v-if="mostrarFormularioManual">
          <div class="form-group">
            <label class="form-control-label">Data</label>
            <DatePicker
              :model="dataManual"
              @update:model="dataManual = $event"
              :min-date="dataManualMinima"
              :max-date="dataManualMaxima"
            />
          </div>

          <div class="form-group">
            <label class="form-control-label">Valor</label>
            <input
              type="text"
              inputmode="decimal"
              class="form-control modal-extrato-valor-input"
              :class="valorManualClasse"
              :value="valorManualTexto"
              placeholder="R$ 0,00"
              @input="onValorManualInput"
              @blur="onValorManualBlur"
            >
            <small class="text-muted">Use o sinal negativo para saídas (ex.: R$ -50,00) e positivo para entradas (ex.: R$ 50,00). Zero não é permitido.</small>
          </div>

          <div class="form-group">
            <label class="form-control-label">Tipo</label>
            <select class="form-control" v-model="idTipoExtratoManual">
              <option v-for="tipo in TipoExtratoLista" :key="tipo.Id" :value="Number(tipo.Id)">
                {{ tipo.Nome }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-control-label">Descrição</label>
            <textarea class="form-control" v-model="descricaoManual" rows="2" />
          </div>
        </template>

        <template v-else-if="modo === 'edit'">
          <p>
            <strong>{{ ExtratoSel.BancoNome }}</strong>
            <span class="float-right">{{ ExtratoSel.Data }}</span>
          </p>

          <div class="row">
            <div class="col-9 small">
              <p>{{ ExtratoSel.Descricao }}</p>
              <p class="mb-0" v-if="ExtratoSel.TipoExtratoNome">
                <span
                  :class="{ 'text-muted': !ExtratoSel.TipoExtratoCor }"
                  :style="ExtratoSel.TipoExtratoCor ? { color: ExtratoSel.TipoExtratoCor } : null">
                  {{ ExtratoSel.TipoExtratoNome }}
                </span>
                <span v-if="ExtratoSel.Origem === 'proc_sinc_empresa'" class="extrato-origem-empresa"> - Empresa</span>
              </p>
              <p class="mb-0 small text-muted" v-if="ExtratoSel.Origem === 'manual'">Cadastrado manualmente</p>
            </div>
            <div class="col-3">
              <p :class="`${(ExtratoSel.Tipo == 'entrada') ? 'extrato-entrada' : 'extrato-saida'} text-align-end`">
                {{ ocultarValores ? '••••••' : ExtratoSel.Valor }}
              </p>
            </div>
          </div>
        </template>

        <div class="form-group">
          <label class="form-control-label">
            <FontAwesomeIcon icon="tag" class="icon"/>
            Categoria Item Extrato</label>
          <input type="text" class="form-control" id="pesquisaCategoria" v-model="pesquisaCategoria">
          <div class="categoria-lista">
            <div class="row categoria-lista-item p-1"
                 v-for="(categoria, index) in ((pesquisaCategoria) ? CategoriaLista.filter( i => i.IconTag.includes(pesquisaCategoria.toLowerCase()) || i.CategoriaNome.toLowerCase().includes(pesquisaCategoria.toLowerCase())) : CategoriaLista)"
                 :key="index"
                 @click="selecionarCategoria(categoria)">
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


        <div class="form-group" v-if="categoriaSelecionada">
          <div class="row p-2">
            <div class="col-2">
              <IconeCategoria :icon="categoriaSelecionada.IconTag"
                              :color="categoriaSelecionada.IconColor"
                              :width="30" :height="30"/>
            </div>
            <div class="col-6" :style="`color: ${categoriaSelecionada.IconColor}`">
              {{ categoriaSelecionada.CategoriaNome }}
            </div>
            <div class="col-4">
              <button type="button" class="btn btn-sm btn-outline-danger btn-fill" @click="selecionarCategoria(null)">
                <FontAwesomeIcon icon="xmark" class="icon"/>
                Remover
              </button>
            </div>
          </div>
        </div>
        <div v-else>
          <p class="text-muted small align-center">Categoria não selecionada</p>
        </div>


        <div class="form-group">
          <label class="form-control-label">
            <FontAwesomeIcon icon="pen-to-square" class="icon"/>
            Descrição Personalizada</label>
          <textarea class="form-control" v-model="descricaoPersonalizada"/>
        </div>

      </div>
    </template>
    <template #footer>
      <button type="button" class="btn btn-cancel" data-bs-dismiss="modal" id="fecharModal" @click.capture="moveFocusBeforeClose">Cancelar</button>
      <button type="button" class="btn btn-primary" :disabled="processando" @click="salvarExtratoSel">
        {{ processando ? 'Salvando...' : 'Salvar' }}
      </button>
    </template>
  </BaseModal>

</template>

<script>
import Swal from 'sweetalert2'
import BaseModal from '@/components/BaseModal.vue'
import DatePicker from '@/components/DatePicker.vue'
import financeiroService from "@/services/Financeiro.service";
import Helper from "@/helpers/helper.service";
import IconeCategoria from '../../components/IconeCategoria'
import moment from 'moment'

export default {
  name: "ModalExtrato",
  components: { BaseModal, DatePicker, IconeCategoria },
  emits:['updateExtrato'],
  props: {
    modo: { type: String, default: 'edit' },
    ExtratoSel: null,
    CategoriaLista: null,
    CategoriaSel: null,
    TipoExtratoLista: { type: Array, default: () => [] },
    ocultarValores: { type: Boolean, default: false },
    CalendarioSel: { type: Object, default: null },
  },
  computed: {
    isExtratoManual() {
      return this.modo === 'edit' && this.ExtratoSel?.Origem === 'manual'
    },
    mostrarFormularioManual() {
      return this.modo === 'create' || (this.isExtratoManual && this.editandoManual)
    },
    valorManualClasse() {
      if (this.valorManual === null || this.valorManual === '')
        return null

      const valor = parseFloat(this.valorManual)
      if (Number.isNaN(valor) || valor === 0)
        return null

      return valor > 0 ? 'modal-extrato-valor--entrada' : 'modal-extrato-valor--saida'
    },
    dataManualMinima() {
      if (!this.CalendarioSel?.Mes || !this.CalendarioSel?.Ano)
        return null
      return new Date(this.CalendarioSel.Ano, this.CalendarioSel.Mes - 1, 1)
    },
    dataManualMaxima() {
      if (!this.CalendarioSel?.Mes || !this.CalendarioSel?.Ano)
        return null
      return new Date(this.CalendarioSel.Ano, this.CalendarioSel.Mes, 0)
    },
  },
  data() {
    return {
      pesquisaCategoria: null,
      descricaoPersonalizada: null,
      categoriaSelecionada: null,
      dataManual: null,
      valorManual: null,
      valorManualTexto: '',
      idTipoExtratoManual: 1,
      descricaoManual: null,
      processando: false,
      editandoManual: false,
    }
  },
  methods: {
    alternarEdicaoManual() {
      this.editandoManual = !this.editandoManual
      this.carregarCamposManualDoExtrato()
    },
    carregarCamposManualDoExtrato() {
      if (!this.ExtratoSel || this.ExtratoSel.Origem !== 'manual')
        return

      const data = moment(this.ExtratoSel.Data, 'DD/MM/YYYY', true)
      this.dataManual = data.isValid() ? data.toDate() : null
      this.valorManual = this.ExtratoSel.ValorNumerico ?? null
      this.valorManualTexto = Helper.formatCurrencyInput(this.valorManual)
      this.idTipoExtratoManual = Number(this.ExtratoSel.IdTipoExtrato) || 1
      this.descricaoManual = this.ExtratoSel.Descricao ?? null
    },
    onValorManualInput(event) {
      const raw = event.target.value ?? ''
      const negativo = raw.includes('-')
      const digits = raw.replace(/[^\d]/g, '')

      if (!digits) {
        this.valorManual = null
        this.valorManualTexto = negativo ? 'R$ -' : ''
        return
      }

      const numero = parseInt(digits, 10) / 100
      this.valorManual = negativo ? -numero : numero
      this.valorManualTexto = Helper.formatCurrencyInput(this.valorManual)
    },
    onValorManualBlur() {
      if (this.valorManual === null || Number.isNaN(this.valorManual)) {
        this.valorManualTexto = ''
        return
      }
      this.valorManualTexto = Helper.formatCurrencyInput(this.valorManual)
    },
    validarValorManual() {
      const valor = this.valorManual ?? Helper.parseCurrencyMaskedInput(this.valorManualTexto)
      if (valor === null || Number.isNaN(parseFloat(valor)))
        return 'Informe um valor válido'
      if (parseFloat(valor) === 0)
        return 'O valor não pode ser zero'
      this.valorManual = parseFloat(valor)
      return null
    },
    validarCamposManual() {
      if (!this.dataManual)
        return 'Informe a data do lançamento'

      if (this.dataManualMinima && this.dataManualMaxima) {
        const data = moment(this.dataManual).startOf('day')
        const min = moment(this.dataManualMinima).startOf('day')
        const max = moment(this.dataManualMaxima).startOf('day')
        if (data.isBefore(min) || data.isAfter(max))
          return 'A data deve estar dentro do mês selecionado no extrato'
      }

      const erroValor = this.validarValorManual()
      if (erroValor)
        return erroValor

      if (!this.descricaoManual || !this.descricaoManual.trim())
        return 'Informe a descrição do lançamento'
      if (!this.idTipoExtratoManual)
        return 'Selecione o tipo do lançamento'
      return null
    },
    selecionarCategoria(categoria) {
      this.categoriaSelecionada = categoria
    },
    resetCamposCreate() {
      const hoje = moment().startOf('day')
      const min = this.dataManualMinima ? moment(this.dataManualMinima).startOf('day') : null
      const max = this.dataManualMaxima ? moment(this.dataManualMaxima).startOf('day') : null

      if (min && max && hoje.isBetween(min, max, 'day', '[]'))
        this.dataManual = hoje.toDate()
      else if (min)
        this.dataManual = min.toDate()
      else
        this.dataManual = new Date()

      this.valorManual = null
      this.valorManualTexto = ''
      this.idTipoExtratoManual = 1
      this.descricaoManual = null
      this.descricaoPersonalizada = null
      this.categoriaSelecionada = null
      this.pesquisaCategoria = null
    },
    validarCreate() {
      return this.validarCamposManual()
    },
    /** Remove o foco do elemento antes do Bootstrap aplicar aria-hidden, evitando warning de acessibilidade. */
    moveFocusBeforeClose() {
      if (document.activeElement && document.activeElement instanceof HTMLElement) {
        document.activeElement.blur();
      }
    },
    fecharModal() {
      document.getElementById('fecharModal')?.click()
    },
    async salvarExtratoSel() {
      if (this.modo === 'create')
        return this.createExtratoSel()
      return this.updateExtratoSel()
    },
    async createExtratoSel() {
      const erro = this.validarCreate()
      if (erro) {
        swalToast.fire('', erro, 'warning')
        return
      }

      this.processando = true
      try {
        const dados = {
          Data: moment(this.dataManual).format('YYYY-MM-DD'),
          Valor: this.valorManual,
          Descricao: this.descricaoManual.trim(),
          IdTipoExtrato: this.idTipoExtratoManual,
          IdCategoria: this.categoriaSelecionada?.Id ?? null,
          DescricaoPersonalizada: this.descricaoPersonalizada
        }

        const result = await financeiroService.createExtratoManual(dados)
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        this.fecharModal()
        swalToast.fire('', 'extrato criado', 'success')
        this.$emit('updateExtrato');

      } catch (e) {
        swalToast.fire('', 'erro ao criar extrato', 'error')
        Helper.messageExceptionHandler(e)
      } finally {
        this.processando = false
      }
    },
    async updateExtratoSel() {
      if (this.isExtratoManual && this.editandoManual) {
        const erro = this.validarCamposManual()
        if (erro) {
          swalToast.fire('', erro, 'warning')
          return
        }
      }

      this.processando = true
      try {

        const dados = {
          IdExtrato: this.ExtratoSel.Id,
          IdCategoria: this.categoriaSelecionada?.Id ?? null,
          DescricaoPersonalizada: this.descricaoPersonalizada
        }

        if (this.isExtratoManual && this.editandoManual) {
          dados.Data = moment(this.dataManual).format('YYYY-MM-DD')
          dados.Valor = this.valorManual
          dados.Descricao = this.descricaoManual.trim()
          dados.IdTipoExtrato = this.idTipoExtratoManual
        }

        const result = await financeiroService.updateExtrato(dados)
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        this.ExtratoSel.DescricaoPersonalizada = this.descricaoPersonalizada;
        this.ExtratoSel.IdCategoria = this.categoriaSelecionada?.Id ?? null
        this.ExtratoSel.CategoriaNome = this.categoriaSelecionada?.CategoriaNome ?? null
        this.ExtratoSel.IconTag = this.categoriaSelecionada?.IconTag ?? null
        this.ExtratoSel.IconColor = this.categoriaSelecionada?.IconColor ?? null

        if (this.isExtratoManual && this.editandoManual) {
          this.ExtratoSel.Data = moment(this.dataManual).format('DD/MM/YYYY')
          this.ExtratoSel.ValorNumerico = this.valorManual
          this.ExtratoSel.Valor = Helper.formatCurrency(this.valorManual)
          this.ExtratoSel.Descricao = this.descricaoManual.trim()
          this.ExtratoSel.IdTipoExtrato = this.idTipoExtratoManual
          this.ExtratoSel.Tipo = this.valorManual > 0 ? 'entrada' : 'saida'
          this.editandoManual = false
        }

        this.fecharModal()
        swalToast.fire('', 'extrato atualizado', 'success')

        this.$emit('updateExtrato');

      } catch (e) {
        swalToast.fire('', 'erro ao atualizar extrato', 'error')
        Helper.messageExceptionHandler(e)
      } finally {
        this.processando = false
      }

    },
    async excluirExtratoSel() {
      const confirm = await Swal.fire({
        title: 'Excluir item',
        text: 'Deseja excluir este lançamento? Ele não aparecerá mais no extrato.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false,
        confirmButtonColor: '#ae2828',
        cancelButtonColor: '#6c757d'
      })

      if (!confirm.isConfirmed)
        return

      this.processando = true
      try {
        const result = await financeiroService.excluirExtrato({ IdExtrato: this.ExtratoSel.Id })
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        this.fecharModal()
        swalToast.fire('', 'extrato excluído', 'success')
        this.$emit('updateExtrato')

      } catch (e) {
        swalToast.fire('', 'erro ao excluir extrato', 'error')
        Helper.messageExceptionHandler(e)
      } finally {
        this.processando = false
      }
    },
  },
  watch: {
    ExtratoSel() {
      this.pesquisaCategoria = null
      this.editandoManual = false
      if (this.modo === 'create') {
        this.resetCamposCreate()
        return
      }
      this.descricaoPersonalizada = this.ExtratoSel?.DescricaoPersonalizada
      this.categoriaSelecionada = this.CategoriaSel

      if (this.ExtratoSel?.Origem === 'manual')
        this.carregarCamposManualDoExtrato()

      this.timeOut = setTimeout(async () => {
        document.getElementById('pesquisaCategoria')?.focus();
      }, 150)

    },
    CategoriaSel(value) {
      if (this.modo !== 'create')
        this.categoriaSelecionada = value
    },
    modo(value) {
      if (value === 'create')
        this.resetCamposCreate()
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

.modal-extrato-topo-acoes {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
}

.modal-extrato-valor-input.modal-extrato-valor--entrada {
  border-color: var(--bs-success) !important;
  color: var(--bs-success);
}

.modal-extrato-valor-input.modal-extrato-valor--entrada:focus {
  border-color: var(--bs-success) !important;
  box-shadow: 0 0 0 0.25rem rgba(var(--bs-success-rgb, 25, 135, 84), 0.25);
}

.modal-extrato-valor-input.modal-extrato-valor--saida {
  border-color: var(--bs-danger) !important;
  color: var(--bs-danger);
}

.modal-extrato-valor-input.modal-extrato-valor--saida:focus {
  border-color: var(--bs-danger) !important;
  box-shadow: 0 0 0 0.25rem rgba(var(--bs-danger-rgb, 220, 53, 69), 0.25);
}

</style>
