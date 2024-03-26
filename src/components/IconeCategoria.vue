<template>
  <div :style="`width: ${_width}; height: ${_height}`">
    <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
         v-if="_icon" :width="_width" :height="_height" viewBox="0 0 122.000000 122.000000"
         preserveAspectRatio="xMidYMid meet"
         role="img">
      <g transform="translate(0.000000,122.000000) scale(0.100000,-0.100000)"
         :fill="_color" stroke="none" v-html="_content">
      </g>
    </svg>
  </div>
</template>

<script>

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
      _icon: this.icon,
      _content: null,
      _color: this.color
    }
  },
  async mounted() {

    if (this._icon) {

      await fetch(`img/categorias/${this._icon}.svg`)
          .then(async response => response.text())
          .then(async text => {
            this._content = text;
          })

    }
  }
}
</script>

<style scoped>

</style>