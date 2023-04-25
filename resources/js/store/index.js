import createPersistedState from 'vuex-persistedstate';
import { createStore } from 'vuex';

import modules from './module';

export default createStore({
  modules,
  plugins: [createPersistedState(
    { paths: ['auth'] },
  )],
  strict: process.env.NODE_ENV !== 'production',
});
