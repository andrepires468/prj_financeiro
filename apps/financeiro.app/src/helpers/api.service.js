import baseAPIService from './base.api.service'

export default {
  login: async (url, body) =>
    baseAPIService.executeAnonymousMethod(url, 'POST', body),

  get: async (url, anonymous = false) => anonymous ?
    baseAPIService.executeAnonymousMethod(url, 'GET', undefined) :
    baseAPIService.executeTokenMethod(url, 'GET', undefined),

  getExternal: async (url) => baseAPIService.executeExternalMethod(url, 'GET'),

  post: async (url, body = undefined, anonymous = false) => anonymous ?
    baseAPIService.executeAnonymousMethod(url, 'POST', body ? body : undefined, undefined) :
    baseAPIService.executeTokenMethod(url, 'POST', body),

  put: async (url, body = undefined, anonymous = false) => anonymous ?
    baseAPIService.executeAnonymousMethod(url, 'PUT', body ? body : undefined, undefined) :
    baseAPIService.executeTokenMethod(url, 'PUT', body),

  delete: async (url, anonymous = false) => anonymous ?
    baseAPIService.executeAnonymousMethod(url, 'DELETE', undefined) :
    baseAPIService.executeTokenMethod(url, 'DELETE', undefined),
}
