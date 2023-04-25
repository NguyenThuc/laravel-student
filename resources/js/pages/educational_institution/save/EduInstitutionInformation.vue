<template>
   <VeeForm @submit="save" :validation-schema="schema" v-slot="{ errors }" id="dealer-information">
    <div class="form-group">
      <label class="col-form-label label-custom">
        教育機関名
        <required-mark></required-mark>
      </label>
      <error-message :message="errors.name"></error-message>
      <Field
        type="text"
        class="form-control"
        name="name"
        placeholder="○○教育機関"
        :value="data.name"
        @input="updateField({field: 'name', event: $event})"
      />
    </div>

    <div class="form-group">
      <label class="col-form-label label-custom">
        電話番号
        <required-mark></required-mark>
      </label>
      <error-message :message="errors.phoneNumber"></error-message>
      <Field
        type="text"
        class="form-control"
        name="phoneNumber"
        placeholder="09011112222(半角数字、ハイフンなし)"
        :value="data.phoneNumber"
        @input="updateField({field: 'phoneNumber', event: $event})"
      />
    </div>

    <div class="form-group">
      <label class="col-form-label label-custom">
        契約開始日
        <required-mark></required-mark>
      </label>
      <error-message :message="errors.startDate"></error-message>
      <Field
        name="startDate"
        type="hidden"
        v-model="startDate"
      />
      <a-space direction="vertical" :size="12">
        <!-- eslint-disable -->
        <a-date-picker
          :value="data.startDate"
          @change="updateField({field: 'startDate', event: $event}); syncStartDate($event)"
          :format="dateFormat"
          placeholder="yyyy/mm/dd"/>
        <!-- eslint-enable -->
      </a-space>
    </div>

    <div class="form-group">
      <label class="col-form-label label-custom">
        契約終了日
        <required-mark></required-mark>
      </label>
      <error-message :message="errors.endDate"></error-message>
      <Field
        name="endDate"
        type="hidden"
        v-model="endDate"
      />
      <a-space direction="vertical" :size="12">
        <!-- eslint-disable -->
        <a-date-picker
          :value="data.endDate"
          @change="updateField({field: 'endDate', event: $event}); syncEndDate($event)"
          :format="dateFormat"
          placeholder="yyyy/mm/dd"/>
        <!-- eslint-enable -->
      </a-space>
    </div>

    <div class="form-group" v-if="!data.id">
      <label class="col-form-label label-custom">
        オーナー氏名
        <required-mark></required-mark>
      </label>
      <error-message :message="errors.teacherName"></error-message>
      <Field
        type="text"
        name="teacherName"
        class="form-control"
        placeholder="氏名"
        :value="data.teacherName"
        @input="updateField({field: 'teacherName', event:$event})"
      />
    </div>

    <div class="form-group" v-if="!data.id">
      <label class="col-form-label label-custom">
        オーナーメールアドレス
        <required-mark></required-mark>
      </label>
      <error-message :message="errors.teacherEmail"></error-message>
      <Field
        type="text"
        name="teacherEmail"
        class="form-control"
        placeholder="mail@example.com"
        :value="data.teacherEmail"
        @input="updateField({field: 'teacherEmail', event:$event})"
      />
    </div>

    <div class="form-group">
      <label class="col-form-label label-custom">
        対象教材（複数選択可）
        <required-mark></required-mark>
      </label>
      <error-message :message="errors.mstTextbookCourses"></error-message>
      <Field
        name="mstTextbookCourses"
        type="hidden"
        :value="data.mstTextbookCourseIds"
      />
      <div v-for='category in categories'
           :key='category.id'>
        <label class="fw-normal">{{ category.name }}</label>
        <div class="container p-0">
          <div class="row">
            <div
              class="col-6 category-checkbox"
              v-for='mst_textbook_course in category.mst_textbook_courses'
              :key='mst_textbook_course.id'>
              <label>
                <input
                  type="checkbox"
                  class="form-control"
                  :id="mst_textbook_course.id"
                  :value="mst_textbook_course.id"
                  :checked="data.mstTextbookCourseIds.includes(mst_textbook_course.id)"
                  @change="updateField({field: 'mstTextbookCourseIds', event:$event})"
                >
                <div :title="showTitle(mst_textbook_course)">
                  <i class="fa fa-check" aria-hidden="true"></i>
                  <p>
                    {{mst_textbook_course.name == null ? mst_textbook_course.name_en : mst_textbook_course.name}}
                  </p>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="col-form-label label-input-common">
        担当販売店スタッフ
        <required-mark></required-mark>
      </label>
      <error-message :message="errors.sellers"></error-message>
      <Field
        name="sellers"
        type="hidden"
        :value="data.selectedSellerIds"
      />
      <div class="position-relative">
        <input
          class="form-control form-control-custom"
          placeholder="担当販売店スタッフを選択する"
          data-toggle="collapse"
          data-target="#selected-seller"
          readonly
        >
        <div class="icon">
          <drop-down></drop-down>
        </div>
      </div>
      <div class="row">
        <div class="collapse multi-collapse col-lg-7" id="selected-seller">
          <div class="card card-body">
            <div class="panel panel-default">
              <div class="row">
                <div class="search-wrapper panel-heading col-sm-12">
                  <input
                    class="form-control"
                    name="sellers" type="text"
                    v-model="searchQuery"
                    placeholder="絞り込み"
                  />
                </div>
              </div>
                <div class="mt-2" v-for="item in resultQuery" :key="item.id">
                  <div class="form-check mt-2">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :id="item.id"
                      :value="item.id"
                      v-model="item.checked"
                      :a="item.id"
                      :checked="data.selectedSellerIds.includes(item.id)"
                      @change="updateField({field: 'selectedSellerIds', event:$event})"
                    >
                    <span class="form-check-label">
                        {{ item.name }}
                      </span>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-lg btn-primary w-100">確認</button>
  </VeeForm>
</template>

<script>
import {
  Form as VeeForm,
  Field,
} from 'vee-validate';
import { mapActions, mapState } from 'vuex';
import * as yup from 'yup';
import filters from '../../../filter';

yup.addMethod(yup.array, 'arrRequired', function(message) {
  return this.test("required", function (value) {
    const { createError } = this;
    if (value.length == 0) {
      return createError({ message: message});
    }
    return true;
  });
});

yup.addMethod(yup.string, 'after', function() {
   return this.test("after", function (value, data) {
    const { createError } = this;
    if (value && (filters.formatDate(value) <= filters.formatDate(data.parent.startDate))) {
      return createError({ message: '契約終了日には、契約開始日より後の日付を指定してください。' });
    }
    return true;
  });
});

export default {
  components: {
    VeeForm,
    Field,
  },
  data() {
    let schema = yup.object({
      name: yup.string().trim()
        .max(255, '教育機関名は、255文字以下にしてください。')
        .required('教育機関名は、必ず指定してください。'),
      phoneNumber: yup.string()
      .trim()
        .length(11, '電話番号は、有効な電話番号形式で指定してください。')
        .required('電話番号は、必ず指定してください。')
        .matches(/^[0-9]*$/,'電話番号は、有効な電話番号形式で指定してください。'),
      startDate: yup.string().trim()
        .required('契約開始日は、必ず指定してください。'),
      endDate: yup.string().trim()
        .required('契約終了日は、必ず指定してください。').after(),
      sellers: yup.array()
      .required().arrRequired('担当販売店スタッフは、必ず指定してください。' ),
      mstTextbookCourses: yup.array().required().arrRequired('対象教材は、必ず指定してください。'),
    }).when((_, schema) => {
      if (!this.data.id) {
        return schema.shape({
          teacherEmail: yup.string().trim()
            .matches(/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/, 'オーナーメールアドレスは、有効なメールアドレス形式で指定してください。')
            .required('オーナーメールアドレスは、必ず指定してください。'),
          teacherName: yup.string().trim()
            .max(255, 'オーナー氏名は、255文字以下にしてください。')
            .required('オーナー氏名は、必ず指定してください。'),
        });
      }
    });

    return {
      schema,
      searchQuery: null,
      startDate: '',
      endDate: '',
      selected: '',
    };
  },
  props: {
    sellers: {
      type: Object,
    },
    categories: {
      type: Object,
    },
  },
  methods: {
    ...mapActions('educationalInstitution', ['checkTeacherEmail', 'updateField', 'updateStep']),
    async save(value, actions) {
      const param = { params: { email: this.data.teacherEmail, staffId: this.data.staffId ?? null } };
      const response = await this.checkTeacherEmail(param);
      if (response.status === 'error') {
        actions.setFieldError('teacherEmail', response.message);
      } else {
        this.updateStep('confirm');
      }
    },
    syncStartDate(event) {
      this.startDate = filters.formatDate(event.$d);
    },
    syncEndDate(event) {
      this.endDate = filters.formatDate(event.$d);
    },
    showTitle(courses) {
      let name = courses.name === null ? courses.name_en : courses.name;
      return name.length > 50 ? name : '';
    }
  },
  created() {
    if(this.data.endDate)
      this.syncEndDate(this.data.endDate);
    if(this.data.startDate)
      this.syncStartDate(this.data.startDate);
  },
  computed: {
    ...mapState('educationalInstitution',['step', 'data', 'dateFormat']),
    resultQuery() {
      if (this.searchQuery) {
        const { length } = this.searchQuery;
        return this.sellers.filter((item) => {
          const subTitle = item.name.substring(0, length)
            .toLowerCase();
          return this.searchQuery.localeCompare(subTitle) === 0;
        });
      }
      return this.sellers;
    },
  },
};
</script>

<style lang="scss" scoped>
.radio-input {
  vertical-align: middle;
  margin-right: 30px;
  width: 30px;
  height: 30px;
}

.span-radio {
  margin-right: 30px;
  margin-bottom: 5px;
}

.category-checkbox {
  border-radius: 4px;
  overflow: auto;
  float: left;
  margin-bottom: 10px;
  overflow-y: hidden;
  label {
    float: left;
    width: 100%;
    margin-bottom: 0;
    font-weight: 400;
    div {
      padding: 0px 20px 0 20px;
      display: flex;
      align-items: center;
      height: 75px;
      border-radius: 0.25rem;
      background-color: #ECECEC;

      p {
        word-break: break-all;
        margin-bottom: 0px;
        display: block; /* Fallback for non-webkit */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
      }
    }
    input {
      position: absolute;
      top: -200px;
    }
    i {
      margin-right: 10px;
    }
  }
  input {
    &:checked {
      + {
        div {
          background-color: #007EF2;
          color: #fff;
        }
      }
    }
  }
}

#dealer-information {
  .list-class-info {
    background: #ffffff;
    margin: 0 0 16px 0;
    padding-top: 20px;
  }

  .list-class-info:hover {
    cursor: pointer;
  }

  .add-info {
    font-size: 24px;
    text-align: right;
    font-weight: 600;
  }

  .add-info:hover {
    cursor: pointer;
  }

  .form-control[readonly] {
    background-color: #ffffff;
  }

  .list-school-selected {
    max-height: 100px;
    overflow: auto;
  }

  .icon {
    position: absolute;
    right: 2%;
    bottom: 42%;
    z-index: 1
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
}
.fw-normal {
  font-weight: normal;
  margin-top: 0.5rem;
}
.ant-picker {
  height: 60px;
  font-size: 20px;
  width: 100%;
  font-weight: 400;
  border-radius: 0.25rem;
}
.ant-space {
  display: block;
}

.search-wrapper {
  padding: 0px;
}
#selected-seller{
  padding: 5px 5px 0 0;
  margin-left: 15px;
  background: #FFFFFF;
}
</style>
