<template lang="pug">
div
  .container-xl
    .page-header.d-print-none
      .row.g-2.align-items-center
        .col
          h2.page-title Feeders
        // Page title actions
  .page-body
    .container-xl
      .row.row-cards
        .col-6.col-lg-4(v-for="device in current_devices")
          .card
            .card-header
              h2.m-0(v-if="!device.edit")
                a(href='#' @click.prevent="toggleEdit(device.id)") {{device.name ? device.name : 'No name' }}
              template(v-if="device.edit")
                .input-group
                  input.form-control(type='text', v-model="device.name")
                  a.btn(type='button', @click.prevent="saveName(device.id)") Save
              .card-actions
                .dropdown
                  a.btn-action.dropdown-toggle(href='#' data-bs-toggle='dropdown')
                    font-awesome-icon(icon="ellipsis-vertical")
                  .dropdown-menu.dropdown-menu-end(style='')
                    a.dropdown-item(href='#' @click.prevent="identify(device.id)")
                      font-awesome-icon(icon="question", size="sm")
                      span.ms-2 Identify
                    a.dropdown-item(href='#' @click.prevent="replaceDrier(device.id)")
                      font-awesome-icon(icon="rotate", size="sm")
                      span.ms-2 Reset drier
                    a.dropdown-item(href='#' @click.prevent="removeDevice(device.id)")
                      font-awesome-icon(icon="rotate", size="sm")
                      span.ms-2 Remove device
            .card-body
              .d-flex.flex-column
                template(v-if="isBeforeNow(device.drier_replaced_at)")
                  .alert.alert-danger(role='alert') Replace drier
                .d-flex.justify-content-center
                  img.img-feeder(src="../../images/H57d2de3cb6d14892ad3ea29bbcd3c698o.jpeg")
                h4.m-0.text-center {{device.mac_address}}
                h4.m-0.text-center {{device.ip_address}}
            .d-flex.flex-column
              Link.card-btn.border-start-0.border-end-0(:href="route('feeders.schedule.show', {feeder: device.id})")
                font-awesome-icon(icon="calendar-day", size="sm")
                span.ms-2 Schedule
              a.card-btn.border-start-0.border-end-0(href='#' @click.prevent="dispense(device.id)")
                font-awesome-icon(icon="play", size="sm")
                span.ms-2 Dispense
</template>

<script>
import find from 'lodash/find';
import forEach from 'lodash/forEach';
import indexOf from 'lodash/indexOf';
import dayjs from 'dayjs';
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter';
import { Dropdown } from 'bootstrap';
import BaseLayout from '@/Layouts/BaseLayout';

dayjs.extend(isSameOrAfter);

export default {
  layout: BaseLayout,
  props: {
    devices: {
      type: Array,
      default: [],
    },
  },
  data() {
    return {
      current_devices: [],
      mounted: false,
    };
  },
  mounted() {
    this.reloadDevices();
  },
  methods: {
    reloadDevices() {
      const _devices = [];
      forEach(this.devices, (device) => {
        device.edit = false;
        _devices.push(device);
      });
      this.current_devices = _devices;

      this.$nextTick(() => {
        const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
        [...dropdownElementList].map((dropdownToggleEl) => new Dropdown(dropdownToggleEl));
        this.mounted = true;
      });
    },
    isBeforeNow(datetime) {
      return dayjs().isSameOrAfter(dayjs(datetime).add(30, 'days'), 'millisecond');
    },
    saveName(deviceId) {
      const device = find(this.current_devices, ['id', deviceId]);
      const index = indexOf(this.current_devices, device);
      device.edit = false;
      const _devices = this.current_devices;
      _devices[index] = device;
      this.current_devices = _devices;
      this.$inertia.post(this.route('feeders.name', {
        feeder: deviceId,
      }), {
        name: device.name,
      });
    },
    toggleEdit(deviceId) {
      const device = find(this.current_devices, ['id', deviceId]);
      const index = indexOf(this.current_devices, device);
      device.edit = true;
      const _devices = this.current_devices;
      _devices[index] = device;
      this.current_devices = _devices;
    },
    dispense(feeder) {
      this.$inertia.post(this.route('feeders.dispense.feeder', {
        feeder,
      }), {preserveState: false});
    },
    identify(feeder) {
      this.$inertia.post(this.route('feeders.identify', {
        feeder,
      }), {preserveState: false});
    },
    replaceDrier(feeder) {
      if (confirm('Are you sure you want to reset drier status?')) {
        this.$inertia.post(this.route('feeders.replace_drier', {
          feeder,
        }), {
          preserveState: false,
        });
        window.location.reload();
      }
    },
    removeDevice(feeder) {
      if (confirm('Are you sure you want to remove this device?')) {
        this.$inertia.delete(this.route('feeders.destroy', {
          feeder,
        }), {preserveState: false});
      }
    },
  },
};
</script>

<style scoped>
.img-feeder {
  max-width: 50%;
  height: auto;
}
</style>
