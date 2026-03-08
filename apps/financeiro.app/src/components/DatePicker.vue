<template>
  <VueDatePicker
    :v-model="model"
    cancelText="Cancelar"
    selectText="Selecionar"
    format="dd/MM/yyyy"
    locale="pt-BR"
    auto-apply
    :hide-navigation="['time']"
  >
<!--    <template #dp-input="{ value, onInput, onEnter, onTab, onClear, onBlur, onKeypress, onPaste, isMenuOpen }">-->
<!--      <div class="input-group">-->
<!--        <input type="text" class="form-control" :value="value">-->
<!--        <span class="feather-calendar"></span>-->
<!--      </div>-->
<!--    </template>-->
  </VueDatePicker>
</template>

<script>

import '@vuepic/vue-datepicker/dist/main.css'
import VueDatePicker from '@vuepic/vue-datepicker';

export default {
  name: "DatePicker",
  components: {VueDatePicker},
  props: {
    model: null
  },
}
</script>

<style>
.dp__input_wrap {
  height: 42px;
}

.dp__input {
  height: 42px;
  border-radius: 4px;
  border: 1px solid var(--gray1);
  padding: 6px 30px 6px 40px !important;
}

.dp__input:hover {
  border: 1px solid var(--gray1);
}

.dp__today {
  border-color: var(--info-border);
}

.dp__active_date {
  border-color: var(--info-border);
  background-color: var(--info);
  color: var(--white);
}

.dp__overlay_cell_active {
  border-color: var(--info-border);
  background-color: var(--info);
}

</style>
