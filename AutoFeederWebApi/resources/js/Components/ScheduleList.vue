<template lang="pug">
.list-group.list-group-flush.list-group-hoverable(v-for="schedule in schedules" :key="schedule.id")
  .list-group-item
    .row.align-items-center
      .col-auto
        span.badge.bg-green
      .col
        .d-block {{schedule.time}}
        .d-block {{schedule.unit}} {{schedule.unit === 1 ? 'unit' : 'units'}}
      .col-auto
        a(@click.prevent="edit(schedule.id)")
          font-awesome-icon(icon="pen", size="sm")
      .col-auto
        a(@click.prevent="$emit('remove', schedule.id)")
          font-awesome-icon(icon="trash-can", size="sm")
      .col-auto
        .form-check.form-switch
          input.form-check-input(type='checkbox' :checked="schedule.enable" @change="$emit('toggleState', schedule.id)")
    .row.align-items-center(v-if="current_editing_schedule === schedule.id")
      form.row.g-3(autocomplete="off")
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
              button.btn.btn-green(@click.prevent="update") Update
</template>

<script>
import find from 'lodash/find';

export default {
  emits: ['remove', 'toggleState'],
  props: {
    errors: Object,
    route_group_name: String,
    route_key: String,
    resource_id: String,
    schedules: {
      type: Object,
      default: {},
    },
  },
  data() {
    return {
      current_editing_schedule: null,
      form: {
        time: null,
        unit: null,
      },
    };
  },
  methods: {
    edit(scheduleId) {
      this.current_editing_schedule = scheduleId;
      const schedule = find(this.schedules, ['id', scheduleId]);
      this.form.time = schedule.time;
      this.form.unit = schedule.unit;
    },
    closeForm() {
      this.current_editing_schedule = null;
      this.form.time = null;
      this.form.unit = null;
    },
    update() {
      this.$inertia.post(
        this.route(`${this.route_group_name}.schedule.update`, { [this.route_key]: this.resource_id, schedule: this.current_editing_schedule }),
        {
          time: this.form.time,
          unit: this.form.unit,
        },
        {
          onSuccess: () => {
            this.closeForm();
          },
        },
      );
    },
  },
};
</script>

<style scoped>
.form-switch {
  min-height: auto;
}
</style>
