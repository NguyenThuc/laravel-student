<template>
  <div class="detail-form" id="school-detail ">
    <div class="row mx-4">
      <div class="col-md-12">
        <div class="header">
          <span class="title">教育機関情報詳細</span>
        </div>
      </div>
      <template v-if="edu_institution">
        <div class="col-md-12">
          <div class="row">
            <div class="col-md-12">
              <div class="body">
                <div class="body__header">
                  <div class="label">教育機関名</div>
                  <div class="name">{{ edu_institution.name }}</div>
                </div>

                <div class="body__content">
                  <table>
                    <tbody>
                      <tr>
                        <td class="column-name">電話番号</td>
                        <td class="column-value">{{ edu_institution.phone_number }}</td>
                      </tr>
                      <tr>
                        <td class="column-name">契約開始日</td>
                        <td class="column-value">{{ filters.formatDate(edu_institution.contractInfo.started_at) }}</td>
                      </tr>
                      <tr>
                        <td class="column-name">契約完了日</td>
                        <td class="column-value">{{ filters.formatDate(edu_institution.contractInfo.ended_at) }}</td>
                      </tr>
                      <tr>
                        <td class="column-name">オーナー氏名</td>
                        <td class="column-value">
                          {{ edu_institution.staff.name }}
                        </td>
                      </tr>
                      <tr>
                        <td class="column-name">オーナーメールアドレス</td>
                        <td class="column-value">{{ edu_institution.staff.email }}</td>
                      </tr>
                      <tr>
                        <td class="column-name">対象教材（複数選択可）</td>
                        <td class="column-value">
                          <p v-for="course in edu_institution.courses" :key="course.id">{{ course.name }}</p>
                        </td>
                      </tr>
                      <tr>
                        <td class="column-name">担当販売店スタッフ</td>
                        <td class="column-value">
                          <p v-for="seller in edu_institution.sellers" :key="seller.id">{{ seller.name }}</p>
                        </td>
                     </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-md-12">
              <div class="footer">
                <a href="/educational_institutions" class="btn btn-default">一覧に戻る</a>
                <a
                  class="btn btn-edit"
                  :href="`/educational_institutions/${edu_institution.id}/edit`"
                >編集する</a>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>

export default {
  props: {
    edu_institution: {
      type: Object,
    },
  },
};
</script>

<style lang="scss" scoped>
  .btn-edit {
    background: #007EF2 !important;
    color: #FFFFFF !important;
    border-color: #007EF2;
  }
</style>
