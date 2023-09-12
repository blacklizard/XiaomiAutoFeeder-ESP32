<template lang="pug">
div
  .container-xl
    .page-header.d-print-none
      .row.g-2.align-items-center
        .col
          h2.page-title Statistic
        // Page title actions
  .page-body
    .container-xl
      .row.row-cards
        .card
          .card-body
            Bar(:data="chartData")
</template>

<script>
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import BaseLayout from '@/Layouts/BaseLayout';
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)


dayjs.extend(relativeTime);

export default {
  layout: BaseLayout,
  components: { Bar },
  props: {
    statistics: Object,
  },
  data() {
    console.log(this.statistics);
    return {
      chartData: {
        labels: this.statistics.data.map((stat) => {
          return stat.feeder
        }),
        datasets: [
          {
            label: 'Total Unit',
            backgroundColor: '#f87979',
            data: this.statistics.data.map((stat) => {
              return stat.total
            })
          }
        ]
      }
    }
  },
  methods: {
    dayjs,
  },
};
</script>

<style scoped>

</style>
