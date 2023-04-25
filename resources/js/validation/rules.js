import { defineRule, configure } from 'vee-validate';
import AllRules from '@vee-validate/rules';
import { localize } from '@vee-validate/i18n';
import mess from './locale/veevalidate';

// Default values
configure({
  validateOnBlur: false,
  validateOnChange: true,
  validateOnInput: false,
  validateOnModelUpdate: true,
  generateMessage: localize('ja', {
    messages: mess,
  }),
});

Object.keys(AllRules).forEach((rule) => {
  defineRule(rule, AllRules[rule]);
});

defineRule('checkRulePassword', (value) => {
  const regex = /^(?=.*?)(?=.*?[a-zA-Z])(?=.*?[0-9]).{8,32}$|^(?=.*?)(?=.*?[a-zA-Z0-9])(?=.*?[_#!%^&*-]).{8,32}$/;
  return regex.test(value)
    ? true
    : 'パスワードはアルファベット(半角英大文字、半角英小文字)、数字、記号記号!#%^&*-_の内、2種類以上の複合で作成してください';
});
