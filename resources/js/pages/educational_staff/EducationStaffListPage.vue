<template>
  <div>
    <div class="row px-4">
      <div class="col-12 py-2 title-box">
        <span class="heading-bold float-start">先生一覧</span>
      </div>
    </div>
    <div class="row px-4">
      <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <div class="card sreach-box">
          <div class="row">

            <div class="col-lg-4 search-input">
              <label for="">スクール</label>
              <select v-model="id_institution" name="params.search.school"
                      :value="params.search.school"
                      @change="updateSearchField({field: 'school', event: $event})"
                      class="form-control form-search">
                <option value="">選択</option>
                <option
                  v-for="school in this.schools" :key="school.id"
                  :value="school.id"
                >{{ school.name }}
                </option>
              </select>
            </div>

            <div class="col-lg-4 search-input">
              <label for="">教室</label>
              <select :disabled="this.id_institution.length === 0" name="params.search.role"
                      :value="params.search.class"
                      @change="updateSearchField({field: 'class', event: $event})"
                      class="form-control form-search">
                <option value="">選択</option>
                <option
                  v-for="class_room in this.listClassRoom" :key="class_room.id"
                  :value="class_room.id"
                >{{ class_room.name }}
                </option>
              </select>
            </div>
            <div class="col-lg-4 search-input">
              <label for="">権限</label>
              <select name="params.search.role"
                      :value="params.search.role"
                      @change="updateSearchField({field: 'role', event: $event})"
                      class="form-control form-search">
                <option  value="0">選択</option>
                <option
                  v-for="(role,key) in this.roles" :key="key"
                  :value="key"
                >{{ role }}
                </option>

              </select>
            </div>
            <hr/>
            <div class="col-12 py-2 box-btn-search">
              <button @click="handleTableChange(1, null, {})"
                      class="btn btn-primary btn-lg rounded-pill float-end btn-search">
                <i class="fa fa-search" aria-hidden="true"></i>
                検索
              </button>
            </div>
          </div>
        </div>
        <a-table
          :columns="columns"
          :row-key="record => record.id"
          :data-source="educationStaffs"
          :pagination="pagination"
          :loading="loading"
          :showSorterTooltip="false"
          :locale="locale"
          @change="handleTableChange">
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'staff_name' ">
              {{ record.name }}
            </template>
            <template v-if="column.dataIndex === 'role' && record.role===1  ">
              オーナー
            </template>
            <template v-if="column.dataIndex === 'role' && record.role===2 ">
              スタッフ
            </template>
            <template v-if="column.dataIndex === 'role' && record.role===3  ">
              一般教職員
            </template>
            <template v-if="column.dataIndex === 'role' && record.role===4  ">
              販売パートナー
            </template>
            <template v-if="column.dataIndex === 'created_at'">
              {{ filters.formatDateTime(record.created_at) }}
            </template>
            <template v-if="column.dataIndex === 'id'">
              <a :href="`/educational_staffs/${record.id}`">
                <i class="fa fa-chevron-right float-end arrow-pointer" aria-hidden="true"></i>
              </a>
            </template>
            <template v-if="column.dataIndex === 'school_name' ">{{ record.educational_institution.name }}</template>
            <template v-if="column.dataIndex === 'class_name'">
              <li v-for="item in record.classrooms" :key="item.id">
                {{ item.name }}
              </li>
            </template>
          </template>
        </a-table>
      </div>
    </div>
  </div>
</template>

<script>

import { mapActions, mapState } from 'vuex';

export default {
  data() {
    return {
      id_institution: '',
    };
  },
  props: {
    roles: {}
  },
  mounted() {
    this.getData();
    this.getListSchool();

  },
  computed: {
    ...mapState('educationStaff', ['columns', 'educationStaffs', 'pagination', 'params', 'locale', 'schools', 'loading', 'listClassRoom']),
  },
  methods: {
    ...mapActions('educationStaff', ['getListEducationStaff', 'setParams', 'updateSearchField', 'getListSchool', 'getListClassRoom']),
    handleTableChange(pagination, _, sort) {
      this.setParams({
        sort,
        currentPage: pagination.current ?? 1,
        search: this.params.search,
      });
      this.getData();
    },
    async getData() {
      await this.getListEducationStaff(this.params);
      if (document.getElementsByClassName('anticon-right')[0]) {
        document.getElementsByClassName('anticon-right')[0].innerHTML = '<span>次へ <i class="fa fa-chevron-right" aria-hidden="true"></i></span>';
      }
      if (document.getElementsByClassName('anticon-left')[0]) {
        document.getElementsByClassName('anticon-left')[0].innerHTML = '<span><i class="fa fa-chevron-left" aria-hidden="true"> 前へ</i></span>';
      }
    },

  },
  watch: {
    async id_institution() {
      if (this.id_institution >0 ) {
        const payload = { 'id_institution': this.id_institution };
        this.getListClassRoom(payload);
      } else {
      }
    },
  }
};
</script>

<style lang="scss" scoped>

.btn-add, .btn-search {
  padding: 4px 39px;
}

.btn-add:hover {
  color: #fff;
}

:global(.content) {
  background: #fff !important;
}

.form-search {
  height: 45px;
}

.sreach-box .search-input {
  padding: 36px;
}

.heading-bold {
  font-size: 34px;
  font-weight: 700;
  font-family: 'Hiragino Kaku Gothic ProN';
  font-style: normal;
  font-weight: 600;
  font-size: 34px;
  line-height: 34px;
  color: #162B44;
}

.btn-primary {
  color: rgb(255, 255, 255);
  background-color: rgb(13, 110, 253);
  border-color: rgb(13, 110, 253);
}
</style>
