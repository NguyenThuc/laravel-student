<template>
  <div id="my-page">
    <div class="row px-4">
      <div class="col-md-12">
        <span class="title">登録情報確認</span>
      </div>

      <div class="col-md-12" v-if="seller">
        <div class="content">
          <div class="form-group">
            <label>氏名</label>
            <input
              type="text"
              class="form-control no-input"
              :value="seller.name"
              readonly
            />
          </div>
          <div class="form-group">
            <label>メールアドレス</label>
            <input
              type="email"
              class="form-control no-input"
              :value="seller.email"
              readonly
            />
          </div>
          <div class="form-group">
            <label>権限</label>
            <input
              type="text"
              class="form-control no-input"
              :value="seller.roleName"
              readonly
            />
          </div>
          <div class="form-group">
            <label>担当教育機関</label>
            <div class="schools">
              <div class="item" v-for="item in seller.educationalInstitutions" :key="item.id">
                {{ item.name }}
              </div>
            </div>
          </div>

          <div class="actions">
            <a :href="`/profile`" class="btn btn-default">編集</a>
            <a href="/change-password" class="btn btn-default"
              >パスワード変更</a
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MyPage',
  props: {
    seller: {
      type: Object,
      default() {
        return {};
      },
    },
  },
};
</script>

<style lang="scss">
  #my-page {
    .title {
      font-size: 34px;
      font-weight: 700;
    }

    .content {
      background: #f8f8f8 !important;
      border-radius: 20px;
      margin: 20px 0;
      padding: 60px;

      .form-group {
        margin-bottom: 20px;

        .form-control.no-input {
          background: transparent;
          border: 0;
          border-bottom: 1px solid #80808052;
          border-radius: 0;
          padding-bottom: 10px;
          font-size: 15px;

          &:hover,
          &:focus,
          &:active {
            background: transparent;
            box-shadow: none;
          }
        }
      }

      .schools {
        display: flex;
        flex-direction: column;
        width: 100%;
        font-size: 18px;
        padding: 13px 13px 13px 3px;
        border-radius: 5px;

        &:first-child {
          margin-bottom: 35px;
        }

        .item {
          border-bottom: 1px solid #80808052;
          padding: 10px 20px 20px 12px;
          font-size: 15px;
        }
      }

      .actions {
        margin-top: 30px;

        .btn {
          width: 100%;
          background: #ECECEC;
          font-size: 18px;
          font-weight: bold;
          padding: 13px;
          border-radius: 5px;
          color: #162B44;

          &:first-child {
            margin-bottom: 35px;
          }

          &:focus,
          &:hover,
          &:active {
            box-shadow: none;
            outline: none;
          }
        }
      }
    }
  }
</style>
