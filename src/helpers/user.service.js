const USER = 'user'
import {router} from '../router'

export default {
  getLocalUser() {

    if (window.localStorage.getItem(USER) && window.localStorage.getItem(USER) !== undefined)
      return JSON.parse(window.localStorage.getItem(USER))

    console.log("[user.service] Sessão de usuário não encontrada");
    swalToast.fire('', 'Sessão não encontrada, entre novamente com a sua conta', 'warning');
    router.push("/login")
    return undefined;

  },
  getUser() {
    const user = this.getLocalUser()
    return user.Usuario.Usuario
  },
  getPermissions() {
    const user = this.getLocalUser()
    return user.Usuario.Permissoes
  },
  getModalidade() {
    const user = this.getLocalUser()
    return user.Usuario.Modalidade
  },
  isAdmin() {
    const user = this.getUser()
    if (user.Admin)
      return true
    return false
  },
  checkPermision(permissaoNome) {
    const user = this.getLocalUser()
    if (!user)
      return false

    if (user.Usuario.Usuario.Admin)
      return true

    user.Usuario.Permissoes.each(perm => {
      if (perm.nome == permissaoNome)
        return true
    })

    return true
  },
  saveUser(user) {
    const session = {
      ...user,
      requested: Date.now()
    }
    window.localStorage.setItem(USER, JSON.stringify(session))
  },
  destroyUser() {
    window.localStorage.removeItem(USER)
  },
  isAuthenticated() {
    return (
      window.localStorage.getItem(USER) &&
      window.localStorage.getItem(USER) != 'undefined'
    )
  },
}
