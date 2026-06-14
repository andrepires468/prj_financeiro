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
  async listaExtrato(page = null, pageSize = null, mes = null, ano = null, excluidos = false) {
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
        {
          Name: 'excluidos',
          Value: excluidos ? '1' : null
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
  async createExtratoManual(data) {
    try {
      const url = `${endpoints.financeiro}/create-extrato-manual`;
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
  async excluirExtrato(data) {
    try {
      const url = `${endpoints.financeiro}/excluir-extrato`;
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
  async restaurarExtrato(data) {
    try {
      const url = `${endpoints.financeiro}/restaurar-extrato`;
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
  async ChartCategoriaUltimosMeses(idCategoria, qtdMeses) {

    const params = [
      {
        Name: 'idCategoria',
        Value: idCategoria
      },
      {
        Name: 'qtdMeses',
        Value: qtdMeses
      }
    ]

    try {
      const url = `${endpoints.financeiro}/chart-categoria-ultimos-meses${Helper.getURLParams(params)}`;
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
  async ChartCategoriasMes(dataBase) {

    const params = [
      {
        Name: 'dataBase',
        Value: dataBase
      },
    ]

    try {
      const url = `${endpoints.financeiro}/chart-categorias-mes${Helper.getURLParams(params)}`;
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
  async chartUltimosMeses(qtdMeses) {

    const params = [
      {
        Name: 'qtdMeses',
        Value: qtdMeses
      }
    ]

    try {
      const url = `${endpoints.financeiro}/chart-ultimos-meses${Helper.getURLParams(params)}`;
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
