<template lang="pug">
div
  .container-xl
    .page-header.d-print-none
      .row.g-2.align-items-center
        .col
          h2.page-title Create New Group
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
                template(v-for="(feeder, index) in feeders")
                  label.form-check.mb-2
                    input.form-check-input(type='checkbox', v-model="selectedFeeders", :value="feeder.id"  :class="{'is-invalid': errors.feeders }")
                    span.form-check-label {{feeder.name ? feeder.name : feeder.mac_adderss}}
                    template(v-if="index + 1 === feeders.length" )
                      .invalid-feedback(v-if="errors.feeders") {{ errors.feeders }}
              .mt-2
                a.btn.btn-primary.w-100(href='#' @click.prevent="create")  Create

</template>

<script>
import BaseLayout from '@/Layouts/BaseLayout';

export default {
  layout: BaseLayout,
  props: {
    errors: Object,
    feeders: {
      type: Array,
      default: [],
    },
  },
  data() {
    return {
      selectedFeeders: [],
      name: null,
    };
  },
  methods: {
    create() {
      this.$inertia.post(this.route('groups.store'), {
        name: this.name,
        feeders: this.selectedFeeders,
      });
    },
  },
};
</script>

<style scoped>

</style>
