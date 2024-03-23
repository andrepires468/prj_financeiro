<template>

  <router-view v-slot='{ Component }'>

    <header class="navbar sticky-top bg-dark flex-md-nowrap p-0 shadow" data-bs-theme="dark">
      <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 text-white" href="#">financeiro.app</a>

      <!--      <ul class="navbar-nav flex-row d-md-none">-->
      <!--        <li class="nav-item text-nowrap">-->
      <!--          <button class="nav-link px-3 text-white" type="button" data-bs-toggle="collapse"-->
      <!--                  data-bs-target="#navbarSearch"-->
      <!--                  aria-controls="navbarSearch" aria-expanded="false" aria-label="Toggle search">-->
      <!--          </button>-->
      <!--        </li>-->
      <!--        <li class="nav-item text-nowrap">-->
      <!--          <button class="nav-link px-3 text-white" type="button" data-bs-toggle="offcanvas"-->
      <!--                  data-bs-target="#sidebarMenu"-->
      <!--                  aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">-->
      <!--            <svg class="bi">-->
      <!--              <use xlink:href="#list"/>-->
      <!--            </svg>-->
      <!--          </button>-->
      <!--        </li>-->
      <!--      </ul>-->

      <!--      <div id="navbarSearch" class="navbar-search w-100 collapse">-->
      <!--        <input class="form-control w-100 rounded-0 border-0" type="text" placeholder="Search" aria-label="Search">-->
      <!--      </div>-->


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

    <div class="container-fluid">
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

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
          <div
              class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Dashboard</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
              <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
              </div>
            </div>
          </div>

          <component :is='Component'/>

        </main>
      </div>
    </div>

  </router-view>

</template>

<script>

import user from '../../helpers/user.service'
import authService from '@/services/Auth.service'
import Helper from "@/helpers/helper.service";

export default {
  name: "SystemLayout",
  data() {
    return {
      Usuario: user.getUser()
    }
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
