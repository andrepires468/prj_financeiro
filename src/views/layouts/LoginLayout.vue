<template>

  <router-view v-slot='{ Component }'>
    <div class="container-fluid">
      <component :is='Component'/>
    </div>
  </router-view>

</template>

<script>
export default {
  name: "HomeLayout"
}
</script>

<style scoped>

</style>
