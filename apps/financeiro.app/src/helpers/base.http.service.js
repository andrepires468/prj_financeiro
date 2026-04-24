import helperService from './helper.service'
import APIError from '../models/api/api.error'

const BASE_URL = import.meta.env.VITE_API_BASE_URL
const API_VERSION = import.meta.env.VITE_API_VERSION

export default {
  executeAPI: async (url, method, body, headers) => {
    url = BASE_URL + API_VERSION + url
    return fetch(url, {
      method: method,
      headers: headers,
      timeut: 60,
      body: body ? JSON.stringify(body) : undefined,
    }).then(response => {
      return helperService.getHttpErrror(response).then(responseError => {
        if (responseError) {
          // Se a API respondeu com o envelope padrão ({ HttpCode, Success, Message, ... }),
          // retornamos ele diretamente para que a camada superior trate (ex.: 401 -> logout)
          if (responseError && typeof responseError === 'object' && 'Success' in responseError) {
            return responseError
          }
          throw new APIError(responseError.Message, responseError)
        } else {
          return response.json()
        }
      })
    }).then(responseJson => {
      return responseJson
    }).catch(error => {
      console.log(error);
      return error
    })
  },
  executeExternal: async (url, method, headers) => {
    return fetch(url, {
      method: method,
      headers: headers,
      timeut: 60,
    }).then(response => {
      return helperService.getHttpErrror(response).then(responseError => {
        if (responseError) {
          throw new APIError(responseError.Message, responseError)
        } else {
          return response.json()
        }
      })
    }).then(responseJson => {
      return responseJson
    }).catch(error => {
      throw error
    })
  },
}
