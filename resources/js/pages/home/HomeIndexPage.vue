<template>

    <p>{{ title }}</p>

</template>

<script>
// import Header from '../../layouts/Header'
// import Footer from '../../layouts/Footer'
export default {
  props: {
    title: String,
    items: Array,
  },
  // components : {
  //   'Header': Header,
  //   'Footer': Footer
  // }
};
</script>

<style lang="scss" scoped>
// TODO: add style here
</style>
