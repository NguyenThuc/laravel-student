<template>
  <div id="seller-detail">
    <div class="row mx-4">
      <div class="col-md-12">
        <div class="header">
          <span class="title">{{ isDeleted ? '販売店スタッフ削除' : '販売店スタッフ詳細' }}</span>
          <a
            v-if="!isDeleted && utils.checkAdmin(auth.role)"
            @click="deleteItem(seller.id)"
            class="btn btn-default btn-delete"
          >
            削除
          </a>
        </div>
      </div>

      <template v-if="seller && !isDeleted">
        <div class="col-md-12">
          <div class="row">
            <div class="col-md-12">
              <div class="body">
                <div class="body__header">
                  <div class="label">氏名</div>
                  <div class="name txt-ellipsis">{{ seller.name }}</div>
                </div>

                <div class="body__content">
                  <div class="left">
                    <p class="column-name">登録日時</p>
                    <p class="column-name">更新日時</p>
                    <p class="column-name">メールアドレス</p>
                    <p class="column-name">権限</p>
                    <p class="column-name">担当教育機関</p>
                  </div>
                  <div class="right">
                    <p class="column-value created_at">
                      {{ filters.formatDate(seller.created_at) }}
                    </p>
                    <p class="column-value updated_at">
                      {{ filters.formatDateTime(seller.updated_at) }}
                    </p>
                    <p class="column-value txt-ellipsis">{{ seller.email }}</p>
                    <p class="column-value txt-ellipsis">{{ seller.roleName }}</p>
                    <div class="column-value txt-ellipsis list-schools">
                      <p v-for="school in seller.educationalInstitutions" :key="school.id">
                        {{ school.name }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-12">
              <div :class="[permissionEdit ? '' : 'justify-content-center',
                    'footer']">
                <a href="/sellers" class="btn btn-default">一覧に戻る</a>
                <a
                  v-if="permissionEdit"
                  class="btn btn-edit"
                  :href="`/sellers/${seller.id}/edit`"
                >編集</a>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template v-if="isDeleted">
        <div class="col-md-12 delete-success">
          <div class="row">
            <div class="col-md-12">
              <div class="body">
                <div class="title">販売店スタッフの登録削除が完了しました</div>

                <div class="actions">
                  <a href="/sellers" class="btn btn-default btn-back">一覧画面へ戻る</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex';
import swal from 'sweetalert2';

export default {
  data() {
    return {
      isDeleted: false,
      permissionEdit: false
    };
  },

  mounted() {
    this.permissionEdit = this.utils.checkAdmin(this.auth.role);
  },

  props: {
    seller: {
      type: Object,
    },
  },
  methods: {
    ...mapActions('seller', ['deleteSeller']),
    deleteItem(id) {
      swal.fire({
        title: '選択した販売店スタッフの登録を削除します',
        text: 'よろしければ削除ボタンを押してください',
        showCancelButton: true,
        reverseButtons: true,
        cancelButtonColor: '#ECECEC',
        confirmButtonColor: '#007EF2',
        confirmButtonText: '削除する',
        cancelButtonText: 'キャンセル',
      }).then(async (result) => {
        if (result.isConfirmed) {
          const response = await this.deleteSeller(id);
          if (response.status === 'success') {
            this.isDeleted = true;
          }
        }
      });
    },
  },
};
</script>

<style lang="scss">
  #seller-detail {
    .btn {
      &:hover,
      &:focus,
      &:active {
        outline: none;
        box-shadow: none;
      }
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px !important;
      font-family: 'Hiragino Kaku Gothic ProN';

      .title {
        font-size: 34px;
        font-weight: 700;
      }

      .btn-delete {
        font-size: 14px;
        font-weight: 600;
        border-radius: 50px;
        color: #fff;
        min-width: 80px;
        min-height: 40px;
        background: #fff;
        border-color: #ed1c24;
        color: #ed1c24;
        padding-top: 8px;

        &:hover {
          background: #fff;
          color: #fff;
          background: #ed1c24;
        }
      }
    }

    .header,
    .body,
    .footer {
      max-width: 760px;
    }

    .body {
      background: #f8f8f8 !important;
      border-radius: 20px;
      margin-top: 20px;
      padding: 37px 16px 32px 16px;
      font-family: 'Hiragino Kaku Gothic ProN';

      &__header {
        display: flex;
        align-items: center;

        .label {
          background: #162B44;
          border-radius: 5px;
          min-width: 80px;
          min-height: 30px;
          font-size: 12px;
          color: #fff;
          display: flex;
          align-items: center;
          justify-content: center;
          margin-right: 16px;
        }

        .name {
          color: #162B44;
          font-size: 24px;
        }
      }

      &__content {
        background: #fff;
        border-radius: 10px;
        min-height: 188px;
        margin-top: 36px;
        display: flex;
        justify-content: space-between;

        .left {
          width: 35%;
          text-align: right;
          padding: 24px 32px 32px 0;
          border-right: 1px solid #ccc;
        }

        .right {
          width: 65%;
          text-align: left;
          padding: 24px 10px 32px 32px;

          .list-schools {
            p {
              margin-bottom: 10px;

              &:last-child {
                margin-bottom: 0;
              }
            }
          }
        }

        .column-name,
        .column-value {
          color:#162B44;
          font-size: 18px;
          font-weight: 400;
          margin-bottom: 16px;
        }

        .column-name:last-child,
        .column-value:last-child {
          margin-bottom: 0;
        }
      }
    }

    .footer {
      margin-top: 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-family: 'Hiragino Kaku Gothic ProN';

      .btn {
        width: calc(50% - 16px);
        background: #ECECEC;
        font-size: 18px;
        font-weight: bold;
        padding: 13px;
        border-radius: 5px;
        color: #162B44;

        &:focus,
        &:hover,
        &:active {
          box-shadow: none;
          outline: none;
        }
      }

      .btn-edit {
        background: #007EF2;
        border-color: #007EF2;
        color: #fff;
      }
    }

    .delete-success {
      .body {
        padding: 80px;
        font-weight: 400;
        text-align: center;

        .title {
          color: #162B44;
          font-size: 34px;
        }

        .actions {
          .btn-back {
            font-size: 18px;
            font-weight: 700;
            background: #ECECEC;
            min-width: 284px;
            min-height: 60px;
            border-radius: 5px;
            margin-top: 33px;
            padding: 12px 0;
          }
        }
      }
    }
  }

  .swal2-container {
    .swal2-popup {
      padding: 80px;
      background: #F8F8F8;
      border-radius: 15px;
      min-width: 760px;

      .swal2-title {
        font-size: 34px;
        font-weight: 400;
        margin-bottom: 34px;
      }

      .swal2-html-container {
        font-size: 18px;
        font-weight: 400;
        margin-top: 0;
      }

      .swal2-actions {
        margin-top: 32px;

        .swal2-cancel,
        .swal2-confirm {
          min-width: 268px;
          min-height: 60px;
          font-size: 18px;
          font-weight: 700;
          padding: 0;

          &:hover,
          &:focus,
          &:active {
            outline: none;
            box-shadow: none;
          }
        }

        .swal2-cancel {
          color: #162B44;
          margin-right: 16px;
        }

        .swal2-confirm {
          margin-left: 16px;
        }
      }
    }
  }
</style>
