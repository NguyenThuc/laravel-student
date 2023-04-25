import axios from 'axios';

export default {
  login: (credential = {}) => axios.post('/login', credential),
  updatePassword: (payload = {}) => axios.post('/change-password', payload),
  checkExistEmail: (payload = {}) => axios.post('/chech-exist-email', payload),
  forgotPassword: (payload = {}) => axios.post('/forgot-password', payload),
  resetPassword: (payload = {}) => axios.post('/reset-password', payload),
  createPassword: (payload = {}) => axios.post('/create-password', payload),
};
