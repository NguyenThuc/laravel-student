import axios from 'axios';

export default {
  checkEmail: (param = {}) => axios.get('/sellers/check-email', param),
  getSellers: (params = {}) => axios.get('/sellers', { params }),
  createData: (param = {}) => axios.post('/sellers', param),
  updateData: (param = {}) => axios.put(`/sellers/${param.id}`, param),
  updateProfile: (param = {}) => axios.put(`/profile`, param),
  deleteSeller: (param = {}) => axios.delete(`/sellers/${param}`),
  getEducationalInstitutionsByAgency: (id, param = {}) => axios.get(`/agencies/${id}/educational-institutions`, param),
};
