<template>

  <router-view v-slot='{ Component }'>

    <header class="navbar sticky-top flex-md-nowrap p-0 shadow">
      <router-link class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" to="/financeiro">financeiro.app</router-link>
      <div class="container-fluid">
        <div class="dropdown">
          <button class="btn  dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown"
                  aria-expanded="false">
            {{ Usuario.Nome }}
          </button>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
            <li>
              <button class="dropdown-item" @click="sair" type="button">Sair</button>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <div class="container-fluid content-body">
      <div class="row">
        <div class="sidebar col-md-3 col-lg-2 p-0 bg-body-tertiary">
          <div class="offcanvas-md offcanvas-end bg-body-tertiary" tabindex="-1" id="sidebarMenu"
               aria-labelledby="sidebarMenuLabel">
            <div class="offcanvas-header">
              <h5 class="offcanvas-title" id="sidebarMenuLabel">Company name</h5>
              <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"
                      aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">
              <ul class="nav flex-column">
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center gap-2 active" aria-current="page" href="#">
                    <b-icon-bar-chart/>
                    Dashboard
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center gap-2" href="#">
                    <b-icon-currency-dollar/>
                    Finance
                  </a>
                </li>
              </ul>

              <h6
                  class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase">
                <span>Reports</span>
              </h6>
              <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center gap-2" href="#">
                    <b-icon-dash/>
                    Current month
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center gap-2" href="#">
                    <b-icon-dash/>
                    Last quarter
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center gap-2" href="#">
                    <b-icon-dash/>
                    Social engagement
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-5">
          <component :is='Component'/>
        </main>
      </div>
    </div>

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
