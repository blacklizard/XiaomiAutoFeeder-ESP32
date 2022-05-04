<template lang="pug">
div
  .container-xl
    .page-header.d-print-none
      .row.g-2.align-items-center
        .col
          h2.page-title Edit Group - {{group.name}}
  .page-body
    .container-xl
      .row.row-cards
        .col-12.col-lg-6()
          .card
            .card-body
              .mb-3
                .form-label Name
                input.form-control(type='text', v-model="name" :class="{'is-invalid': errors.name }")
                .invalid-feedback(v-if="errors.name") {{ errors.name }}
              .mb-3
                label.form-label Feeders
                template(v-for="(feeder, index) in all_feeders")
                  label.form-check.mb-2
                    input.form-check-input(type='checkbox', v-model="selectedFeeders", :value="feeder.id"  :class="{'is-invalid': errors.feeders }")
                    span.form-check-label {{feeder.name ? feeder.name : feeder.mac_adderss}}
                    template(v-if="index + 1 === all_feeders.length" )
                      .invalid-feedback(v-if="errors.feeders") {{ errors.feeders }}
              .mt-2
                a.btn.btn-primary.w-100(href='#' @click.prevent="create")  Update

</template>

<script>
import map from 'lodash/map';
import BaseLayout from '@/Layouts/BaseLayout';

export default {
  layout: BaseLayout,
  props: {
    errors: Object,
    group: Object,
    feeders: {
      type: Array,
      default: [],
    },
    current_feeders: {
      type: Array,
      default: [],
    },
  },
  data() {
    return {
      all_feeders: [...this.current_feeders, ...this.feeders],
      selectedFeeders: [],
      name: this.group.name,
    };
  },
  mounted() {
    this.selectedFeeders = map(this.current_feeders, 'id');
  },
  methods: {
    create() {
      this.$inertia.patch(this.route('groups.update', { group: this.group.id }), {
        name: this.name,
        feeders: this.selectedFeeders,
      });
    },
  },
};
</script>

<style scoped>

</style>
