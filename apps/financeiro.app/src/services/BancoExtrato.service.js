import endpoints from "../models/api/api.endpoints";
import api from "../helpers/api.service";
import Helper from "../helpers/helper.service";
import ServiceResponseCode from "../models/service.response.code";

export default {
  async getBancoExtrato() {
    try {
      const result = await api.get(endpoints.bancoExtrato)
      return Helper.getServiceResponse(result)
    } catch (e) {
      return {
        Code: ServiceResponseCode.GenericException,
        Title: 'Error',
        Message: e.message,
        Success: false,
      }
    }
  },
  async updateBancoExtrato(data) {
    try {
      const result = await api.put(endpoints.bancoExtrato, data)
      return Helper.getServiceResponse(result)
    } catch (e) {
      return {
        Code: ServiceResponseCode.GenericException,
        Title: 'Error',
        Message: e.message,
        Success: false,
      }
    }
  }
}
