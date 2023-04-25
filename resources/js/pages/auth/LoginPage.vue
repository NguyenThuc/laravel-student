<template>
  <div class="container-fluid">
    <div class="row login">
      <div class="col-sm-12 col-md-12 col-lg-3 col-xl-2"></div>
      <div class="col-sm-12 col-md-12 col-lg-6 col-xl-8">
        <div class="wrap">
          <div class="justify-content-center text-center">
            <span class="login-title">ログイン</span>
          </div>
          <div class="justify-content-center">
            <VeeForm
              @submit="onSubmit"
              :validation-schema="schema"
              v-slot="{ errors }"
            >
              <div v-if="errors.error_email_password " class="custom-error" >
                <span v-html="errors.error_email_password"> </span>
              </div>

              <div class="form-group mb-3 height-grid">
                <error-message :message="errors.email"></error-message>
                <label for="email" class="mb-2 label">メールアドレス</label>
                <Field
                  :validateOnChange="false"
                  type="email"
                  class="form-control input-text"
                  name="email"
                  placeholder="mail@example.com"
                />
              </div>
              <div class="form-group mb-3 height-grid">
                <error-message :message="errors.password"></error-message>
                <label for="password" class="mb-2 label">パスワード</label>
                <Field
                  :validateOnChange="false"
                  type="password"
                  id="password"
                  class="form-control input-text"
                  name="password"
                />
              </div>
              <div class="height-grid mx-auto">
                <button class="btn btn-primary btn-block label">
                  ログイン
                </button>
              </div>
            </VeeForm>
          </div>
          <div class="row justify-content-center text-center div-forget">
            <a href="/forgot-password" class="link-forget"
            >パスワードを忘れた方</a
            >
          </div>
        </div>
      </div>
      <div class="col-sm-12 col-md-12 col-lg-3 col-xl-2"></div>
    </div>
  </div>
</template>

<script>
import {
  Field, Form as VeeForm,
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
      email: yup.string()
        .required('メールアドレスは、必ず指定してください。'),
      password: yup.string()
        .required('パスワードは、必ず指定してください。'),
    });
    return {
      schema,
      loading: false,
    };
  },

  methods: {
    ...mapActions('auth', ['login', 'setSeller']),

    // eslint-disable-next-line consistent-return
    async onSubmit(formData, { setErrors }) {
      this.loading = true;
      const response = await this.login(formData);
      if (response && response.success !== false) {
        let seller = response.data.data;
        this.setSeller(seller);
        // login success
        window.location.replace('/');
      } else {
        // login false show error
        setErrors({
          error_email_password: response.message,
        });
        this.loading = false;
        return false;
      }
    },
  },
};
</script>
<style scoped type="scss">
.custom-error {
  background: red;
  margin-bottom: 28px;
  top: 145px;
  color: white;
  padding: 5px 30px;
  font-size: 15px;
}
</style>
