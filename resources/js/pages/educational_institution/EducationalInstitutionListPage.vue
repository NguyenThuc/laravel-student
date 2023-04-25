<template>
  <div>
    <div class="row px-4">
      <div class="col-12 py-2 title-box">
        <span class="heading-bold float-start">教育機関一覧</span>
        <a href="/educational_institutions/create"
                class="btn btn-primary btn-lg rounded-pill float-end btn-search">
          新規登録
        </a>
      </div>
    </div>
    <div class="row px-4">
      <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
        <a-table
          :columns="columns"
          :row-key="record => record.id"
          :data-source="eduInstitutions"
          :pagination="pagination"
          :showSorterTooltip="false"
          :locale="locale"
          @change="handleTableChange"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'owner_name'">
              {{ record.owner_name }}
            </template>
            <template v-if="column.dataIndex === 'student_register'">
              0
            </template>
            <template
              v-if="column.dataIndex === 'contract_status' &&
              record.contracts[0] &&
              !isContractExpired(record.contracts[0].ended_at)"
            >
              契約中
            </template>
            <template
              v-if="column.dataIndex === 'contract_status' &&
              record.contracts[0] &&
              isContractExpired(record.contracts[0].ended_at)"
            >
              終了
            </template>
            <template v-if="column.key === 'action'">
              <a :href="`/educational_institutions/${record.id}/detail`">
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
  data() {
    return {
      columns: [
        {
          title: '教育機関ID',
          dataIndex: 'id',
          width: '16%',
        }, {
          title: '教育機関名',
          dataIndex: 'name',
          width: '27%',
        }, {
          title: 'オーナー',
          dataIndex: 'owner_name',
          width: '27%',
        }, {
          title: '契約ステータス',
          dataIndex: 'contract_status',
          width: '15%',
        }, {
          title: '登録生徒数',
          dataIndex: 'student_register',
          width: '15%',
          align: 'right',
        }, {
          key: 'action',
        },
      ],
    };
  },
  mounted() {
    this.getData();
  },
  computed: {
    ...mapState('educationalInstitution', ['eduInstitutions', 'pagination', 'params', 'locale']),
  },
  methods: {
    ...mapActions('educationalInstitution', ['getEduInstitutions', 'setParams']),
    handleTableChange(pagination, _, sort) {
      this.setParams({
        sort,
        currentPage: pagination.current ?? 1,
        search: this.params.search,
      });
      this.getData();
    },

    async getData() {
      await this.getEduInstitutions(this.params);
      if (document.getElementsByClassName('anticon-left')[0]) {
        document.getElementsByClassName('anticon-left')[0].innerHTML = '<span><i class="fa fa-chevron-left" aria-hidden="true">　前へ</i></span>';
      }
      if (document.getElementsByClassName('anticon-right')[0]) {
        document.getElementsByClassName('anticon-right')[0].innerHTML = '<span>次へ　<i class="fa fa-chevron-right" aria-hidden="true"></i></span>';
      }
    },

    isContractExpired(expiredDay) {
      const today = new Date().toISOString().slice(0, 10);
      const result = expiredDay.localeCompare(today);
      return result === -1;
    },
  },
};
</script>

<style lang="scss" scoped>
.btn-add, .btn-search {
  padding: 8px 30px;
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
}

.btn-primary {
  color: rgb(255, 255, 255);
  background-color: rgb(13, 110, 253);
  border-color: rgb(13, 110, 253);
}
</style>
