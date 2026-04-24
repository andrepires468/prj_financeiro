import userService from './user.service'
import baseHttpService from './base.http.service'
import {router} from '../router';

let unauthorizedHandling = false

async function handleUnauthorized(result) {
  if (unauthorizedHandling) return
  unauthorizedHandling = true
  try {
    const message = (result && result.Message)
      ? result.Message
      : 'Não autorizado, faça o login novamente'

    userService.destroyUser()

    if (typeof window !== 'undefined' && window.swalAlert) {
      await window.swalAlert.fire('Sessão expirada', message, 'warning')
    }

    if (!router.currentRoute.value || router.currentRoute.value.path !== '/login') {
      await router.push('/login')
    }
  } finally {
    unauthorizedHandling = false
  }
}

export default {
  async executeTokenMethod(url, method, body) {

    const user = userService.getLocalUser();

    if (!url || url == undefined) throw new Error("Url não passada");
    if (!method || method == undefined) throw new Error("Método não passado");

    if (!user || !user.accessToken) {
      await router.push('/login');
      throw new Error("Sessão não encontrada, entre novamente com a sua conta")
    }

    const result = await baseHttpService.executeAPI(url, method, body, {
      "Content-Type": "application/json",
      "Authorization": "Bearer " + user.accessToken
    });

    if (result && result.Success === false && result.HttpCode == 401) {

      // permission error (mantém comportamento original)
      if (result.Code == 5) {
        if (typeof window !== 'undefined' && window.swalToast) {
          window.swalToast.fire('Acesso Negado', result.Message, 'warning')
        }
        await router.push('/404')
      } else {
        // token inválido/expirado/não autorizado -> alerta + logout + redirect
        await handleUnauthorized(result)
      }

    }

    return result;

  },
  async executeAnonymousMethod(url, method, body) {
    const result = await baseHttpService.executeAPI(url, method, body, {
      "Content-Type": "application/json"
    });
    return result;
  },
  async executeExternalMethod(url) {
    const result = await baseHttpService.executeExternal(url, "GET", {
      "Content-Type": "application/json"
    });
    return result;
  }
};
