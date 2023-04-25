import moment from 'moment';

const filters = {
  formatDate(value) {
    return moment(value).format('YYYY/MM/DD');
  },
  formatDateTime(value) {
    return moment(value).format('YYYY/MM/DD　HH:mm:ss');
  },
};

export default filters;
