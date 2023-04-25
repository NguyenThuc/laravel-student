<template>
  <div id="seller-detail">
    <div class="row mx-4">
      <div class="col-md-12">
        <div class="header">
          <span class="title">{{ this.changeFinished ? '先生オーナー権限変更' : '教育機関スタッフ情報詳細' }}</span>
        </div>
      </div>

      <template v-if="educational_staff && !this.changeFinished">
        <div class="col-md-12">
          <div class="row">
            <div class="col-md-12">
              <div class="body">
                <div class="body__header">
                  <div class="label">氏名</div>
                  <div class="name txt-ellipsis">{{ educational_staff.name }}
                    <span v-if="educational_staff.name_kana">（ {{educational_staff.name_kana}} ）</span></div>
                </div>

                <div class="body__content">
                  <div class="left">
                    <p class="column-name">登録日時</p>
                    <p class="column-name">更新日時</p>
                    <p class="column-name">教育機関</p>
                    <p class="column-name">権限</p>
                    <p v-if="checkOwner(educational_staff.role)"  class="column-name">メールアドレス</p>
                    <p class="column-name">教室</p>
                  </div>
                  <div class="right">
                    <p class="column-value created_at">
                      {{ filters.formatDate(educational_staff.created_at) }}
                    </p>
                    <p class="column-value updated_at">
                      {{ filters.formatDateTime(educational_staff.updated_at) }}
                    </p>
                    <p class="column-value txt-ellipsis">{{ educational_staff.educationalInstitution.name }}</p>
                    <p class="column-value txt-ellipsis">{{ educational_staff.roleName}}</p>
                    <p v-if="checkOwner(educational_staff.role)" class="column-value txt-ellipsis">{{ educational_staff.email}}</p>
                    <div class="column-value txt-ellipsis">
                      <p v-for="classroom in educational_staff.classrooms" :key="classroom.id"> {{classroom.name}}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-12">
              <div class="footer">
                <a href="/educational_staffs" class="btn btn-default">一覧に戻る</a>
                <a class="btn btn-edit" data-toggle="modal" data-target="#confirmChange">オーナー権限を設定する</a>
              </div>
            </div>
          </div>
        </div>
      </template>
      <!-- Modal -->
      <change-owner-model :isChange="is_change" :educationalStaff="educational_staff"></change-owner-model>
      <template v-if="this.changeFinished">
        <div class="col-md-12 delete-success">
          <div class="row">
            <div class="col-md-12">
              <div class="body">
                <div class="title">オーナー権限が変更しました</div>
                <div class="actions">
                  <a href="/educational_staffs" class="btn btn-default btn-back">一覧画面へ戻る</a>
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

import { mapState } from 'vuex';
  export default {
    props: {
      educational_staff: {
        type: Object,
      },
      is_change: Boolean,
    },
    methods: {
      checkOwner(role) {
        return role === this.constants.educationalStaffRole.owner;
      },
    },
    computed: {
      ...mapState('educationStaff', ['changeFinished']),
    },
  };
</script>

<style lang="scss" scoped>
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

</style>
