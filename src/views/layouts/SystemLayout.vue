<template>

  <router-view v-slot='{ Component }'>

    <nav class="navbar navbar-expand-lg navbar-light bg-light bg-white fixed-top shadow-sm">
      <div class="container">

        <router-link to="/financeiro" class="navbar-brand d-flex align-items-center">
          <strong>financeiro.app</strong>
        </router-link>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo02"
                aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <router-link class="nav-link" to="/financeiro">Financeiro</router-link>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarCadastro" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Cadastros
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarCadastro">
                  <li><router-link class="dropdown-item" to="/cadastro/categoria">Categoria</router-link></li>
              </ul>
            </li>
          </ul>
          <div class="d-flex">
            <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarLogin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                {{ Usuario.Nome }}
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarLogin">
                <li><a class="dropdown-item" @click="sair">Sair</a></li>
              </ul>
            </li>
            </ul>

          </div>
        </div>
      </div>
    </nav>

    <div class="container content-body">
      <component :is='Component'/>
    </div>

    <hr class="text-muted m-5"/>

    <footer class="text-muted py-5">
      <div class="container">
        <p class="float-end mb-1">
          <a href="#">Back to top</a>
        </p>
        <p class="mb-1">Album example is © Bootstrap, but please download and customize it for yourself!</p>
        <p class="mb-0">New to Bootstrap? <a href="/">Visit the homepage</a> or read our <a
            href="/docs/5.0/getting-started/introduction/">getting started guide</a>.</p>
      </div>
    </footer>

  </router-view>

</template>

<script>

import authService from '@/services/Auth.service'
import userService from '../../helpers/user.service'
import Helper from "@/helpers/helper.service"
import moment from "moment"

export default {
  name: "SystemLayout",
  data() {
    return {
      Usuario: userService.getUser()
    }
  },
  beforeMount() {
    this.$router.beforeEach(async (to, from, next) => {

      const user = userService.getLocalUser()
      const refresh_time = moment.unix(user.created_at).add(1, 'hours').format("YYYY-MM-DD HH:mm:ss");

      if (moment(new Date(refresh_time)).diff(new Date(), 'minutes') > 60) {

        swalLoading.fire('', 'Atualizando token do usuário, aguarde...')
        await authService.refreshToken()
        swalLoading.close();

      }
      next();

    })
  },
  methods: {
    async sair() {

      try {

        swalLoading.fire('', 'Desconectando, aguarde...');

        const result = await authService.Logout()
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        this.$router.push('/Login')
        swalLoading.close()

      } catch (e) {
        Helper.messageExceptionHandler(e)
      }

    }
  }
}
</script>

<style scoped>

</style>
