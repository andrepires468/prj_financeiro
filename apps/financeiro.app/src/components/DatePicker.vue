<template>
  <VueDatePicker
    :model-value="model"
    @update:model-value="$emit('update:model', $event)"
    cancelText="Cancelar"
    selectText="Selecionar"
    format="dd/MM/yyyy"
    locale="pt-BR"
    auto-apply
    :hide-navigation="['time']"
    :min-date="minDate"
    :max-date="maxDate"
    prevent-min-max-navigation
    ignore-time-validation
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
  components: { VueDatePicker },
  emits: ['update:model'],
  props: {
    model: { default: null },
    minDate: { default: null },
    maxDate: { default: null },
  },
}
</script>

<style>
.dp__main {
  width: 100%;
}

.dp__input_wrap {
  width: 100%;
}

.dp__input {
  width: 100%;
  min-height: 38px;
  height: 38px;
  border-radius: 2px;
  border: 1px solid var(--bs-border-color) !important;
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  padding: 0.375rem 0.75rem 0.375rem 2.5rem !important;
  font-size: 1rem;
  line-height: 1.5;
  box-shadow: none;
}

.dp__input:hover {
  border-color: var(--bs-border-color) !important;
}

.dp__input:focus,
.dp__input_focus {
  border-color: var(--bs-primary) !important;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb, 13, 110, 253), 0.25);
}

.dp__today {
  border-color: var(--info-border, var(--bs-primary));
}

.dp__active_date {
  border-color: var(--info-border, var(--bs-primary));
  background-color: var(--info, var(--bs-primary));
  color: var(--white, var(--bs-primary-color));
}

.dp__overlay_cell_active {
  border-color: var(--info-border, var(--bs-primary));
  background-color: var(--info, var(--bs-primary));
}

</style>
