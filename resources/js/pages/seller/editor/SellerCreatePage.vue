<template>
  <div class="container-fluid" id="create-seller">
    <h1 class="title-common">{{title}}</h1>
    <div class="dealer-step">
      <nav-step :step="sellerData.step"></nav-step>
    </div>
    <div class="seller-content">
      <seller-information-page v-if="sellerData.step==='information'"
                               :sellerEducationalIns="sellerEducationalIns">
      </seller-information-page>
      <seller-confirm-page v-if="sellerData.step==='confirm'"
                           :sellerEducationalIns="sellerEducationalIns">
      </seller-confirm-page>
      <seller-create-done-page v-if="sellerData.step==='done'" :sellerId="sellerData.id">
      </seller-create-done-page>
      <select-educational-ins-page :sellerEducationalIns="sellerEducationalIns"
                                   @handleSelectedEducationalIns="handleSelectedEducationalIns">
      </select-educational-ins-page>
    </div>
  </div>
</template>

<script>
import { mapActions, mapState } from 'vuex';

export default {
  computed: {
    ...mapState('seller', ['sellerData'])
  },

  created() {
    let seller = this.seller;
    seller.educationalInsIds = this.utils.getIdInEducationalIns(this.seller_educational_ins);
    this.updateSellerData(seller);
    this.sellerEducationalIns = this.seller_educational_ins;
    this.title = this.seller.id ? '販売店スタッフ編集' : '販売店スタッフ新規登録';
  },

  props: {
    seller: {
      type: Object,
      default() {
        return {};
      },
    },
    seller_educational_ins: {
      type: Object,
      default() {
        return {};
      },
    },
  },
  data() {
    return {
      title: '',
      sellerEducationalIns: {
        type: Array,
        default: []
      }
    };
  },
  methods: {
    ...mapActions('seller', ['updateSellerData']),

    handleSelectedEducationalIns(res) {
       this.sellerEducationalIns = res.data;
    }
  },

};
</script>

<style lang="scss" scoped>
  .seller-content {
    background: #f8f8f8;
    padding: 72px 80px;
    border-radius: 16px;
  }
  #create-seller::-webkit-scrollbar {
    width: 0 !important
  }
</style>
