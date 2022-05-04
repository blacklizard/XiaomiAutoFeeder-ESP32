<template lang="pug">
form.p-3.row.g-3(@submit.prevent="$emit('submitForm', form.time, form.unit)" autocomplete="off")
  .col-md-6
    label.form-label(for='time') Time
    input#time.form-control(type='time', v-model="form.time" :class="{'is-invalid': errors.time}")
    .invalid-feedback {{errors.time}}
  .col-md-6
    label.form-label(for='unit') Unit
    input#unit.form-control(type='number' v-model="form.unit" :class="{'is-invalid': errors.unit}")
    .invalid-feedback {{errors.unit}}
  .col-12
    .d-flex.justify-content-end
      .btn-group
        a.btn.btn-red(@click.prevent="closeForm") Cancel
        button.btn.btn-green(type='submit') Save
</template>

<script>
export default {
  name: 'AddScheduleForm',
  props: {
    errors: Object,
  },
  data() {
    return {
      form: {
        time: null,
        unit: null,
      },
    };
  },
  emits: ['closeForm', 'submitForm'],
  methods: {
    closeForm() {
      this.resetForm();
      this.$emit('closeForm');
    },
    resetForm() {
      for (const key in this.form) {
        this.form[key] = null;
      }
    },
  },
};
</script>

<style scoped>

</style>
