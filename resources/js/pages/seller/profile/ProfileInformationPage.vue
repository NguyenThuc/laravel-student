<template>
  <VeeForm @submit="save" :validation-schema="schema" v-slot="{ errors }" id="dealer-information">
    <div class="form-group">
      <label class="col-form-label label-input-common">
        氏名
        <required-mark></required-mark>
      </label>
      <error-message :message="errors.name"></error-message>
      <Field name="name"
      :value="profile.data.name"
      @input="updateField({field: 'name', event: $event})"
      type="text" placeholder="氏名" class="form-control" />
    </div>

    <div class="form-group">
      <label class="col-form-label label-input-common">
        メールアドレス <required-mark></required-mark>
      </label>
      <error-message :message="errors.email"></error-message>
      <Field name="email"
      :value="profile.data.email"
      @input="updateField({field: 'email', event: $event})"
      type="text" placeholder="mail@example.com" class="form-control" />
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-common fw-700 btn-submit">確認</button>
  </VeeForm>
</template>

<script>
import {
  Form as VeeForm,
  Field,
} from 'vee-validate';
import { mapActions, mapState } from 'vuex';
import * as yup from 'yup';

export default {
  components: {
    VeeForm,
    Field,
  },
  data() {
    const schema = yup.object({
      name: yup.string().trim()
        .max(255, '氏名は、255文字以下にしてください。')
        .required('氏名は、必ず指定してください。')
        .label('氏名'),
      email: yup.string().trim()
        .matches(
          /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/,
          'メールアドレスは、有効なメールアドレス形式で指定してください。',
        )
        .max(255)
        .required('メールアドレスは、必ず指定してください。')
        .label('メールアドレス'),
    });
    return {
      schema,
    };
  },
  computed: {
    ...mapState('seller', ['profile']),
  },
  methods: {
    ...mapActions('seller', ['checkEmail', 'updateField', 'updateProfileStep']),
    async save(_, actions) {
      const params = { email: this.profile.data.email, id: this.profile.id };
      const response = await this.checkEmail({ params });
      if (!response.success) {
        actions.setFieldError('email', response.message);
      } else {
        this.updateProfileStep('confirm');
      }
    },
  },
};
</script>

<style lang="scss" scoped>
  #dealer-information {
    .list-class-info {
      background: #ffffff;
      margin: 0 0 16px 0 ;
      padding-top: 20px;
    }
    .list-class-info:hover {
      cursor: pointer;
    }
    .add-info {
      font-size: 30px;
      text-align: center;
      font-weight: 600;
    }
    .add-info:hover {
      cursor: pointer;
    }
    .form-control[readonly] {
      background-color: #ffffff;
    }
    .list-school-selected {
      max-height: 150px;
      overflow: auto;
      font-size: 18px;
    }
    .icon {
      position: absolute;
      right: 2%;
      bottom: 42%;
      pointer-events: none;
    }
    .form-control-custom::placeholder {
      color: inherit;
    }
    .custom-select {
      height: 60px;
      background: #ffffff;
      option {
        font-size: 18px;
        background: #ECECEC;
      }
    }
    .custom-select:focus > option:checked {
      background: #C4C4C4 !important;
    }
    .form-group {
      margin-bottom: 32px;
    }
    .btn-submit {
      margin-top: 30px;
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
