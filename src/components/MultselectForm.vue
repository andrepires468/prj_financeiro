<template>

  <multiselect
    selectLabel="Selecionar"
    selectGroupLabel="Selecionar"
    tagPlaceholder="Criar"
    selectedLabel="Selecionado"
    deselectLabel="Remover"

    :options="options"
    :v-model="model"
    :label="label"
    :placeholder="placeholder"
    :internalSearch="internalSearch"

    :searchable="searchable"
    :loading="loading"
    :limit="limit"
    :disabled="disabled"
    @select="change"

  >
    <slot></slot>
    <template v-slot:noOptions>Nenhuma opção listada</template>
    <template v-slot:noResult>Nenhum resultado, consider alterar a pesquisa</template>
    <template v-slot:maxElements>Máximo de opções selecionadas</template>
  </multiselect>
</template>

<script>

import 'vue-multiselect/dist/vue-multiselect.css';
import Multiselect from 'vue-multiselect'

export default {
  name: "MultselectForm",
  components: {Multiselect},
  emits: [
    "change",
  ],
  props: {
    disabled: false,
    options: null,
    model: null,
    label: '',
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
  },
  methods:{
    change(item){
      this.$emit('change', item)
    }
  }
}
</script>

<style>



</style>
