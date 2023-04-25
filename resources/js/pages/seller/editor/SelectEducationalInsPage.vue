<template>
  <!-- Modal -->
  <div class="modal fade" id="selectEducationalIns"
       tabindex="-1" role="dialog"
       aria-labelledby="exampleModalLabel"
       aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-custom" role="document">
      <div class="modal-content">
        <div class="modal-body">
          <div class="row h-100">
            <div :class="[sellerData.id ? 'col-5' : 'col-5-custom']">
              <div class="title">教育機関一覧</div>
              <select class="custom-select content"
                      v-model="selectedEducationalInsIds"
                      @click="area='list'"
                      multiple>
                <option v-for="eduIns in listEducationalIns" :key="eduIns.id" :value="eduIns.id">
                  {{eduIns.name}}
                </option>
              </select>
            </div>
            <div class="col-1 group-action">
              <button id="right"
                      type="button"
                      class="btn btn-secondary
                      btn-sm list-manage
                      increase btn-arrow col-12"
                      @click="moveSelect()">
                <i class="fa fa-angle-double-right"
                   style="font-size: 27px;">
                </i>
              </button>
              <button id="left"
                      class="btn btn-secondary
                      btn-sm list-manage
                      decrease btn-arrow
                      col-12"
                      @click="moveList()">
                <i class="fa fa-angle-double-left" style="font-size: 27px;"></i>
              </button>
            </div>
            <div class="col-3" v-if="sellerData.id">
              <div class="title">登録済みの教育機関</div>
              <select class="custom-select content list-selected"
                      multiple
                      @click="area='selectSeller'"
                      v-model="registeredEducationalInsIds">
                <option v-for="eduIns in listEducationalInsSeller"
                        :key="eduIns.id"
                        :value="eduIns.id">
                  {{eduIns.name}}
                </option>
              </select>
            </div>
            <div :class="[sellerData.id ? 'col-3' : 'col-5-custom']">
              <div class="title">登録する教育機関</div>
              <select class="custom-select content"
                      @click="area='selected'"
                      v-model="registerEducationalInsIds"
                      multiple>
                <option v-for="eduIns in selectedEducationalIns" :key="eduIns.id" :value="eduIns.id">
                  {{eduIns.name}}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <p class="error-text">{{error}}</p>
          <button type="button"
                  class="btn btn-secondary btn-close-modal"
                  data-dismiss="modal" @click="revertData()" ref="Close">キャンセル
          </button>
          <button type="button"
                  class="btn btn-primary btn-submit-modal"
                  @click="submit()">登録
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions, mapState } from 'vuex';

export default {

  props: {
    sellerEducationalIns: {
      type: Array,
      default() {
        return [];
      }
    }
  },

  computed: {
    ...mapState('seller', ['sellerData'])
  },

  mounted() {
    this.getListEducationalIns();
    this.listEducationalInsSeller = this.sellerEducationalIns;
  },

  data() {
    return {
      listEducationalIns: [],
      selectedEducationalInsIds: [],
      registerEducationalInsIds: [],
      selectedEducationalIns: [],
      listEducationalInsSeller: [],
      registeredEducationalInsIds: [],
      area: '',
      dataInfo: this.data,
      error: '',
    };
  },

  methods: {
    ...mapActions('seller', ['getEducationalInstitutionsByAgency', 'updateSellerData']),

    async getListEducationalIns() {
      const response = await this.getEducationalInstitutionsByAgency(this.sellerData.agency_id, {});
      if (response.success) {
        const { educationalInsIds } = this.sellerData;
        this.listEducationalIns = response.data.data;
        if (educationalInsIds.length > 0) {
          this.listEducationalIns = this.listEducationalIns.filter((eduIds) => !educationalInsIds.includes(eduIds.id));
        }
      }
      this.saveDataSession(this.listEducationalIns, this.listEducationalInsSeller, this.selectedEducationalIns);
    },

    moveSelect() {
      if (this.area === 'list' && this.selectedEducationalInsIds.length > 0) {
        this.selectedEducationalInsIds.forEach((val) => {
          this.handleMoveList(this.listEducationalIns, this.selectedEducationalIns, val);
        });
      }
      this.area = '';
      this.clearSeletedData();
    },

    moveList() {
      if (this.area === 'selected' && this.registerEducationalInsIds.length > 0) {
        this.registerEducationalInsIds.forEach((val) => {
          this.handleMoveList(this.selectedEducationalIns, this.listEducationalIns, val);
        });
      }
      if (this.area === 'selectSeller' && this.registeredEducationalInsIds.length > 0) {
        this.registeredEducationalInsIds.forEach((val) => {
          this.handleMoveList(this.listEducationalInsSeller, this.listEducationalIns, val);
        });
      }
      this.area = '';
      this.clearSeletedData();
    },

    submit() {
      const { selectedEducationalIns } = this;
      let listEducationalInsSubmit = selectedEducationalIns;
      if (this.listEducationalInsSeller.length > 0) {
        listEducationalInsSubmit = listEducationalInsSubmit.concat(this.listEducationalInsSeller);
      }
      if (listEducationalInsSubmit.length > 0) {
        let idsInEducationalIns = this.utils.getIdInEducationalIns(listEducationalInsSubmit);
        this.updateSellerData({educationalInsIds: idsInEducationalIns});
        this.$emit('handleSelectedEducationalIns', { data: listEducationalInsSubmit });
        this.saveDataSession(this.listEducationalIns, this.listEducationalInsSeller, selectedEducationalIns);
        this.$refs.Close.click();
      } else {
        this.error = '登録する教育機関は、必ず指定してください。';
      }
    },

    handleMoveList(listEducationalIns, selectedEducationalIns, selected) {
      const index = listEducationalIns.findIndex((eduIns) => eduIns.id === selected);
      selectedEducationalIns.push(listEducationalIns[index]);
      listEducationalIns.splice(index, 1);
    },

    revertData() {
      this.getDataSession();
      let listEducationalInsSubmit = this.selectedEducationalIns;
      if (this.listEducationalInsSeller.length > 0) {
        listEducationalInsSubmit = listEducationalInsSubmit.concat(this.listEducationalInsSeller);
      }
      this.$emit('handleSelectedEducationalIns', { data: listEducationalInsSubmit });
      this.error = '';
    },

    saveDataSession(listEducationalIns, listEducationalInsSeller, selectedEducationalIns) {
      sessionStorage.setItem('listEducationalIns', JSON.stringify(listEducationalIns));
      sessionStorage.setItem('listEducationalInsSeller', JSON.stringify(listEducationalInsSeller));
      sessionStorage.setItem('selectedEducationalIns', JSON.stringify(selectedEducationalIns));
    },

    getDataSession() {
      this.listEducationalIns = JSON.parse(sessionStorage.getItem('listEducationalIns'));
      this.selectedEducationalIns = JSON.parse(sessionStorage.getItem('selectedEducationalIns'));
      this.listEducationalInsSeller = JSON.parse(sessionStorage.getItem('listEducationalInsSeller'));
    },

    clearSeletedData() {
      this.registeredEducationalInsIds = [];
      this.registerEducationalInsIds = [];
      this.selectedEducationalInsIds = [];
    },
  },
};
</script>
<style lang="scss" scoped>
  #selectEducationalIns {
    padding-top: 4%;

    .modal-content {
      height: 72vh;
      overflow: scroll;
      border-radius: 14px;
      padding: 20px;
      background: #f8f8f8;
    }

    .modal-content::-webkit-scrollbar {
      width: 0 !important
    }

    .modal-custom {
      max-width: 65%;
    }

    .modal-body {
      border: none;
    }

    .modal-footer {
      border: none;
      display: block;
      text-align: center;
    }

    .title {
      font-size: 15px;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .content {
      background: inherit;
      border: 2px solid lightgray;
      border-radius: 10px;
      height: 100%;
      font-size: 18px;
    }

    .content option {
      padding: 10px;
      font-weight: 600;
      font-size: 14px;
    }

    .content::-webkit-scrollbar {
      width: 0 !important
    }

    .group-action {
      height: 100%;
      padding-top: 18%;
      padding-left: 27px;

      button {
        background: #ffffff;
        margin-bottom: 24px;
        width: 42px;

        i {
          color: #000000;
        }
      }
    }

    .list-selected {
      background: rgb(237 237 237) !important;
    }

    .modal-footer {
      margin-top: 5%;
      justify-content: center;

      button {
        border: none;
        border-radius: 25px;
        padding: 10px 52px;
        font-weight: 600;
      }

      .btn-close {
        background: #dddddd;
        color: #000000;
        margin-right: 32px;
      }
    }
    .error-text {
      font-size: 18px;
      color: red;
      font-weight: 400;
    }
    .col-5-custom {
      flex: 0 0 45.666667%;
      max-width: 45.666667%;
    }
  }
</style>
