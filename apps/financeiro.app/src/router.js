import {createRouter, createWebHistory} from 'vue-router'
import {h, resolveComponent} from 'vue'
//import Home from './views/pages/Home.vue'
//import moment from "moment";

const routes = [
  {
    path: '/',
    name: '',
    component: () => import('./views/layouts/LoginLayout'),
    redirect: '/login',
    children: [
      {
        path: '/login',
        name: 'Login',
        component: () => import('./views/pages/Login'),
      }
    ]
  },
  {
    path: '/financeiro',
    name: 'home',
    component: () => import('./views/layouts/SystemLayout'),
    redirect: '/financeiro',
    children: [
      {
        path: '/financeiro',
        name: 'Financeiro',
        component: () => import('./views/pages/Financeiro')
      },
      {
        path: '/cadastro',
        name: 'home',
        redirect: '/cadastro',
        children: [
          {
            path: '/cadastro/categoria',
            name: 'Categoria',
            component: () => import('./views/pages/cadastro/Categoria')
          },
          {
            path: '/cadastro/banco-extrato',
            name: 'BancoExtrato',
            component: () => import('./views/pages/cadastro/BancoExtrato')
          }
        ]
      }
    ]
  },
]

export const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL ?? '/'),
  linkActiveClass: 'active',
  routes,
  scrollBehavior() {
    return {top: 0} //// always scroll to top
  },
})
