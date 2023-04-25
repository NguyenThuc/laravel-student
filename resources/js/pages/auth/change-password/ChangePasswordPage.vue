<template>
  <div>
    <h1 class="auth-title-common" v-if="!success">パスワード変更</h1>
    <div id="auth-layout" v-if="!success">
      <VeeForm
        @submit="save"
        :validation-schema="schema"
        v-slot="{ errors }"
      >
        <div class="form-group">
          <label class="col-form-label label-custom">現在のパスワード</label>
          <div class="pad-0">
            <error-message :message="errors.current_password"></error-message>
            <Field
              name="current_password"
              v-model="data.current_password"
              type="password"
              placeholder="Password"
              class="form-control"
            />
          </div>
        </div>

        <div class="form-group">
          <label class="col-form-label label-custom">新しいパスワード</label>
          <div class="pad-0">
            <error-message :message="errors.password"></error-message>
            <Field
              name="password"
              v-model="data.password"
              type="password"
              placeholder="Password"
              class="form-control"
            />
          </div>
        </div>

        <div class="form-group">
          <label class="col-form-label label-custom ">
            新しいパスワード（もう一度入力下さい）
          </label>
          <div class="pad-0">
            <error-message :message="errors.confirm_password"></error-message>
            <Field
              name="confirm_password"
              v-model="data.confirm_password"
              type="password"
              placeholder="Password"
              class="form-control"
            />
          </div>
        </div>

        <div class="form-group group-action">
          <button-cancel @click="back()"></button-cancel>
          <button-submit :loading="loading"></button-submit>
        </div>
      </VeeForm>
    </div>
    <change-password-done v-if="success && isValid"></change-password-done>
  </div>
</template>

<script>
import {
  Form as VeeForm,
  Field,
} from 'vee-validate';
import * as yup from 'yup';
import { mapActions } from 'vuex';

export default {
  components: {
    VeeForm,
    Field,
  },
  data() {
    const schema = yup.object({
      current_password: yup.string()
        .required('現在のパスワードは、必ず指定してください。')
        .trim()
        .label('現在のパスワード'),
      password: yup.string()
        .trim()
        .matches(
          /^(?=.*?[a-zA-Z])(?=.*?[0-9])[a-zA-Z0-9_#!%^&*-]{8,32}$|^(?=.*?)(?=.*?[a-zA-Z0-9])(?=.*?[_#!%^&*-])[a-zA-Z0-9_#!%^&*-]{8,32}$/,
          'パスワードはアルファベット(半角英大文字、半角英小文字)、数字、記号記号!#%^&*-_の内、2種類以上の複合で作成してください',
        )
        .min(8, 'パスワードは、8文字以上にしてください。')
        .max(32, 'パスワードは、32文字以下にしてください。')
        .required('新しいパスワードは、必ず指定してください。')
        .label('新しいパスワード'),
      confirm_password: yup.string()
        .trim()
        .oneOf([yup.ref('password'), null], '新しいパスワードの入力と一致しません')
        .required('新しいパスワードの入力と一致しません')
        .label('パスワード（確認用）'),
    });
    return {
      schema,
      loading: false,
      success: false,
      isValid: true,
      data: {
        current_password: '',
        password: '',
        confirm_password: '',
      },
    };
  },
  mounted() {
    document.getElementsByClassName('main-content')[0].classList.add('change-password-page');
  },
  methods: {
    ...mapActions('auth', ['updatePassword']),
    async save(value, actions) {
      this.loading = true;
      const response = await this.updatePassword(value);
      if (!response) return;
      this.loading = false;
      if (response.success) {
        // TODO need refactoring
        this.success = true;
        this.isValid = true;
      } else {
        this.isValid = false;
        Object.keys(this.data).forEach((key) => {
          if (response.message[key]) {
            actions.setFieldError(key, response.message[key]);
          }
        });
      }
    },
    back() {
      window.location.href = '/my-page';
    },
  },
};
</script>
<style scoped>
  .content {
    background-color: #ffffff;
  }
  .label-custom {
    font-family: 'Hiragino Kaku Gothic ProN';
    font-style: normal;
    font-weight: 600;
    font-size: 18px;
    line-height: 18px;
    display: flex;
    align-items: center;
    color: #162b44;
  }
  .form-group input {
    height: 60px;
  }
  .form-group input::placeholder {
    position: absolute;
    height: 18px;
    left: 16px;
    top: calc(50% - 18px / 2 - 1px);
    font-family: 'Hiragino Kaku Gothic ProN';
    font-style: normal;
    font-weight: 300;
    font-size: 18px;
    line-height: 100%;
    display: flex;
    align-items: center;
    color: #a7a7a7;
  }
  .group-action {
    display: flex;
    justify-content: space-between;
  }
  .btn-back {
    background: #ececec;
    border-radius: 5px;
    height: 60px !important;
    width: 47.1% !important;
    font-family: 'Hiragino Kaku Gothic ProN';
    font-style: normal;
    font-weight: 700;
    font-size: 18px;
    line-height: 18px;
    color: #162b44;
    padding: 0px;
  }
  .btn-submit {
    background: #007ef2;
    border-radius: 5px;
    font-family: 'Hiragino Kaku Gothic ProN';
    font-style: normal;
    font-weight: 700;
    font-size: 18px;
    line-height: 18px;
    color: #ffffff;
    height: 60px !important;
    width: 47.1% !important;
    padding: 0px;
  }
</style>
