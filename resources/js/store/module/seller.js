import SellerApi from '../api/sellerApi';

export const state = {
  columns: [
    {
      title: '氏名',
      dataIndex: 'name',
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
      title: '',
      dataIndex: 'id',
    },
  ],
  sellers: [],
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
  schools: {},
  loading: false,
  profile: {
    step: 'information',
    id: '',
    data: {
      email: '',
      name: '',
    }
  },
  sellerData: {
    agency_id: null,
    step: 'information',
    name: '',
    email: '',
    role: 2,
    educationalInsIds: [],
    roleName: '管理者',
  }
};

export const mutations = {
  setSellers(stateData, { data }) {
    const temp = stateData;
    temp.sellers = data.data;
    temp.pagination.total = data.total;
    temp.pagination.pageSize = data.per_page;
    this.setLoading(state, false);
  },

  setParams(stateData, data) {
    const temp = stateData;
    temp.params.sortField = data.sort.sortField ?? '';
    temp.params.sortOrder = data.sort.sortOrder ?? '';
    temp.params.page = data.currentPage;
    temp.params.search = data.search;
  },

  updateSearchField(stateData, data) {
    const temp = stateData;
    temp.params.search[data.field] = data.event.target.value;
  },

  setSchool(stateData, { data }) {
    const temp = stateData;
    temp.schools = data;
  },

  setLoading(stateData, status) {
    const temp = stateData;
    temp.loading = status;
  },

  updateField(stateData, data) {
    const temp = stateData;
    temp.profile.data[data.field] = data.event.target.value;
  },

  updateProfileStep(stateData, data) {
    const temp = stateData;
    temp.profile.step = data;
  },

  updateDataProfile(stateData, data) {
    const temp = stateData;
    temp.profile.data.name = data.name;
    temp.profile.data.email = data.email;
    temp.profile.id = data.id;
  },

  updateSellerData(stateData, data) {
    const temp = stateData.sellerData;
    Object.keys(data).forEach((key) => { (temp[key] = data[key]); });
  }

};

export const getters = {};

export const actions = {

  async checkEmail(_, payload) {
    try {
      const response = await SellerApi.checkEmail(payload);
      const { data } = response;
      return {
        success: true,
        data,
      };
    } catch (error) {
      const errors = error.response.data;
      return {
        success: false,
        message: errors.message ?? null,
      };
    }
  },

  async getSellers(_, payload) {
    try {
      this.commit('seller/setLoading', true);
      const { data } = await SellerApi.getSellers(payload);
      this.commit('seller/setSellers', data);
    } catch (error) {
      this.commit('seller/setLoading', false);
    }
  },

  setParams(_, payload) {
    this.commit('seller/setParams', payload);
  },

  updateSearchField(_, payload) {
    this.commit('seller/updateSearchField', payload);
  },

  async createData(_, payload) {
    try {
      const response = await SellerApi.createData(payload);
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

  async getEducationalInstitutionsByAgency(_, payload) {
    try {
      const response = await SellerApi.getEducationalInstitutionsByAgency(payload);
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

  async updateData(_, payload) {
    try {
      const response = await SellerApi.updateData(payload);
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

  async deleteSeller(_, payload) {
    try {
      const response = await SellerApi.deleteSeller(payload);
      const { data } = response;
      return data;
    } catch (error) {
      const { errors } = error.response.data;
      return {
        success: false,
        message: errors ?? null,
      };
    }
  },

  updateField(_, payload) {
    this.commit('seller/updateField', payload);
  },

  updateProfileStep(_, payload) {
    this.commit('seller/updateProfileStep', payload);
  },

  async updateProfile(_, payload) {
    try {
      const response = await SellerApi.updateProfile(payload);
      const { data } = response;
      return data;
    } catch (error) {
      const { errors } = error.response.data;
      return {
        success: false,
        message: errors ?? null,
      };
    }
  },

  updateDataProfile(_, payload) {
    this.commit('seller/updateDataProfile', payload);
  },

  updateSellerData(_, payload) {
    this.commit('seller/updateSellerData', payload);
  }
};
