import textbookCategoryApi from '../api/textbookCategoryApi';

export const state = {};

export const mutations = {};

export const getters = {};

export const actions = {

  async getCategory() {
    try {
      const response = await textbookCategoryApi.getCategory();
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
};
