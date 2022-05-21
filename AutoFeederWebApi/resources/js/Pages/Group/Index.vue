<template lang="pug">
div
  .container-xl
    .page-header.d-print-none
      .row.g-2.align-items-center
        .col
          h2.page-title Groups
        .col-auto.ms-auto
          a.btn.btn-primary(:href="route('groups.create')")
            font-awesome-icon(icon="add", size="sm")
            .ms-2 Create new group
  .page-body
    .container-xl
      .row.row-cards
        .col-6.col-lg-4(v-for="group in groups")
          .card
            .card-header
              h2.m-0 {{ group.name }}
              .card-actions
                .dropdown
                  a.btn-action.dropdown-toggle(href='#' data-bs-toggle='dropdown')
                    font-awesome-icon(icon="ellipsis-vertical")
                  .dropdown-menu.dropdown-menu-end(style='')
                    a.dropdown-item(:href="route('groups.edit', {group: group.id})")
                      font-awesome-icon(icon="pen", size="sm")
                      span.ms-2 Edit
            .card-body
              ul(v-for="feeder in group.feeders")
                li {{ feeder.name ? feeder.name : feeder.mac_address }}
            .d-flex.flex-column
              Link.card-btn.border-start-0.border-end-0(:href="route('groups.schedule.show', {group: group.id})")
                font-awesome-icon(icon="calendar-day", size="sm")
                span.ms-2 Schedule
              a.card-btn.border-start-0.border-end-0(href='#' @click.prevent="dispense(group.id)")
                font-awesome-icon(icon="play", size="sm")
                span.ms-2 Dispense
</template>

<script>
import BaseLayout from '@/Layouts/BaseLayout';
import { Dropdown } from 'bootstrap';

export default {
  layout: BaseLayout,
  props: {
    errors: Object,
    groups: {
      type: Array,
      default: [],
    },
  },
  data() {
    return {

    };
  },
  mounted() {
    this.$nextTick(() => {
      const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
      [...dropdownElementList].map((dropdownToggleEl) => new Dropdown(dropdownToggleEl));
    });
  },
  methods: {
    dispense(group) {
      this.$inertia.post(this.route('groups.dispense.feeder', {
        group,
      }), {});
    },
  },
};
</script>

<style scoped>

</style>
