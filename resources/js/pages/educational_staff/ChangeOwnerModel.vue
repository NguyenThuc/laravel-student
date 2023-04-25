<template>
  <div class="modal fade" id="confirmChange" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">{{title}}</h5>
        </div>
        <div class="modal-body">
          <div class="content-confirm" v-if="isChange">
            <p>以下のアカウントへ、この教育機関のオーナー権限を設定しますか？</p>
            <p>※現在のオーナーは「スタッフ権限」に変更されます。</p>
            <div class="content-info">
              <p><span class="label">アカウント氏名</span>： {{ educationalStaff.name }} （ {{educationalStaff.name_kana}} ）</p>
              <p><span class="label">教育機関</span>： {{ educationalStaff.educationalInstitution.name }}</p>
            </div>
          </div>
          <div class="content-alert" v-if="!isChange">
            <p>オーナー権限を設定するには以下の条件が必要です</p>
            <ul class="content-info">
              <li>現在の権限が「スタッフ」又は「一般教職員」</li>
              <li>メールアドレスが登録されている事</li>
            </ul>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary btn-submit-modal"
                  @click="changeOwner" v-if="isChange">設定する</button>
          <button type="button" class="btn btn-secondary btn-close-modal" data-dismiss="modal" ref="Close">{{textBtnClose}}</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>

  import { mapActions } from 'vuex';

  export default {
    mounted() {
      this.title = this.isChange ? 'Confirmation' : 'Alert';
      this.textBtnClose = this.isChange ? 'キャンセル' : '閉じる';
    },
    data() {
      return {
        title: '',
        textBtnClose: ''
      };
    },
    props: {
      isChange: Boolean,
      educationalStaff: {
        type: Object,
      },
    },
    methods: {
      ...mapActions('educationStaff', ['changeOwnerRole', 'updateChangeFinished']),
      checkOwner(role) {
        return role === this.constants.educationalStaffRole.owner;
      },

      async changeOwner() {
        let params = {
          id: this.educationalStaff.id,
        };
        let response = await this.changeOwnerRole(params);
        if (response.success) {
          this.updateChangeFinished(true);
          this.$refs.Close.click();
        }
      }
    }
  };
</script>
<style lang="scss" scoped>
  #confirmChange {
    top: auto;
    .modal-dialog {
      max-width: 650px;
    }
    .modal-content {
      border-radius: 24px;
      .modal-title {
        color: #162B44;
        font-weight: 600
      }
      .modal-body {
        color: black;
        font-weight: 600;
        font-size: 18px;
        padding: 15px 65px;
        p {
          margin-bottom: 4px;
        }
        .content-confirm {
          .content-info {
            padding: 0 64px;
            .label {
              width: 130px;
              display: inline-block;
            }
          }
        }
        .content-alert {
          .content-info {
            padding-left: 24px;
          }
        }
      }
    }
    .modal-footer {
      border: none;
      justify-content: center;
      padding-top: 0;
      button {
        width: 170px;
        margin-right: 30px;
      }
      button:last-child {
        margin-right: 0;
      }
    }
    .content-info {
      margin-top: 30px;
    }
  }

</style>
