<template>
<div>
  <div class="container-fluid" id="forgot-password" v-if="!success">
    <div class="page-title">パスワード再設定</div>
    <p class="title">メールアドレスを入力してください</p>

    <VeeForm
      @submit="submit"
      :validation-schema="validate"
      v-slot="{ errors }"
    >
      <div class="form-group">
        <label class="col-form-label label-custom">メールアドレス</label>

        <div class="pad-0">
          <error-message :message="errors.email"></error-message>
          <Field
            type="email"
            name="email"
            v-model="form.email"
            class="form-control"
            placeholder="mail@example.com"
          />
        </div>
      </div>

      <div class="actions">
        <button class="btn btn-primary btn-submit d-block" type="submit">送信</button>
      </div>
    </VeeForm>
  </div>

  <div class="container-fluid" id="forgot-password-success" v-if="success">
    <div class="page-title weight-normal">送信が完了しました</div>
    <div class="content">
      <p>{{ form.email }}へ登録メールを送りました</p>
      <p>入力されたメールアドレスが誤っていた場合や、連絡用メールアドレスへの事前登録が確認できなかった場合は送信されません</p>
    </div>
  </div>
</div>
</template>

<script>
import { Form as VeeForm, Field } from 'vee-validate';
import { mapActions } from 'vuex';
import * as yup from 'yup';

export default {
  components: {
    VeeForm,
    Field,
  },
  data() {
    const validate = yup.object({
      email: yup.string()
        .email('メールアドレスは、有効なメールアドレス形式で指定してください。')
        .required('メールアドレスは、必ず指定してください。'),
    });

    return {
      validate,
      success: false,
      loading: false,
      form: {
        email: '',
      },
    };
  },
  methods: {
    ...mapActions('auth', ['forgotPassword', 'checkExistEmail']),

    async submit(value, actions) {
      this.loading = true;

      const response = await this.forgotPassword(value);

      if (response.type === 'error') {
        return Object.keys(this.form).forEach((key) => {
          if (response.message && response.message[key]) {
            actions.setFieldError(key, response.message[key]);
          }
        });
      }

      this.success = true;
      this.loading = false;
      return true;
    },
  },
};
</script>

<style scoped lang="scss">
  .page-title {
    font-size: 34px;
    line-height: 34px;
  }

  .weight-normal {
    font-weight: normal;
  }

  #forgot-password {
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

  #forgot-password-success {
    .content {
      max-width: 600px;
      margin: 32px auto 0;

      p {
        margin-bottom: 0;
        font-size: 18px;
      }
    }
  }
</style>
