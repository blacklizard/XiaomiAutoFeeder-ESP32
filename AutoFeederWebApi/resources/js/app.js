import { createApp, h } from 'vue';
import { createInertiaApp, Link } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';

/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core';

/* import specific icons */
import {
  faHouse,
  faQuestion,
  faCalendarDay,
  faPlay,
  faPlus,
  faRotate,
  faTrashCan,
  faEllipsisVertical,
  faLayerGroup,
  faPen,
} from '@fortawesome/free-solid-svg-icons';

/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

require('./bootstrap');

library.add([
  faHouse,
  faQuestion,
  faCalendarDay,
  faPlay,
  faPlus,
  faRotate,
  faTrashCan,
  faEllipsisVertical,
  faLayerGroup,
  faPen,
]);

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => require(`./Pages/${name}.vue`),
  setup({
    el, app, props, plugin,
  }) {
    return createApp({ render: () => h(app, props) })
      .use(plugin)
      .mixin({ methods: { route } })
      .component('font-awesome-icon', FontAwesomeIcon)
      .component('Link', Link)
      .mount(el);
  },
});

InertiaProgress.init({ color: '#4B5563' });
