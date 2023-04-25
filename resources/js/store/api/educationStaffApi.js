import axios from 'axios';
export default {
  getListEducationStaff: (params = {}) => axios.get('/educational_staffs', { params }),
  getListClassRoom: (param = {}) => axios.get(`/educational_staffs/educational_institution/${param.id_institution}/list_classroom`, { param }),
  getSchools: (params = {}) => axios.get('/educational_staffs/list_edu_institution', { params }),
  changeOwnerRole: (params = {}) => axios.put(`/educational_staffs/${params.id}/change-owner`, params),
};
