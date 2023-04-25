<template>
  <div>
    <div class="create-done">
      <h1 class="heading" v-if="!data.id">教育機関を登録しました</h1>
      <h1 class="heading" v-else>教育機関を編集しました。</h1>
    </div>
    <div class="form-group btn-block text-center">
      <a href="/educational_institutions/" class="btn btn-light btn-back">一覧画面へ戻る</a>
      <a href="/educational_institutions/create" class="btn btn-primary ml-4" v-if="!data.id">続けて他の教育機関を登録</a>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex';
export default ({
  computed: {
    ...mapState('educationalInstitution',['data']),
  },
});
</script>

<style lang="scss" scoped>
  .create-done {
    text-align: center;
    padding: 20px;

    .heading {
      font-weight: 400;
      margin-bottom: 24px;
    }

    p {
      margin-bottom: 8px;
      font-size: 18px;
    }
  }

  a {
    width: 45%;
    height: 48px;
    border: none;
    font-weight: 600;
    border-radius: 5px;
    padding-top: 10px;
  }
</style>
