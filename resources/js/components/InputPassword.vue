<template>
<!--  @keyup="errors.current_password=''"-->
  <input type="password"
         :class="field.class"
         :id="field.id"
         :placeholder="field.placeholder"
         :name="field.name"
         @input="$emit('update:modelValue', $event.target.value)" />
</template>

<script>
export default {
  data() {
    return {
      data: '',
    };
  },
  props: {
    field: {},
    modelValue: {
      type: String,
      default: '',
    },
  },

};
</script>
