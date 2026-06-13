<template>

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-5 pb-2 mb-3 border-bottom">
    <h1>Cadastro / Banco Extrato</h1>
  </div>

  <form @submit.prevent="salvar" autocomplete="off" novalidate>

    <div class="row g-3">

      <div class="col-12 col-md-6">
        <label class="form-label" for="bancoNome">
          <FontAwesomeIcon icon="building-columns" class="icon" aria-hidden="true"/>
          Banco
        </label>
        <input id="bancoNome"
               class="form-control"
               type="text"
               :value="form.BancoNome"
               readonly
               disabled>
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label" for="gmailUser">
          <FontAwesomeIcon icon="envelope" class="icon" aria-hidden="true"/>
          Gmail (usuário)
        </label>
        <input id="gmailUser"
               v-model="form.GmailUser"
               class="form-control"
               type="email"
               autocomplete="off"
               placeholder="seuemail@gmail.com">
      </div>

      <div class="col-12 col-md-6">
        <label class="form-label" for="gmailPass">
          <FontAwesomeIcon icon="key" class="icon" aria-hidden="true"/>
          Gmail (senha de app)
          <small v-if="gmailPassConfigurado" class="text-muted ms-1">— já configurada</small>
        </label>
        <div class="input-group">
          <input id="gmailPass"
                 v-model="form.GmailPass"
                 class="form-control"
                 :type="revelarSenha ? 'text' : 'password'"
                 autocomplete="new-password"
                 :placeholder="gmailPassConfigurado ? 'Deixe em branco para manter a atual' : 'xxxx xxxx xxxx xxxx'">
          <button type="button"
                  class="btn btn-outline-secondary btn-reveal-senha"
                  tabindex="-1"
                  aria-label="Mostrar senha enquanto pressionar"
                  @pointerdown.prevent="revelarSenha = true"
                  @pointerup="revelarSenha = false"
                  @pointerleave="revelarSenha = false"
                  @pointercancel="revelarSenha = false">
            <FontAwesomeIcon icon="eye" aria-hidden="true"/>
          </button>
        </div>
        <div class="form-text">
          Use a senha de aplicativo do Google (16 caracteres). Os espaços são removidos automaticamente.
        </div>
      </div>

      <div class="col-6 col-md-3">
        <label class="form-label" for="mesInicio">
          <FontAwesomeIcon icon="calendar" class="icon" aria-hidden="true"/>
          Mês de início
        </label>
        <select id="mesInicio" v-model="form.MesInicio" class="form-select">
          <option :value="null">—</option>
          <option v-for="m in meses" :key="m.Id" :value="m.Id">{{ m.Id }} — {{ m.Nome }}</option>
        </select>
      </div>

      <div class="col-6 col-md-3">
        <label class="form-label" for="anoInicio">
          <FontAwesomeIcon icon="calendar-days" class="icon" aria-hidden="true"/>
          Ano de início
        </label>
        <input id="anoInicio"
               v-model.number="form.AnoInicio"
               class="form-control"
               type="number"
               min="2000"
               max="2100"
               placeholder="2024">
      </div>

    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary" :disabled="salvando">
        <FontAwesomeIcon icon="check" class="icon" aria-hidden="true"/>
        Salvar alterações
      </button>
      <button type="button" class="btn btn-outline-secondary" :disabled="salvando" @click="carregar">
        <FontAwesomeIcon icon="arrow-rotate-right" class="icon" aria-hidden="true"/>
        Recarregar
      </button>
    </div>

  </form>

</template>

<script>
import bancoExtratoService from "@/services/BancoExtrato.service"
import Helper from "@/helpers/helper.service"
import mesesModel from "@/models/meses.model"

export default {
  name: "BancoExtrato",
  data() {
    return {
      form: {
        Id: null,
        BancoNome: '',
        GmailUser: '',
        GmailPass: '',
        MesInicio: null,
        AnoInicio: null
      },
      gmailPassConfigurado: false,
      revelarSenha: false,
      salvando: false,
      meses: mesesModel
    }
  },
  async mounted() {
    await this.carregar()
  },
  methods: {
    async carregar() {

      try {

        swalLoading.fire()

        const result = await bancoExtratoService.getBancoExtrato()
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        const d = result.Data || {}
        this.form = {
          Id: d.Id ?? null,
          BancoNome: d.BancoNome ?? '',
          GmailUser: d.GmailUser ?? '',
          GmailPass: '',
          MesInicio: d.MesInicio ?? null,
          AnoInicio: d.AnoInicio ?? null
        }
        this.gmailPassConfigurado = !!d.GmailPassConfigurado

        swalLoading.close()

      } catch (e) {
        swalLoading.close()
        Helper.messageExceptionHandler(e)
      }

    },
    validar() {

      const ambosPreenchidos = !!this.form.MesInicio && !!this.form.AnoInicio
      const ambosVazios = !this.form.MesInicio && !this.form.AnoInicio

      if (!ambosPreenchidos && !ambosVazios) {
        swalAlert.fire('Atenção', 'Informe Mês e Ano de início juntos, ou deixe ambos em branco.', 'warning')
        return false
      }

      if (this.form.AnoInicio && (this.form.AnoInicio < 2000 || this.form.AnoInicio > 2100)) {
        swalAlert.fire('Atenção', 'Ano de início deve estar entre 2000 e 2100.', 'warning')
        return false
      }

      return true

    },
    async salvar() {

      if (!this.validar())
        return

      this.salvando = true

      try {

        const payload = {
          GmailUser: (this.form.GmailUser || '').trim(),
          MesInicio: this.form.MesInicio || null,
          AnoInicio: this.form.AnoInicio || null
        }

        if (this.form.GmailPass && this.form.GmailPass.trim() !== '')
          payload.GmailPass = this.form.GmailPass.trim()

        const result = await bancoExtratoService.updateBancoExtrato(payload)
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        swalToast.fire('', 'Banco Extrato atualizado', 'success')

        this.form.GmailPass = ''
        await this.carregar()

      } catch (e) {
        Helper.messageExceptionHandler(e)
      } finally {
        this.salvando = false
      }

    }
  }
}
</script>

<style scoped>
.btn-reveal-senha {
  user-select: none;
  touch-action: manipulation;
}
</style>
