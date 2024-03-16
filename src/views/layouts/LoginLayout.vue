<template>
  <div class="main-wrapper">
    <router-view v-slot='{ Component }'>
      <component :is='Component'/>
    </router-view>
  </div>
</template>

<script>
export default {
  name: "HomeLayout"
}
</script>

<style scoped>

</style>
