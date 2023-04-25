import axios from 'axios';

export default {
  getAll: (param = {}) => axios.get('/educational_institutions/get-all', param),
  getEduInstitutions: (params = {}) => axios.get('/educational_institutions/get-list', { params }),
  checkTeacherEmail: (param = {}) => axios.get('/educational_institutions/check-teacher-email', param),
  createData: (param = {}) => axios.post('/educational_institutions/create', param),
  updateData: (param = {}) => axios.put('/educational_institutions/' + param.id, param),
  getSellersByAgency: (params = {}) => axios.get('get-by-agency', params),
};
