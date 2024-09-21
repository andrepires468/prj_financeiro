import endpoints from "../models/api/api.endpoints";
import api from "../helpers/api.service";
import Helper from "../helpers/helper.service";
import ServiceResponseCode from "../models/service.response.code";

export default {
  async calendarioExtrato() {
    try {
      const url = `${endpoints.financeiro}/calendario-extrato`;
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
  async listaExtrato(page = null, pageSize = null, mes = null, ano = null) {
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
        {
          Name: 'mes',
          Value: mes
        },
        {
          Name: 'ano',
          Value: ano
        },
      ]
      const url = `${endpoints.financeiro}/lista-extrato${Helper.getURLParams(params)}`;
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
  async updateExtrato(data) {
    try {
      const url = `${endpoints.financeiro}/update-extrato`;
      const result = await api.put(url, data)
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
  async chartUltimosMeses() {
    try {
      const url = `${endpoints.financeiro}/chart-ultimos-meses`;
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
}
