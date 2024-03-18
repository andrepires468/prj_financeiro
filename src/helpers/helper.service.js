import ServiceResponseCode from '../models/service.response.code'
import APIError from '../models/api/api.error'
import moment from "moment";

export default {
  getMessageError(result) {
    return `${result.Message}|${result.Type ?? 'error'}`;

  },
  messageExceptionHandler(exception) {
    const message = exception.message.split('|');
    if (message[1])
      switch (message[1]) {
        case 'error':
          swalAlert.fire('Erro', message[0], 'error');
          break;
        case 'warning':
          swalAlert.fire('Atenção', message[0], 'warning');
          break;
      }
    else
      swalAlert.fire('', exception.message, '');

  },
  getServiceResponse(response) {

    if (!response) {
      return {
        Code: ServiceResponseCode.ResponseNotFound,
        Message: 'Not Found',
        Success: false,
        Type: response.Type
      }
    }

    if (response.Success) {
      return {
        Code: response.Code,
        Message: response.Message,
        Success: true,
        Data: response.Data,
        Type: 'success'
      }
    } else {
      return {
        Code: response.Code,
        Message: response.Message,
        Success: false,
        Data: response.Data,
        Type: response.Type
      }
    }

  },
  async getHttpErrror(response) {

    if (response.status == 200 || response.status == 201 || response.status == 202 || response.status == 206) {
      return null
    }

    const text = await response.text()

    switch (response.status) {
      case 406:
        return {
          Message: `Not authorized. Session as expired, please Login`,
          Type: 'TOKEN',
          Error: 'Session as expired'
        }
      case 401:
        if (response.type == 'cors') {
          return {
            Message: `CORS error`,
            Type: 'CORS',
            Error: text
          }
        } else {
          return {
            Message: `Not authorized`,
            Type: 'UNAUTHORIZED',
            Error: text
          }
        }
      default:
        return {
          Message: `error status: ${response.Status}`,
          Type: 'UNKNOWN',
          Error: JSON.parse(text)?.errors ?? ''
        }

    }
  },
  getHttpErrorCode(error) {
    if (!error || !(error instanceof APIError)) {
      return ServiceResponseCode.GenericException
    } else {
      return error?.data?.Code ?? ServiceResponseCode.GenericException
    }
  },
  getURLParams(params = []) {
    if (!params) {
      return ''
    }

    params = params.filter(p => p.Name && p.Value)

    if (params.length == 0) {
      return ''
    }

    let queryString = '?'

    params.forEach(param => {
      queryString += (queryString == '?' ? '' : '&') + param.Name + '=' + encodeURI(param.Value)
    })

    return queryString
  },
  // ucWords(s) {
  //   let str = s.toLowerCase()
  //   let re = /(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g
  //   return str.replace(re, s => s.toUpperCase())
  // },
  getTokenParam(token, param) {

    let base64Url = token.split('.')[1]
    let base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/')
    let jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function (c) {
      return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
    }).join(''))

    const data = JSON.parse(jsonPayload)

    return data[param] ?? null

  },
  formatMoney(value) {
    const formCurrency = new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2,
      roundingIncrement: 5
    })
    return formCurrency.format(value)
  },
  formatDecimal(value) {
    const formCurrency = new Intl.NumberFormat("en-US", {
      style: "decimal",
      minimumFractionDigits: 2
    });
    return formCurrency.format(value)
  },
  formatPercentage(value) {
    return value.toString().replaceAll('.', ',') + '%'
  },
  formatDateTime(dateTime) {
    if (!dateTime) return null
    return moment(dateTime, "YYYY-MM-DDTHH:mm:ss.SSS").format("DD/MM/YYYY HH:mm:ss");
  },
  formatDate(dateTime) {
    if (!dateTime) return null
    return moment(dateTime, "YYYY-MM-DDTHH:mm:ss.SSS").format("DD/MM/YYYY");
  },
  formatDateTimeUs(dateTime) {
    if (!dateTime) return null
    return moment(dateTime, "YYYY-MM-DDTHH:mm:ss.SSS").format("YYYY-MM-DD HH:mm:ss");
  },
  formatDatePickerToUs(dateTime) {
    if (!dateTime) return null
    return moment(dateTime, "MM/DD/YYYY").format("YYYY-MM-DD");
  },
  formatDatePicker(dateTime) {
    if (!dateTime) return null
    return moment(dateTime, "YYYY-MM-DDTHH:mm:ss.SSS").format("MM/DD/YYYY");
  },
  isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
  },
  isResponsive() {
    return window.innerWidth <= 992
  },
  // ukWords(str) {
  //   let val = ''
  //   if (str != null) {
  //     val = str.match(/[a-zA-Z][a-z]+/g)
  //     val = val.join(' ')
  //     val = val.toLowerCase().replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g, function (letter) {
  //       return letter.toUpperCase();
  //     });
  //   }
  //   return val
  // },
  stringToUrl(str) {
    return str.replaceAll(' ', '-').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
  },
  getEmbedLinkYoutube(link) {
    let linkSplit = link.split('v=')
    return `https://www.youtube.com/embed/${linkSplit[1]}`;
  },
  formatIdDisciplina(id) {
    return ('000000' + id).slice(-6);
  },
  addDaysDate(date, days) {
    date.setDate(date.getDate() + days);
    return date;
  },
  removeDaysDate(date, days) {
    date.setDate(date.getDate() - days);
    return date;
  },
  readFile(file) {

    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = (event) => {
        resolve({
          url: event.target.result,
          file: file,
        });
      };
      reader.onerror = (error) => {
        reject(error);
      };
      reader.readAsDataURL(file);
    });

  },

}
