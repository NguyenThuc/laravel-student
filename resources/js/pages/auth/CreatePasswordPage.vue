<template>
  <div class="container-fluid" id="create-password">
    <div class="page-title"> パスワード初期設定</div>
    <VeeForm
      @submit="submit"
      :validation-schema="validate"
      v-slot="{ errors }"
      v-if="!success"
    >
      <div class="form-group form-custom">
        <label class="col-form-label label-custom"> 新しいパスワード</label>
        <div class="pad-0">
          <error-message :message="errors.password"></error-message>
          <Field
            name="password"
            v-model="formCreatePassword.password"
            type="password"
            placeholder="Password"
            class="form-control"
          />
        </div>
      </div>

      <div class="form-group form-custom">
        <label class="col-form-label label-custom">
          新しいパスワード（もう一度入力下さい）
        </label>
        <div class="pad-0">
          <error-message :message="errors.confirm_password"></error-message>
          <Field
            name="confirm_password"
            v-model="formCreatePassword.confirm_password"
            type="password"
            placeholder="Password"
            class="form-control"
          />
        </div>
      </div>

      <div class="form-group  actions">
        <button class="btn btn-primary btn-submit d-block" type="submit">登録する</button>
      </div>
    </VeeForm>
  </div>
</template>

<script>
import { Form as VeeForm, Field } from 'vee-validate';
import * as yup from 'yup';
import { mapActions } from 'vuex';

export default {
  components: {
    VeeForm,
    Field,
  },
  data() {
    const validate = yup.object({
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
      validate,
      loading: false,
      success: false,
      formCreatePassword: {
        token: '',
        password: '',
        confirm_password: '',
      },
    };
  },
  mounted() {
    const token = new URLSearchParams(window.location.search).get('token');
    this.formCreatePassword.token = token;
  },
  methods: {
    ...mapActions('auth', ['createPassword']),

    async submit(value) {
      this.loading = true;

      const response = await this.createPassword({
        value,
        params: this.formCreatePassword,
      });
      this.loading = false;
      if (response.success) {
        window.location = '/active-success';
      }
    },
  },
};
</script>

<style scoped lang="scss">
.content {
  background-color: #ffffff;
}

#create-password {
  .form-custom {
    margin-bottom: 20px;
  }

  .page-title {
    font-size: 34px;
    line-height: 34px;
    margin-bottom: 80px;
  }

  .title {
    font-size: 18px;
    text-align: center;
    color: #162B44;
    margin-top: 32px;
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
    padding-bottom: 15px;
  }

  .form-group {
    .form-control {
      height: 60px;

      &::placeholder {
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
    }
  }

  .actions {
    .btn-submit {
      width: 100%;
      height: 60px;
      margin-top: 37px;
      font-size: 18px;
      background-color: #007EF2;
      border-radius: 5px;
    }
  }
}
</style>
