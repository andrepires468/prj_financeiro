const showAppError = (msg) => {
  const el = document.getElementById('app')
  if (el && !el.querySelector('.app-error')) {
    el.innerHTML = `<div class="app-error p-4" style="color:#c00;font-family:monospace;white-space:pre-wrap;">${msg}\n\nAbra o Console (F12) para mais detalhes.</div>`
  }
}
window.addEventListener('error', (e) => { showAppError('Erro: ' + (e.message || e)); return false })
window.addEventListener('unhandledrejection', (e) => { showAppError('Erro ao carregar: ' + (e.reason?.message || e.reason)); return false })

import { createApp } from 'vue'
import App from './App.vue'

import { FontAwesomeIcon } from '@/plugins/fontawesome'
import VueSweetalert2 from 'vue-sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'
import Swal from "sweetalert2/dist/sweetalert2.js";
import { vMaska } from "maska"
import ColorInput from "vue-color-input";
import {router} from './router'

// componentes
import DataTable from "@/components/DataTable";
import Pagination from "@/components/Pagination";
import DatePicker from "@/components/DatePicker";
import MultselectForm from "@/components/MultselectForm";

import { initTheme } from '@/helpers/theme.service'
initTheme()

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

app.directive("maska", vMaska)
app.component('DataTable', DataTable)
app.component('Pagination', Pagination)
app.component('DatePicker', DatePicker)
app.component('MultselectForm', MultselectForm)

app.use(VueSweetalert2, SwalOptions)
app.component('FontAwesomeIcon', FontAwesomeIcon)
app.use(ColorInput)
app.use(router)

app.config.errorHandler = (err, instance, info) => {
  console.error('[Vue]', info, err)
  const el = document.getElementById('app')
  if (el && !el.querySelector('.app-error')) {
    el.innerHTML = `<div class="app-error p-4" style="color:#c00;font-family:monospace;white-space:pre-wrap;">Erro: ${err?.message || err}\n\nVerifique o Console (F12) para mais detalhes.</div>`
  }
}

app.mount('#app')



