import educationStaff from '../api/educationStaffApi';

export const state = {
  columns: [
    {
      title: '氏名',
      dataIndex: 'staff_name',
      width: '20%',
    },
    {
      title: '権限',
      dataIndex: 'role',
      width: '20%',
    },
    {
      title: '登録日時',
      dataIndex: 'created_at',
    },
    {
      title: 'スクール',
      dataIndex: 'school_name',
    },
    {
      title: '教室',
      dataIndex: 'class_name',
    },
    {
      title: '',
      dataIndex: 'id',
    },
  ],
  educationStaffs: [],
  listClassRoom: [],
  schools: [],
  selectedSchool: '',
  pagination: {
    hideOnSinglePage: true,
    position: ['bottomLeft'],
  },
  params: {
    page: 1,
    search: {},
  },
  locale: {
    emptyText: '登録情報はありません',
  },
  loading: false,
  changeFinished: false
};

export const mutations = {
  setEducationStaffs(state, {data}) {
    state.educationStaffs = data.data;
    state.pagination.total = data.total;
    state.pagination.pageSize = data.per_page;
    this.setLoading(state, true);
  },
  setEducationInstitutions(state,data) {
    state.schools = data;
  },
  setParams(state, data) {
    state.params.sortField = data.sort.sortField ?? '';
    state.params.sortOrder = data.sort.sortOrder ?? '';
    state.params.page = data.currentPage;
    state.params.search = data.search;
  },

  updateSearchField(state, data) {
    state.params.search[data.field] = data.event.target.value;
  },
  setClassroomsbyEducationInstitutions(state, data) {
    state.listClassRoom = data;
  },
  setLoading(state, status) {
    state.loading = status;
  },

  updateChangeFinished(stateData, step) {
    const temp = stateData;
    temp.changeFinished = step;
  },
};

export const getters = {};

export const actions = {

  async getListEducationStaff(_, payload) {
    try {
      this.commit('educationStaff/setLoading', true);
      const { data } = await educationStaff.getListEducationStaff(payload);
      this.commit('educationStaff/setEducationStaffs', data);
    } catch (error) {
      this.commit('educationStaff/setLoading', false);
    }
  },
  setParams(_, payload) {
    this.commit('educationStaff/setParams', payload);
  },

  async updateSearchField(_, payload) {
    try {
      this.commit('educationStaff/updateSearchField', payload);
    } catch (error) {
      this.commit('educationStaff/setLoading', false);
    }
  },

  updateField(_, payload) {
    this.commit('educationStaff/updateField', payload);
  },

  async getListSchool(_, payload) {
    try {
      this.commit('educationStaff/setLoading', true);
      const { data } = await educationStaff.getSchools(payload);
      this.commit('educationStaff/setEducationInstitutions', data);
    } catch (error) {
      this.commit('educationStaff/setLoading', false);
    }
  },
  async getListClassRoom(_, payload) {
    try {
      const { data } = await educationStaff.getListClassRoom(payload);
      this.commit('educationStaff/setClassroomsbyEducationInstitutions', data);
    } catch (error) {
      this.commit('educationStaff/setLoading', false);
    }
  },

  async changeOwnerRole(_, payload) {
    try {
      const response = await educationStaff.changeOwnerRole(payload);
      const { data } = response;
      return {
        success: true,
        data,
      };
    } catch (error) {
      const { errors } = error.response.data;
      return {
        success: false,
        message: errors ?? null,
      };
    }
  },

  updateChangeFinished(_, payload) {
    this.commit('educationStaff/updateChangeFinished', payload);
  },
};
