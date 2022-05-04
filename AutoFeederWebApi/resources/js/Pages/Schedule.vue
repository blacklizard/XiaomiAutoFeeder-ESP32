<template lang="pug">
div
  .container-xl
    .page-header.d-print-none
      .row.g-2.align-items-center
        .col
          h2.page-title Schedule - {{device.name ? device.name : device.mac_address}}
  .page-body
    .container-xl
      .row.row-cards
        .col-12.col-md-6
          .card
            .card-header
              h3.card-title Schedule list
              .card-actions
                .btn-group
                  a.btn.btn-primary(href='#' @click.prevent="showCreateForm")
                    font-awesome-icon(icon="plus", size="sm")
                    .ms-2 Add new
                  template(v-if="!device.schedule_synced")
                    a.btn.btn-green(href='#' @click.prevent="syncSchedule")
                      font-awesome-icon(icon="rotate", size="sm")
                      .ms-2 Sync
            AddScheduleForm(v-if="showForm", @closeForm="closeForm", @submitForm="submit", :errors="errors")
            ScheduleList(
              @remove="remove"
              @toggleState="toggleState"
              :schedules="device.schedules"
              :errors="errors",
              :route_group_name="route_group_name"
              :route_key="route_key",
              :resource_id="device.id"
            )

</template>

<script>
import scheduleMixin from '@/Mixins/scheduleMixin';
import BaseLayout from '@/Layouts/BaseLayout';

export default {
  layout: BaseLayout,
  mixins: [scheduleMixin],
  props: {
    device: {
      type: Object,
      default: {},
    },
  },
  data() {
    return {
      route_group_name: 'feeders',
      route_key: 'feeder',
      resource_key: 'device',
    };
  },
};
</script>

<style scoped></style>
