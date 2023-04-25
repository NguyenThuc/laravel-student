<template>
  <button type="submit"
          class="btn-submit"
          :disabled="loading">
    {{textButton}}
    <span v-if="loading"
          class="spinner-border spinner-border-sm"
          role="status"
          aria-hidden="true">
            </span>
  </button>
</template>

<script>
export default {
  props: {
    loading: {
      type: Boolean,
      default: false,
    },
    text: {
      type: String,
      default: '変更する'
    }
  }
  ,
  mounted() {
    this.textButton = this.text;
  },

  data() {
    return {
      textButton: ''
    };
  }

};
</script>

<style lang="scss" scoped>
  .btn-submit {
    background-color: #3870DC;
    color: #ffffff;
    font-weight: 700;
    height: 60px;
    font-size: 18px;
    span {
      vertical-align: inherit;
    }
  }
</style>
