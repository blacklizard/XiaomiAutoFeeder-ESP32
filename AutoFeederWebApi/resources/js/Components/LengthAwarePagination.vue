<template lang="pug">
template(v-if="links !== undefined && links.length > 0")
  nav(aria-label='Page navigation')
    ul.pagination
      template(v-for="(link, key) in links")
        li.page-item(:class="{ active: link.active, 'disabled': link.url === null }")
          a.page-link(v-if='link.url === null' v-html='link.label' tabindex='-1')
          Link.page-link(v-else='' :href='link.url' v-html='link.label')
</template>

<script>
export default {
  props: {
    links: Array,
  },
};
</script>

<style scoped>

</style>
