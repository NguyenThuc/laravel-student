import educationalInsitutionApi from '../api/educationalInstitutionApi';
import dayjs from 'dayjs';
import { ref } from 'vue';

export const state = {
  eduInstitutions: [],
  pagination: {
    hideOnSinglePage: true,
    position: ['bottomLeft'],
  },
  params: {
    page: 1,
  },
  locale: {
    emptyText: '登録情報はありません',
  },
  step: 'information',
  dateFormat: 'YYYY/MM/DD',
  data: {
    name: '',
    teacherName: '',
    teacherEmail: '',
    phoneNumber: '',
    startDate: '',
    endDate: '',
    selectedSellerIds: [],
    mstTextbookCourseIds: []
  },
};

export const mutations = {
  setEduInstitutions(stateData, { data }) {
    const temp = stateData;
    temp.eduInstitutions = data.data;
    temp.pagination.total = data.pagination.total;
    temp.pagination.pageSize = data.pagination.pageSize;
  },

  setParams(stateData, data) {
    const temp = stateData;
    temp.params.page = data.currentPage;
  },
  updateField(stateData, data) {
    const temp = stateData;
    if(Array.isArray(temp.data[data.field])) {
      const index = temp.data[data.field].indexOf(Number(data.event.target.value));
      if(index !== -1) {
        temp.data[data.field].splice(index, 1);
      } else {
        temp.data[data.field].push(Number(data.event.target.value));
      }
    } else {
      temp.data[data.field] = data.event.target ? data.event.target.value : data.event;
    }
  },

  updateStep(stateData, step) {
    const temp = stateData;
    temp.step = step;
  },

  updateData(stateData, data) {
    const temp = stateData;
    temp.data.name = data.name;
    temp.data.teacherName = data.staff.name;
    temp.data.teacherEmail = data.staff.email;
    temp.data.phoneNumber = data.phone_number;
    temp.data.endDate = ref(dayjs(data.contractInfo.ended_at, temp.dateFormat));
    temp.data.startDate = ref(dayjs(data.contractInfo.started_at, temp.dateFormat));
    temp.data.mstTextbookCourseIds = data.mstTextbookCourseIds.map(Number);
    temp.data.selectedSellerIds = data.selectedSellerIds;
    temp.data.staffId = data.staff.id;
    temp.data.id = data.id;
  },
  
};

export const getters = {};

export const actions = {

  async getAllSchool() {
    const { data } = await educationalInsitutionApi.getAll();
    this.commit('seller/setSchool', data);
  },

  setParams(_, payload) {
    this.commit('educationalInstitution/setParams', payload);
  },

  async checkTeacherEmail(_, payload) {
    try {
      const response = await educationalInsitutionApi.checkTeacherEmail(payload);
      const { data } = response;
      return data;
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error.response.data.message ?? null,
      };
    }
  },

  async create(_, payload) {
    try {
      const response = await educationalInsitutionApi.createData(payload);
      const { data } = response;
      return data;
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error.response.data.message ?? null,
      };
    }
  },

  async update(_, payload) {
    try {
      const response = await educationalInsitutionApi.updateData(payload);
      const { data } = response;
      return data;
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error.response.data.message ?? null,
      };
    }
  },

  async getEduInstitutions(_, payload) {
    const { data } = await educationalInsitutionApi.getEduInstitutions(payload);
    this.commit('educationalInstitution/setEduInstitutions', data);
  },

  async getSellersByAgency(_, payload) {
    try {
      const response = await educationalInsitutionApi.getSellersByAgency(payload);
      const { data } = response;
      return data;
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error.response.data.message ?? null,
      };
    }
  },

  updateField(_, payload) {
    this.commit('educationalInstitution/updateField', payload);
  },

  updateStep(_, payload) {
    this.commit('educationalInstitution/updateStep', payload);
  },

  syncData(_, payload) {
    this.commit('educationalInstitution/updateData', payload);
  }
};
