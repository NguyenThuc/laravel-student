<template>
  <div class="container" id="create-dealer">
    <h1 class="title-common">{{title}}</h1>
    <div class="dealer-step">
      <nav-step :step="this.step"></nav-step>
    </div>
    <div class="dealer-content">
      <edu-institution-information
        v-if="step==='information'"
        :sellers="sellers" :categories="categories"
      >
      </edu-institution-information>
      <edu-institution-confirm
        v-if="step==='confirm'"
        :sellers="sellers" :categories="categories"
      >
      </edu-institution-confirm>
      <edu-institution-create-done v-if="step==='done'">
      </edu-institution-create-done>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
export default {
  computed: {
    ...mapState('educationalInstitution', ['data', 'step']),
  },
  created() {
    if(this.edu_institution) {
      this.syncData(this.edu_institution);
      this.title = '教育機関編集';
    }

  },
  props: {
    sellers: {
      type: Object,
    },
    categories: {
      type: Object,
    },
    edu_institution: {
      type: Object,
    },
  },
  data() {
    return {
      title: '教育機関新規登録',
    };
  },
  methods: {
    ...mapActions('educationalInstitution', ['syncData']),
  },
};
</script>

<style lang="scss" scoped>
.dealer-content {
  background: #f8f8f8;
  padding: 20px 48px;
  border-radius: 16px;
}
#create-dealer::-webkit-scrollbar {
  width: 0 !important
}
</style>
