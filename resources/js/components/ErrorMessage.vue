<template>
  <div v-if="message" class="error-message">
    <span>{{ message }} </span>
  </div>
</template>

<script>
export default {
  props: {
    message: String,
  },
};
</script>
<style lang="scss" scoped >
  @import '../../sass/variables';
  .error-message {
    font-family: $font-hiragino;
    float: right;
    text-align: right;
    margin-bottom: 5px;

    span {
      position: relative;
      color: #ffffff;
      background: red;
      padding: 10px;
      border-radius: 5px;
      font-size: 12px;
      display: inline-block;
      text-align: left;
      font-weight: 400;
      line-height: 16px;
    }

    span:after {
      content: ' ';
      position: absolute;
      top: 100%; /* At the bottom of the tooltip */
      left: 7%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: red transparent transparent transparent;
    }
  }

</style>
