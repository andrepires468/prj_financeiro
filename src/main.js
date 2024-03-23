import { createApp } from 'vue'
import App from './App.vue'

import { BootstrapIconsPlugin } from "bootstrap-icons-vue";

import VueSweetalert2 from 'vue-sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'
import Swal from "sweetalert2/dist/sweetalert2.js";

import {router} from './router'

import 'bootstrap/dist/css/bootstrap.css'
import './assets/css/default.css'


const SwalOptions = {
  allowOutsideClick: false,
  confirmButtonColor: '#258F71',
  cancelButtonColor: '#ae2828'
}

const swalAlert = Swal.mixin(
  {
    ...SwalOptions,
    didOpen: () => {
      Swal.hideLoading();
    },
  }
);
window.swalAlert = swalAlert;


const swalToast = Swal.mixin({
  toast: true,
  position: "top-end",
  timer: 7000,
  timerProgressBar: true,
  showConfirmButton: false,
  showCloseButton: true,
  didOpen: () => {
    Swal.hideLoading();
  }
});
window.swalToast = swalToast;

const swalLoading = Swal.mixin({
  allowOutsideClick: false,
  title: '',
  html: 'Carregando...',
  timerProgressBar: true,
  didOpen: () => {
    Swal.showLoading()
  },
});
window.swalLoading = swalLoading;



window.addEventListener("offline", (event) => {
  window.swalLoading.fire('offline', 'Verifique a sua conexão com a internet', 'warning')
  console.info('[System] offline');
});

window.addEventListener("online", (event) => {
  window.swalToast.close()
  console.info('[System] online');
});


const app = createApp(App)

app.use(BootstrapIconsPlugin)

app.use(router)
    .mount('#app')



