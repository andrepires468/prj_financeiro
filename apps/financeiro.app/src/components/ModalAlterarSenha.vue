<template>

  <BaseModal
    id="modalAlterarSenha"
    title="Alterar senha"
    title-id="modalAlterarSenhaTitulo"
    close-button-id="fecharModalAlterarSenha"
    dialog-class="modal-sm"
    @hidden="resetForm"
  >
    <div class="form-group mb-3">
      <label class="form-control-label" for="senhaAtual">
        <FontAwesomeIcon icon="key" class="icon" aria-hidden="true"/>
        Senha atual
      </label>
      <div class="input-group">
        <input id="senhaAtual" v-model="senhaAtual" class="form-control" autocomplete="current-password"
               :type="revelar.atual ? 'text' : 'password'">
        <button type="button" class="btn btn-outline-secondary btn-reveal-senha" tabindex="-1"
                aria-label="Mostrar senha enquanto pressionar"
                @pointerdown.prevent="revelar.atual = true"
                @pointerup="revelar.atual = false"
                @pointerleave="revelar.atual = false"
                @pointercancel="revelar.atual = false">
          <FontAwesomeIcon icon="eye" aria-hidden="true"/>
        </button>
      </div>
    </div>
    <div class="form-group mb-3">
      <label class="form-control-label" for="novaSenha">
        <FontAwesomeIcon icon="lock" class="icon" aria-hidden="true"/>
        Nova senha
      </label>
      <div class="input-group">
        <input id="novaSenha" v-model="novaSenha" class="form-control" autocomplete="new-password"
               :type="revelar.nova ? 'text' : 'password'">
        <button type="button" class="btn btn-outline-secondary btn-reveal-senha" tabindex="-1"
                aria-label="Mostrar senha enquanto pressionar"
                @pointerdown.prevent="revelar.nova = true"
                @pointerup="revelar.nova = false"
                @pointerleave="revelar.nova = false"
                @pointercancel="revelar.nova = false">
          <FontAwesomeIcon icon="eye" aria-hidden="true"/>
        </button>
      </div>
    </div>
    <div class="form-group mb-0">
      <label class="form-control-label" for="confirmarNovaSenha">
        <FontAwesomeIcon icon="lock" class="icon" aria-hidden="true"/>
        Confirmar nova senha
      </label>
      <div class="input-group">
        <input id="confirmarNovaSenha" v-model="confirmarNovaSenha" class="form-control" autocomplete="new-password"
               :type="revelar.confirmar ? 'text' : 'password'">
        <button type="button" class="btn btn-outline-secondary btn-reveal-senha" tabindex="-1"
                aria-label="Mostrar senha enquanto pressionar"
                @pointerdown.prevent="revelar.confirmar = true"
                @pointerup="revelar.confirmar = false"
                @pointerleave="revelar.confirmar = false"
                @pointercancel="revelar.confirmar = false">
          <FontAwesomeIcon icon="eye" aria-hidden="true"/>
        </button>
      </div>
    </div>

    <template #footer>
      <button type="button" class="btn btn-cancel" data-bs-dismiss="modal" @click.capture="moveFocusBeforeClose">Cancelar</button>
      <button type="button" class="btn btn-primary" :disabled="enviando" @click="submeter">Alterar senha</button>
    </template>
  </BaseModal>

</template>

<script>
import BaseModal from '@/components/BaseModal.vue'
import authService from '@/services/Auth.service'
import Helper from '@/helpers/helper.service'
import Swal from 'sweetalert2/dist/sweetalert2.js'

export default {
  name: 'ModalAlterarSenha',
  components: { BaseModal },
  data() {
    return {
      senhaAtual: '',
      novaSenha: '',
      confirmarNovaSenha: '',
      enviando: false,
      revelar: { atual: false, nova: false, confirmar: false }
    }
  },
  methods: {
    moveFocusBeforeClose() {
      if (document.activeElement && document.activeElement instanceof HTMLElement) {
        document.activeElement.blur()
      }
    },
    resetForm() {
      this.senhaAtual = ''
      this.novaSenha = ''
      this.confirmarNovaSenha = ''
      this.enviando = false
      this.revelar = { atual: false, nova: false, confirmar: false }
    },
    validarCliente() {
      if (!this.senhaAtual || !this.novaSenha || !this.confirmarNovaSenha) {
        swalAlert.fire('Atenção', 'Preencha todos os campos.', 'warning')
        return false
      }
      if (this.senhaAtual.length < 4 || this.novaSenha.length < 4) {
        swalAlert.fire('Atenção', 'As senhas devem ter pelo menos 4 caracteres.', 'warning')
        return false
      }
      if (this.novaSenha !== this.confirmarNovaSenha) {
        swalAlert.fire('Atenção', 'A nova senha e a confirmação não conferem.', 'warning')
        return false
      }
      return true
    },
    fecharModal() {
      const btn = document.getElementById('fecharModalAlterarSenha')
      if (btn) {
        btn.click()
      }
    },
    async submeter() {
      if (!this.validarCliente()) {
        return
      }
      const confirm = await Swal.fire({
        title: 'Confirmar alteração',
        text: 'Deseja realmente alterar a senha?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, alterar',
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false,
        confirmButtonColor: '#258F71',
        cancelButtonColor: '#ae2828'
      })
      if (!confirm.isConfirmed) {
        return
      }
      this.enviando = true
      try {
        const result = await authService.changePassword({
          CurrentPassword: this.senhaAtual,
          NewPassword: this.novaSenha,
          NewPasswordConfirmation: this.confirmarNovaSenha
        })
        if (!result.Success) {
          throw new Error(Helper.getMessageError(result))
        }
        this.fecharModal()
        swalToast.fire('', 'Senha alterada com sucesso', 'success')
      } catch (e) {
        Helper.messageExceptionHandler(e)
      } finally {
        this.enviando = false
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
