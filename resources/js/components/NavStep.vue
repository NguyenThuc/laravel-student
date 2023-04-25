<template>
  <div id="nav-step">
    <div class="btn-group">
      <a href="#" id="breadcrumbs-one"
         :class="classes[0]"
         style="z-index: 3;">情報入力
      </a>
      <a href="#" id="breadcrumbs-two"
         :class="classes[1]"
         style="z-index: 2;">内容確認
      </a>
      <a href="#" id="breadcrumbs-three"
         :class="classes[2]"
         style="z-index: 1;">登録完了
      </a>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    step: {
      type: String,
    },
  },
  data() {
    return {
      notActiveFirst: 'btn btn-outline-primary noHover pointer-standby',
      notActiveLast: 'btn btn-outline-primary noHover pointer-last',
      active: 'btn btn-primary pointer noHover',
      classes: [],
    };
  },

  beforeUpdate() {
    this.handleClass();
  },

  mounted() {
    this.handleClass();
  },

  methods: {
    handleClass() {
      switch (this.step) {
        case 'confirm':
          this.classes = [this.notActiveFirst, this.active, this.notActiveLast];
          break;
        case 'done':
          this.classes = [this.notActiveFirst, this.notActiveLast, this.active];
          break;
        default:
          this.classes = [this.active, this.notActiveFirst, this.notActiveLast];
      }
    },
  },
};
</script>

<style lang="scss" scoped>
  #nav-step {
    .btn-group {
      width: 100%;
      margin-bottom: 32px;
      font-size: 18px;
    }
    .btn-group > .btn:not(:first-child),
    .btn-group > .btn-group:not(:first-child) {
      margin-left: -1px;
    }
    .btn-group > .btn:not(:last-child):not(.dropdown-toggle),
    .btn-group > .btn-group:not(:last-child) > .btn {
      border-top-right-radius: 0;
      border-bottom-right-radius: 0;
    }
    .btn-group > .btn:nth-child(n+3),
    .btn-group > :not(.btn-check) + .btn,
    .btn-group > .btn-group:not(:first-child) > .btn {
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
    }

    .pointer {
      width: 300px;
      height: 60px;
      position: relative;
      background-color: #0d6efd;
      color: #ffffff;
      line-height: 45px;
    }
    .pointer:after {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      width: 0;
      height: 0;
      /* border-left: 20px solid #0d6efd; */
      border-top: 20px solid transparent;
      border-bottom: 20px solid transparent;
    }
    .pointer:before {
      content: "";
      position: absolute;
      right: -26px;
      bottom: 0;
      width: 0;
      height: 0;
      border-left: 26px solid #0d6efd;
      border-top: 31px solid transparent;
      border-bottom: 27px solid transparent;
    }
    .noHover{
      pointer-events: none;
    }

    .pointer-standby {
      width: 300px;
      height: 60px;
      position: relative;
      background-color: transparent;
      line-height: 45px;
    }

    .pointer-standby:before {
      content: "";
      position: absolute;
      bottom: 0;
      width: 0;
      height: 0;
      border-left: 26px solid #ffffff;
      border-top: 31px solid transparent;
      border-bottom: 27px solid transparent;
      right: -26px;
    }

    .pointer-last {
      width: 300px;
      height: 60px;
      position: relative;
      background-color: transparent;
      line-height: 45px;
    }
    .pointer-last:after {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      width: 0;
      height: 0;
      border-left: 26px solid #0d6efd;
      border-top: 31px solid transparent;
      border-bottom: 27px solid transparent;
    }
  }
</style>
