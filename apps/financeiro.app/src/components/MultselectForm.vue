<template>

  <multiselect
    selectLabel="Selecionar"
    selectGroupLabel="Selecionar"
    tagPlaceholder="Criar"
    selectedLabel="Selecionado"
    deselectLabel="Remover"

    :options="options"
    :modelValue="modelValue"
    :label="label"
    :placeholder="placeholder"
    :internalSearch="internalSearch"

    :searchable="searchable"
    :loading="loading"
    :limit="limit"
    :disabled="disabled"
    @select="onSelect"
    @update:modelValue="$emit('update:modelValue', $event)"

  >
    <template v-if="showCategoryIcon" #option="{ option }">
      <div class="multiselect__option multiselect__option--category">
        <IconeCategoria
          v-if="option.IconTag"
          :icon="option.IconTag"
          :color="option.IconColor || '#666'"
          :width="20"
          :height="20"
          class="me-2 float-left"
        />
        <span :style="option.IconColor ? { color: option.IconColor } : null">{{ getOptionLabel(option) }}</span>
      </div>
    </template>
    <template v-if="showCategoryIcon" #singleLabel="{ option }">
      <div class="multiselect__single-label multiselect__single-label--category d-flex align-items-center">
        <IconeCategoria
          v-if="option && option.IconTag"
          :icon="option.IconTag"
          :color="option.IconColor || '#666'"
          :width="18"
          :height="18"
          class="me-2"
        />
        <span :style="option && option.IconColor ? { color: option.IconColor } : null">{{ option ? getOptionLabel(option) : '' }}</span>
      </div>
    </template>
    <slot></slot>
    <template v-slot:noOptions>Nenhuma opção listada</template>
    <template v-slot:noResult>Nenhum resultado, consider alterar a pesquisa</template>
    <template v-slot:maxElements>Máximo de opções selecionadas</template>
  </multiselect>
</template>

<script>

import 'vue-multiselect/dist/vue-multiselect.css';
import Multiselect from 'vue-multiselect'
import IconeCategoria from '@/components/IconeCategoria.vue'

export default {
  name: "MultselectForm",
  components: { Multiselect, IconeCategoria },
  emits: [
    "change",
    "update:modelValue",
  ],
  props: {
    disabled: false,
    options: null,
    modelValue: null,
    label: {
      type: String,
      default: '',
    },
    placeholder: '',
    internalSearch: {
      type: Boolean,
      value: true
    },
    searchable: {
      type: Boolean,
      value: false
    },
    loading: {
      type: Boolean,
      value: false
    },
    limit: null,
    /** Quando true, exibe ícone da categoria (opções devem ter IconTag e IconColor, ex.: CategoriaLista) */
    showCategoryIcon: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    getOptionLabel(option) {
      if (!option || typeof option !== 'object') return option
      return option[this.label] ?? option.CategoriaNome ?? ''
    },
    onSelect(item) {
      this.$emit('update:modelValue', item)
      this.$emit('change', item)
    }
  }
}
</script>

<style>



.btn-group .multiselect,
.btn-group .multiselect * {
  border-radius: 0px !important;
}


.btn-group .multiselect__tags{
  border-radius: 0px !important;
  border-right: none;
  border-left: none;
  border-top: 1px solid !important;
  border-bottom: 1px solid !important;
  border-color: var(--bs-btn-border-color) !important;
}


</style>
