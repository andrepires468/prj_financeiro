import endpoints from "../models/api/api.endpoints";
import api from "../helpers/api.service";
import Helper from "../helpers/helper.service";
import ServiceResponseCode from "../models/service.response.code";
import userService from '../helpers/user.service'

export default {
  async login(data) {
    try {
      const url = `${endpoints.authLogin}`;
      const result = await api.login(url, data)
      if (result && result.Success) {
        userService.saveUser(result.Data)
      }
      return Helper.getServiceResponse(result)
    } catch (e) {
      return {
        Code: ServiceResponseCode.GenericException,
        Message: e.Message,
        Success: false,
      }
    }
  },
  async refreshToken() {
    try {
      const url = endpoints.authRefreshToken
      const result = await api.get(url)
      if (result && result.Success) {
        userService.saveUser(result.Data)
      } else {
        console.error('não foi possível realizar o refresh token')
      }
    } catch (e) {
      return {
        Code: ServiceResponseCode.GenericException,
        Message: e.message,
        Success: false,
      }
    }

  },
  async Logout() {
    try {
      const url = endpoints.authLogout
      const result = await api.post(url);
      userService.destroyUser()
      return Helper.getServiceResponse(result)
    } catch (e) {
      return {
        Code: ServiceResponseCode.GenericException,
        Message: e.message,
        Success: false,
      }
    }
  },

}
