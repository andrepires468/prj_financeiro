<template>
  <div :style="`width: ${_width}; height: ${_height}`">
    <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
         v-if="icon" :width="_width" :height="_height" viewBox="0 0 122.000000 122.000000"
         preserveAspectRatio="xMidYMid meet"
         role="img">
      <g transform="translate(0.000000,122.000000) scale(0.100000,-0.100000)"
         :fill="color" stroke="none" v-html="content">
      </g>
    </svg>
  </div>
</template>

<script>

import categoriaIconsModel from '../models/categoria.icons.model'

export default {
  name: 'IconeCategoria',
  props: {
    icon: null,
    color: null,
    width: 15,
    height: 15,
  },
  data() {
    return {
      _width: `${this.width}px`,
      _height: `${this.height}px`,
      content: null,
    }
  },
  async mounted() {
    this.setIcone()
  },
  methods: {
    setIcone(){
      if (this.icon)
        this.content = categoriaIconsModel.find( i => i.IconTag == this.icon).IconSVG
    }
  },
  watch:{
    icon(){
      this.setIcone()
    }
  }
}
</script>

<style scoped>

</style>