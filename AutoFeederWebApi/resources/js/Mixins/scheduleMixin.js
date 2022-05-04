import ScheduleList from '@/Components/ScheduleList';
import AddScheduleForm from '@/Components/AddScheduleForm';

export default {
  components: {
    ScheduleList,
    AddScheduleForm,
  },
  props: {
    errors: Object,
  },
  data() {
    return {
      showForm: false,
    };
  },
  methods: {
    showCreateForm() {
      this.showForm = true;
    },
    closeForm() {
      this.showForm = false;
    },
    submit(time, unit) {
      this.$inertia.post(
        this.route(`${this.route_group_name}.schedule.add`, { [this.route_key]: this[this.resource_key].id }),
        {
          time,
          unit,
        },
        {
          onSuccess: () => {
            this.closeForm();
          },
        },
      );
    },
    toggleState(scheduleId) {
      this.$inertia.post(
        this.route(`${this.route_group_name}.schedule.toggle`, { [this.route_key]: this[this.resource_key].id, schedule: scheduleId }),
        {},
      );
    },
    syncSchedule() {
      this.$inertia.post(
        this.route(`${this.route_group_name}.schedule.sync`, { [this.route_key]: this[this.resource_key].id }),
        {},
      );
    },
    remove(scheduleId) {
      this.$inertia.post(
        this.route(`${this.route_group_name}.schedule.remove`, { [this.route_key]: this[this.resource_key].id, schedule: scheduleId }),
        {},
      );
    },
  },
};
