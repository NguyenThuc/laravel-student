<template>
  <div>
    <left-outlined />
    <div class="row px-4">
      <div class="col-12 py-2 title-box">
        <span class="heading-bold float-start">販売店スタッフ一覧</span>
          <a v-if="utils.checkAdmin(auth.role)" href="/sellers/create"
          class="btn btn-primary btn-lg rounded-pill float-end btn-search">
          新規登録
          </a>
      </div>
    </div>
    <div class="row px-4">
      <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <div class="card sreach-box" style="display: none">
          <div class="row">
            <div class="col-lg-6 search-input">
              <label for="">氏名</label>
              <input type="text" class="form-control"
              :value="params.search.name"
              @input="updateSearchField({field: 'name', event: $event})"
              name="params.search.name" placeholder="キーワード検索">
            </div>
            <div class="col-lg-6 search-input">
              <label for="">権限</label>
              <select name="params.search.role"
              :value="params.search.role"
              @change="updateSearchField({field: 'role', event: $event})"
              class="form-control">
                <option value="">選択</option>
                <option value="1">管理者</option>
                <option value="2">一般</option>
              </select>
            </div>
          </div>
          <hr />
          <div class="col-12 py-2 box-btn-search">
            <button @click="handleTableChange(1, null, {})"
              class="btn btn-primary btn-lg rounded-pill float-end btn-search">
              <i class="fa fa-search" aria-hidden="true"></i>
              検索
            </button>
          </div>
        </div>

        <a-table
        :columns="columns"
        :row-key="record => record.id"
        :data-source="sellers"
        :pagination="pagination"
        :loading="loading"
        :showSorterTooltip="false"
        :locale="locale"
        @change="handleTableChange">
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'role' && record.role == 1">管理者</template>
            <template v-if="column.dataIndex === 'role' && record.role == 2">一般</template>
            <template v-if="column.dataIndex === 'created_at'">
            {{ filters.formatDateTime(record.created_at) }}
            </template>
            <template v-if="column.dataIndex === 'id'">
              <a :href="`/sellers/${record.id}/detail`">
                <i class="fa fa-chevron-right float-end arrow-pointer" aria-hidden="true"></i>
              </a>
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
  mounted() {
    this.getData();
  },
  computed: {
    ...mapState('seller', ['columns', 'sellers', 'pagination', 'params', 'locale', 'loading']),
  },
  methods: {
    ...mapActions('seller', ['getSellers', 'setParams', 'updateSearchField']),
    handleTableChange(pagination, _, sort) {
      this.setParams({
        sort,
        currentPage: pagination.current ?? 1,
        search: this.params.search,
      });
      this.getData();
    },
    async getData() {
      await this.getSellers(this.params);
      if (document.getElementsByClassName('anticon-right')[0]) {
        document.getElementsByClassName('anticon-right')[0].innerHTML = '<span>次へ <i class="fa fa-chevron-right" aria-hidden="true"></i></span>';
      }
      if (document.getElementsByClassName('anticon-left')[0]) {
        document.getElementsByClassName('anticon-left')[0].innerHTML = '<span><i class="fa fa-chevron-left" aria-hidden="true"></i> 前へ</span>';
      }
    },
  },
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
  background: #fff!important;
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
