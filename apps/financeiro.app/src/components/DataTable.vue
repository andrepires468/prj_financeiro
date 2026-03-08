<template>
  <div class="table-container">
    <table class="table table-hover table-striped">
      <thead>
      <tr>
        <th></th>
        <th v-for="(itemHeader, th_index) in columns_" :key="th_index" :style="itemHeader?._style">
          {{ itemHeader.headerName }}
        </th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(itemRow, tr_index) in rows" :key="tr_index">
        <td style="padding: 0px; width: 10px">
          <div class="btn-group btn-sm" role="group">
            <button type="button" class="btn btn-outline-primary btn-sm" @click="editItem(itemRow)">Editar</button>
          </div>
        </td>
        <td v-for="(itemData, td_index) in itemRow" :key="td_index">
          <div class="cell-text">
            {{ itemData }}
          </div>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import Helper from '../helpers/helper.service'

export default {
  name: "DataTable",
  props: {
    columns: {},
    rows: {},
    disabled: {
      type: Boolean,
      default: false
    }
  },
  emits: [
    "editItem",
  ],
  setup() {
    return {}
  },
  data() {
    return {
      isMobile: false,
      columns_: this.columns.map(i => {
        return {
          ...i,
          headerName: (i?.headerName != '')? i.headerName : Helper.ukWords(i.field)
        }
      })
    }
  },
  created() {
    this.isMobile = Helper.isMobile()
  },
  methods: {
    editItem(item) {
      this.$emit('editItem', item)
    },
  },
}
</script>

<style scoped>

</style>
