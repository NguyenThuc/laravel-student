<template>
  <div class="dealer-confirm">
    <div class="form-group">
      <label class="col-form-label label-custom">
        教育機関名 <required-mark></required-mark>
      </label>
      <p class="data-confirm">{{data.name}}</p>
    </div>

    <div class="form-group">
      <label class="col-form-label label-custom">
        電話番号 <required-mark></required-mark>
      </label>
      <p class="data-confirm">{{data.phoneNumber}}</p>
    </div>

    <div class="form-group">
      <label class="col-form-label label-custom">
        契約開始日 <required-mark></required-mark>
      </label>
      <p class="data-confirm">{{filters.formatDate(data.startDate.$d)}}</p>

    </div>
    <div class="form-group">
      <label class="col-form-label label-custom">
        契約終了日 <required-mark></required-mark>
      </label>
      <p class="data-confirm">{{filters.formatDate(data.endDate.$d)}}</p>
    </div>

    <div class="form-group" v-if="!data.id">
      <label class="col-form-label label-custom">
        オーナー氏名 <required-mark></required-mark>
      </label>
      <p class="data-confirm">{{data.teacherName}}</p>
    </div>

    <div class="form-group" v-if="!data.id">
      <label class="col-form-label label-custom">
        オーナーメールアドレス <required-mark></required-mark>
      </label>
      <p class="data-confirm">{{data.teacherEmail}}</p>
    </div>

    <div class="form-group">
      <label class="col-form-label label-custom">対象教材(複数選択可)<required-mark></required-mark></label>
      <div class="container pl-1" v-for='category in categories' :key='category.id'>
        <div class="row" v-if="category.mst_textbook_courses.length > 0 && category.show">
          <div class="col-3">{{ category.name }}</div>
          <div class="col-9">
            <div v-for='course in category.mst_textbook_courses' :key='course.id'>
              <template v-if="data.mstTextbookCourseIds.includes(course.id)"> {{ course.name }} </template>
            </div>
          </div>
        </div>
      </div>
      <p class="data-confirm mt-0"></p>
    </div>

    <div class="form-group">
      <label class="col-form-label label-custom">担当販売店スタッフ<required-mark></required-mark></label>
      <div class="container pl-1" v-for='seller in sellers' :key='seller.id'>

        <template v-if="data.selectedSellerIds.includes(seller.id)"> {{ seller.name }} </template>
      </div>
      <p class="data-confirm mt-0"></p>
    </div>

    <div class="row btn-group-confirm">
      <button-cancel @click="updateStep('information')"></button-cancel>
      <button-submit :loading="loading" @click="submit()" :text="this.data.id ? '変更する': '登録する'"></button-submit>
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
  props: {
    sellers: {
      type: Object,
    },
    categories: {
      type: Object,
    },
  },
  created() {
    this.categories.forEach(category => {
      category.show = false;
      category.mst_textbook_courses.forEach(mst => {
        if(this.data.mstTextbookCourseIds.includes(mst.id)) {
          category.show = true;
          return;
        }
      });
    });
  },
  computed: {
    ...mapState('educationalInstitution',['step', 'data']),
  },
  methods: {
    ...mapActions('educationalInstitution', ['create', 'update', 'updateStep']),
    async submit() {
      this.loading = true;
      let response = '';
      if(this.data.id) {
        response = await this.update(this.data);
      } else {
        response = await this.create(this.data);
      }
      this.loading = false;
      if (response.status === 'success') {
        this.updateStep('done');
      }
    },
  },
};
</script>
<style lang="scss" scoped>
.dealer-confirm {
  .data-confirm {
    border-bottom: 1px solid gray;
    margin-top: 15px;
    padding-left: 15px;
    word-break: break-all;
  }

  .btn-group {
    width: 100%;
  }

  button {
    width: 49%;
    height: 48px;
    border: none;
    font-weight: 600;
  }

  .btn-close {
    background: #c3c6c9;
    color: #000000;
  }

  button:first-child {
    margin-right: 2%;
  }

  .btn-group-confirm {
    margin: 20px 0 0 0;
  }
}
</style>
