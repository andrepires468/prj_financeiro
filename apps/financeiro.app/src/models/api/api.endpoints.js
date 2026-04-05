const APIBaseEndpoints = {
  AuthController: '/auth',
  DataController: '',
}

const APIEndpoints = {

  // endpoints login
  authLogin: APIBaseEndpoints.AuthController + '/login',
  authValidate: APIBaseEndpoints.AuthController + '/validate-login',
  authLogout: APIBaseEndpoints.AuthController + '/logout',
  authRefreshToken: APIBaseEndpoints.AuthController + '/refresh',
  authChangePassword: APIBaseEndpoints.AuthController + '/change-password',

  process: APIBaseEndpoints.DataController +'/process',

  // endpoints of LMS models
  financeiro: APIBaseEndpoints.DataController +'/financeiro',
  categoria: APIBaseEndpoints.DataController +'/categoria',
}

export default APIEndpoints;
