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
  authForgotPassword: APIBaseEndpoints.AuthController + '/forgot-password',
  authChangePassword: APIBaseEndpoints.AuthController + '/change-password',

  // endpoints of LMS models
  financeiro: APIBaseEndpoints.DataController +'/financeiro',
}

export default APIEndpoints;
