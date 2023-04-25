<template>
  <div class="dealer-confirm">
    <div class="form-group">
      <label class="col-form-label label-input-common">
        氏名
        <required-mark></required-mark>
      </label>
      <p class="data-confirm">{{profile.data.name}}</p>
    </div>

    <div class="form-group">
      <label class="col-form-label label-input-common">
        メールアドレス
        <required-mark></required-mark>
      </label>
      <p class="data-confirm">{{profile.data.email}}</p>
    </div>
    <div class="row btn-group-confirm">
      <button-cancel @click="updateProfileStep('information')"></button-cancel>
      <button-submit :loading="loading" @click="submit()"></button-submit>
    </div>
  </div>
</template>

<script>
import { mapActions, mapState } from 'vuex';

export default {
  data() {
    return {
      loading: false,
    };
  },
  computed: {
    ...mapState('seller', ['profile']),
  },
  methods: {
    ...mapActions('seller', ['updateProfile', 'updateProfileStep']),
    async submit() {
      this.loading = true;
      let response = '';
      response = await this.updateProfile(this.profile.data);
      this.loading = false;
      if (response.status === 'success') {
        this.updateProfileStep('done');
      }
    },
  },
};
</script>

<style lang="scss" scoped>
  .dealer-confirm {
    p {
      font-size: 18px;
      color: #162B44;;
    }
    .data-confirm {
      border-bottom: 1px solid gray;
      margin-top: 32px;
      padding-bottom: 17px;
      word-break: break-all;
    }

    .btn-group {
      width: 100%;
    }

    button {
      width: 49%;
      border: none;
      height: 60px;
      font-size: 18px;
      font-weight: 700;
    }

    .btn-close {
      background: #c3c6c9;
      color: #000000;
    }

    button:first-child {
      margin-right: 2%;
    }

    .btn-group-confirm {
      margin: 30px 0 0 0;
    }
    .list-class {
      max-height: 150px;
      overflow: auto;
      font-size: 18px;
    }

    ::-webkit-scrollbar {
      width: 10px;
    }

    /* Track */
    ::-webkit-scrollbar-track {
      background: #f1f1f1;
    }

    /* Handle */
    ::-webkit-scrollbar-thumb {
      background: #888;
    }

    /* Handle on hover */
    ::-webkit-scrollbar-thumb:hover {
      background: #555;
    }
  }
</style>
