import constants from '../constants';

const utils = {
  getIdInEducationalIns(list) {
    if (list && list.length > 0) {
      return list.map((eduIns) => eduIns.id);
    }
    return [];
  },

  checkAdmin(role) {
    return role === constants.sellerRole.admin;
  },

  checkOwner(auth, seller) {
    return auth.id === seller.id;
  }
};

export default utils;
