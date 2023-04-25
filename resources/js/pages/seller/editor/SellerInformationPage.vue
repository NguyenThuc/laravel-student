<template>
  <VeeForm @submit="save" :validation-schema="schema" v-slot="{ errors }" id="seller-information">
    <div class="form-group">
      <label class="col-form-label label-input-common">
        氏名
        <required-mark></required-mark>
      </label>
      <error-message :message="errors.name"></error-message>
      <Field name="name" :value="sellerData.name"
             type="text" placeholder="氏名" class="form-control"
             @input="updateSellerData({name: $event.target.value})"/>
    </div>

    <div class="form-group">
      <label class="col-form-label label-input-common">
        メールアドレス <required-mark></required-mark>
      </label>
      <error-message :message="errors.email"></error-message>
      <Field name="email" :value="sellerData.email"
             type="text" placeholder="mail@example.com" class="form-control"
             @input="updateSellerData({email: $event.target.value})"/>
    </div>

    <div class="form-group position-relative">
      <label class="col-form-label label-input-common">権限 <required-mark></required-mark></label>
      <error-message :message="errors.role"></error-message>
      <div class="position-relative">
        <Field name="role" as="select"
               :value="sellerData.role"
               id="select-role"
               class="form-control custom-select" ref="dropdown"
               @change="updateSellerData({role: $event.target.value})">
          <option v-for="role in roles"
                  :value="role.value"
                  :key="role.value" selected>{{role.text}}</option>
        </Field>
        <div class="icon">
          <drop-down></drop-down>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="col-form-label label-input-common">担当教育機関</label>
      <error-message :message="errors.educationalInsIds"></error-message>
      <Field name="educationalInsIds" v-model="sellerData.educationalInsIds"
             type="hidden"/>
      <div class="position-relative">
        <input class="form-control form-control-custom"
               placeholder="教育機関を選択する"
               data-toggle="modal"
               data-target="#selectEducationalIns" readonly>
        <div class="icon">
          <drop-down></drop-down>
        </div>
      </div>
    </div>

    <div class="list-class-info row">
      <div class="col-11 list-educational-ins-selected">
        <p v-for="eduIns in sellerEducationalIns" v-bind:key="eduIns.id">{{eduIns.name}}</p>
      </div>

      <div class="col-1 m-auto">
        <p class="add-info" data-toggle="modal" data-target="#selectEducationalIns">+</p>
      </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-common fw-700 btn-submit">確認</button>
  </VeeForm>
</template>

<script>
import {
  Form as VeeForm,
  Field,
} from 'vee-validate';
import * as yup from 'yup';
import { mapActions, mapState } from 'vuex';

export default {
  components: {
    VeeForm,
    Field,
  },

  props: {
    sellerEducationalIns: {
      type: Array,
      default() {
        return [];
      }
    }
  },

  computed: {
    ...mapState('seller', ['sellerData'])
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
      role: yup.string().trim()
        .required()
        .label('権限'),
      educationalInsIds: yup.array()
        .label('教育機関・教室'),
    });

    return {
      roles: [
        { value: 1, text: '管理者' },
        { value: 2, text: '一般' },
      ],
      schema,
      selectedRole: {},
    };
  },

  methods: {
    ...mapActions('seller', ['checkEmail', 'updateSellerData']),
    async save(value, actions) {
      const params = { email: this.sellerData.email };
      if (this.sellerData.id) {
        params.id = this.sellerData.id;
      }
      const response = await this.checkEmail({ params });
      if (!response.success) {
        actions.setFieldError('email', response.message);
      } else {
        this.selectedRole = this.roles.find((role) => role.value === Number(this.sellerData.role));
        this.updateSellerData({step: 'confirm', roleName: this.selectedRole.text});
      }
    },
  },
};
</script>

<style lang="scss" scoped>
  #seller-information {
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
    .list-educational-ins-selected {
      max-height: 150px;
      overflow: auto;
      font-size: 18px;
    }
    .icon {
      position: absolute;
      right: 2%;
      bottom: 32%;
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
