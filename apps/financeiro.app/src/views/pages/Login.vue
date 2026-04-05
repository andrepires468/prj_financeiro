<template>

  <div class="row">
    <div class="col-md-4 col-sm-12"></div>
    <div class="col-md-4 col-sm-12">

      <h2 class="login-presentation align-center p-5">financeiro.app</h2>

      <div class="login-painel">

        <h4>Login</h4>

        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label class="form-control-label">Email</label>
              <input type="text" class="form-control" placeholder="" v-model="Email">
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label class="form-control-label" for="loginPassword">Password</label>
              <div class="input-group">
                <input id="loginPassword" v-model="Password" class="form-control" placeholder="" autocomplete="current-password"
                       :type="revelarSenha ? 'text' : 'password'">
                <button type="button" class="btn btn-outline-secondary btn-reveal-senha" tabindex="-1"
                        aria-label="Mostrar senha enquanto pressionar"
                        @pointerdown.prevent="revelarSenha = true"
                        @pointerup="revelarSenha = false"
                        @pointerleave="revelarSenha = false"
                        @pointercancel="revelarSenha = false">
                  <b-icon-eye aria-hidden="true"/>
                </button>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <button class="btn btn-outline-primary" @click="login">Conectar</button>
            </div>
          </div>
        </div>

      </div>

    </div>
    <div class="col-md-4 col-sm-12"></div>
  </div>

</template>

<script>

import authService from '../../services/Auth.service'
import Helper from '../../helpers/helper.service'

export default {
  name: "Login",
  data() {
    return {
      Email: null,
      Password: null,
      revelarSenha: false
    }
  },
  methods: {
    async login() {

      try {

        swalLoading.fire('', 'Realizando Login...')

        const data = {
          Email: this.Email,
          Password: this.Password,
        }

        const result = await authService.login(data)
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        this.$router.push('/financeiro')

        swalLoading.close()

      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    }
  }
}
</script>

<style scoped>

.login-painel {
  margin-top: 60px;
}

.btn-reveal-senha {
  user-select: none;
  touch-action: manipulation;
}

</style>
