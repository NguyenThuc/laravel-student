<template>
  <div class="create-done">
    <p>登録情報を更新しました。</p>
    <a href="/my-page" class="btn-return">戻る</a>
  </div>
</template>

<script>
export default {

};
</script>

<style lang="scss" scoped>
  .create-done {
    text-align: center;
    padding: 50px;

    .heading {
      font-weight: 400;
      font-size: 34px;
      margin-bottom: 32px;
      color: #162B44;
    }

    p {
      margin-bottom: 8px;
      font-size: 34px;
      font-weight: 400;
    }

    .btn-return {
      height: 60px;
      background: #ECECEC;
      color: #000000;
      padding: 19.2px 97.75px 22.8px 96.05px;
      margin-top: 24px;
      display: inline-block;
      text-decoration: none;
      border-radius: 6px;
      font-size: 18px;
      font-weight: 700;
    }
    .content-done p {
      font-size: 18px;
      background: inherit;
    }
  }
</style>
