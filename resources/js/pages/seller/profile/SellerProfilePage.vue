<template>
  <div class="container-fluid" id="create-dealer">
    <h1 class="title-common">登録情報編集</h1>
    <div class="dealer-step">
      <nav-step :step="profile.step"></nav-step>
    </div>
    <div class="dealer-content">
      <profile-information-page v-if="profile.step==='information'"> 
      </profile-information-page>
      <profile-confirm-page v-if="profile.step==='confirm'">
      </profile-confirm-page>
      <profile-done-page v-if="profile.step==='done'" >
      </profile-done-page>
    </div>
  </div>
</template>

<script>

import { mapActions, mapState } from 'vuex';

export default {
  props: {
    seller: {
      type: Object,
    },
  },
  computed: {
    ...mapState('seller', ['profile']),
  },
  methods: {
    ...mapActions('seller', ['updateDataProfile'])
  },
  created() {
    this.updateDataProfile(this.seller);
  }
};
</script>

<style lang="scss" scoped>
</style>
