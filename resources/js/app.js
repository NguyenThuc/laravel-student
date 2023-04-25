import { createApp } from 'vue';
import { setLocale } from 'yup';
import Antd from 'ant-design-vue';
import swal from 'sweetalert2';
import suggestive from './validation/locale/yup';
import store from './store';
import 'ant-design-vue/dist/antd.css';
import filters from './filter';
import utils from './utils';
import constants from './constants';

setLocale(suggestive);

require('./bootstrap');
require('./validation/rules');

window.swal = swal;
window.toast = swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 5000,
});

const app = createApp();
app.use(store);
app.use(Antd);
// Auto register components
const files = require.context('./', true, /\.vue$/i);
files.keys().map((key) => app.component(key.split('/').pop().split('.')[0], files(key).default));
app.config.globalProperties.filters = filters;
app.config.globalProperties.constants = constants;
app.config.globalProperties.utils = utils;
let auth = {};
if (JSON.parse(localStorage.getItem('vuex'))) {
   auth = JSON.parse(localStorage.getItem('vuex')).auth.seller;
}
app.config.globalProperties.auth = auth;

app.mount('#app');
