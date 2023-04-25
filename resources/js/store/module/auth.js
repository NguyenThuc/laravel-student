import AuthApi from '../api/authApi';

export const state = {
  seller: {}
};

export const mutations = {
  setSeller(stateData, data) {
    const temp = stateData;
    temp.seller = data;
  },
};

export const getters = {};

export const actions = {
  /**
   * Login action
   * @param {*} _
   * @param {*} credentials user credential data
   * @returns
   */
  async login(_, credentials) {
    try {
      return await AuthApi.login(credentials);
    } catch (error) {
      return {
        success: false,
        message: error.response.data.message ?? null,
      };
    }
  },

  async updatePassword(_, payload) {
    try {
      const response = await AuthApi.updatePassword(payload);
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

  async forgotPassword(_, payload) {
    try {
      const { data } = await AuthApi.forgotPassword(payload);
      return data;
    } catch (error) {
      const { errors } = error.response.data;
      return {
        type: 'error',
        message: errors ?? null,
      };
    }
  },

  async resetPassword(_, payload) {
    try {
      const { data } = await AuthApi.resetPassword(payload.params);
      return {
        success: true,
        data,
      };
    } catch (error) {
      return null;
    }
  },

  async createPassword(_, payload) {
    try {
      const { data } = await AuthApi.createPassword(payload.params);
      return {
        success: true,
        data,
      };
    } catch (error) {
      return null;
    }
  },

  setSeller(_, payload) {
    this.commit('auth/setSeller', payload);
  },
};
