<template>

  <router-view v-slot='{ Component }'>

    <nav class="navbar navbar-expand-lg fixed-top shadow-sm"
         :class="isDark ? 'navbar-dark bg-dark' : 'navbar-light bg-light bg-white'">
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
          <div class="d-flex align-items-center">
            <button type="button"
                    class="btn btn-link nav-link py-2 px-2 me-1"
                    :title="isDark ? 'Ativar modo claro' : 'Ativar modo escuro'"
                    @click="toggleTheme"
                    aria-label="Alternar modo escuro">
              <b-icon-sun v-if="isDark" class="theme-toggle-icon"/>
              <b-icon-moon v-else class="theme-toggle-icon"/>
            </button>
            <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarLogin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                {{ Usuario.Nome }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarLogin">
                <li>
                  <button type="button" class="dropdown-item btn-user-logout" @click="sair">Sair</button>
                </li>
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
import { getTheme, setTheme, THEME } from '@/helpers/theme.service'
import moment from "moment"
import {router} from "@/router";

export default {
  name: "SystemLayout",
  data() {
    return {
      Usuario: userService.getUser(),
      theme: getTheme()
    }
  },
  computed: {
    isDark() {
      return this.theme === THEME.DARK
    }
  },
  beforeMount() {
    this.$router.beforeEach(async (to, from, next) => {

      // Não exige sessão ao navegar para login (ex.: após logout)
      if (to.path === '/login') {
        next();
        return;
      }

      const user = userService.getLocalUser();
      if (!user) {
        next();
        return;
      }

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
    toggleTheme() {
      this.theme = this.isDark ? THEME.LIGHT : THEME.DARK
      setTheme(this.theme)
    },
    async sair() {

      try {

        swalLoading.fire('', 'Desconectando, aguarde...');

        const result = await authService.Logout()
        if (!result.Success)
          throw new Error(Helper.getMessageError(result))

        swalLoading.close()
        await router.push('/login')

      } catch (e) {
        swalLoading.close()
        Helper.messageExceptionHandler(e)
      }

    }
  }
}
</script>

<style scoped>
.theme-toggle-icon {
  width: 1.25rem;
  height: 1.25rem;
}

/* Garante que o item Sair do dropdown seja clicável no hover */
.btn-user-logout {
  cursor: pointer;
  width: 100%;
  text-align: left;
  border: none;
  background: none;
}
.btn-user-logout:hover {
  cursor: pointer;
}
</style>
