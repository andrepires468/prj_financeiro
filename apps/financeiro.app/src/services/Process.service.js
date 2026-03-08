import endpoints from "../models/api/api.endpoints";
import api from "../helpers/api.service";
import Helper from "../helpers/helper.service";
import ServiceResponseCode from "../models/service.response.code";

export default {
  async updateExtrato(data) {
    try {
      const url = `${endpoints.process}/import-extrato`;
      const result = await api.post(url, data)
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
  async categorizarExtrato(data) {
    try {
      const url = `${endpoints.process}/categorizar-extrato`;
      const result = await api.post(url, data)
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
}
