import endpoints from "../models/api/api.endpoints";
import api from "../helpers/api.service";
import Helper from "../helpers/helper.service";
import ServiceResponseCode from "../models/service.response.code";

export default {
  async listFinanceiro(page = null, pageSize = null) {
    try {
      const params = [
        {
          Name: 'page',
          Value: page
        },
        {
          Name: 'pageSize',
          Value: pageSize
        },
      ]
      const url = `${endpoints.financeiro}/list${Helper.getURLParams(params)}`;
      const result = await api.get(url)
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
  async getFinanceiro(id) {
    try {
      const url = `${endpoints.financeiro}/${id}`;
      const result = await api.get(url)
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
  async registerFinanceiro(data, method) {
    try {
      const result = (method == 'criar') ?
        await api.post(endpoints.financeiro, data) :
        await api.put(endpoints.financeiro, data)
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
