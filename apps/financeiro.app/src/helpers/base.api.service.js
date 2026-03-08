import userService from './user.service'
import baseHttpService from './base.http.service'
import {router} from '../router';

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

    if (!result.Success && result.HttpCode == 401) {

      // auth error
      if (result.Code == 3) {
        await router.push('/login')

        // permission error
      } else if (result.Code == 5) {
        swalToast.fire('Acesso Negado', result.Message, 'warning')
        await router.push('/404')
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
