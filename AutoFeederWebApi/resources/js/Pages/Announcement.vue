<template lang="pug">
div
  .container-xl
    .page-header.d-print-none
      .row.g-2.align-items-center
        .col
          h2.page-title Announcements
        // Page title actions
  .page-body
    .container-xl
      LengthAwarePagination.mb-3(:links="announcements.meta.links")
      .row.row-cards
        .card
          .card-body
            .divide-y
              div(v-for="announcement in announcements.data")
                .row
                  .col
                    .text-truncate {{announcement.feeder}} - {{announcement.type}}
                    .text-muted {{dayjs(announcement.at).fromNow()}}
      LengthAwarePagination.mt-3(:links="announcements.meta.links")
</template>

<script>
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import BaseLayout from '@/Layouts/BaseLayout';
import LengthAwarePagination from '@/Components/LengthAwarePagination';

dayjs.extend(relativeTime);

export default {
  layout: BaseLayout,
  props: {
    announcements: Object,
  },
  components: {
    LengthAwarePagination,
  },
  methods: {
    dayjs,
  },
};
</script>

<style scoped>

</style>
