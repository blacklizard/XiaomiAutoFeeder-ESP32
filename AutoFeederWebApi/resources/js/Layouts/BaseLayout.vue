<template lang="pug">
div
  .theme-dark
    .page
      header.navbar.navbar-expand.navbar-light.d-print-none.d-none.d-md-block
        .container-xl
          button.navbar-toggler(type='button' data-bs-toggle='collapse' data-bs-target='#navbar-menu')
            span.navbar-toggler-icon
          h1.navbar-brand.navbar-brand-autodark.d-none-navbar-horizontal.pe-0.pe-md-3
            a.navbar-brand(href='#') Auto Feeder
      .navbar-expand.feeder-nav
        #navbar-menu
          .navbar.navbar-light
            .container-xl
              ul.navbar-nav
                li.nav-item(:class="route().current('feeders.*') ? 'active' : ''")
                  Link.nav-link(href="/")
                    font-awesome-icon(icon="house", size="sm")
                    span.nav-link-title.ms-2 Feeders
                li.nav-item(:class="route().current('groups.*') ? 'active' : ''")
                  Link.nav-link(href="/groups")
                    font-awesome-icon(icon="layer-group", size="sm")
                    span.nav-link-title.ms-2 Groups
                li.nav-item(:class="route().current('announcements') ? 'active' : ''")
                  Link.nav-link(href="/announcements")
                    font-awesome-icon(icon="bullhorn", size="sm")
                    span.nav-link-title.ms-2 Announcements
      .page-wrapper
        slot
</template>

<script>
// import {Collapse} from 'bootstrap';

export default {
  mounted() {
    // this.$nextTick(() => {
    //   const collapseElementList = [].slice.call(document.querySelectorAll('.collapse'));
    //   collapseElementList.map((collapseEl) => new Collapse(collapseEl, {
    //     toggle: false,
    //   }));
    // });
  },
};
</script>

<style scoped>
@media (max-width: 767.98px) {
  .feeder-nav{
    position: fixed;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1030;
  }
}

</style>
