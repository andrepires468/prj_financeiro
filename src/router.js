import {createRouter, createWebHistory} from 'vue-router'
import {h, resolveComponent} from 'vue'
//import Home from './views/pages/Home.vue'
//import moment from "moment";

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('./views/layouts/LoginLayout'),
    redirect: '/login',
    children: [
      {
        path: '/login',
        name: 'Login',
        component: () => import('./views/pages/Login'),
      },
    ]
  },
  {
    path: '/financeiro',
    name: 'home',
    component: () => import('./views/layouts/SystemLayout'),
    redirect: '/login',
    children: [
      {
        path: '/financeiro',
        name: 'Financeiro',
        component: () => import('./views/pages/Financeiro')
      },
    ]
  }
]

export const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  linkActiveClass: 'active',
  routes,
  scrollBehavior() {
    return {top: 0} //// always scroll to top
  },
})
