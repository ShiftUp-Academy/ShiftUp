<template>
  <header class="admin-header">
    <AdminTopBar />
    <AdminNavBar />
  </header>
</template>

<script setup>
import AdminTopBar from './AdminTopBar.vue';
import AdminNavBar from './AdminNavBar.vue';
</script>

<style scoped>
.admin-header {
  background: white;
  padding: 30px 50px 0 50px;
  border-bottom: 1px solid #e0e0e0;
  position: sticky;
  top: 0;
  z-index: 100;
}
</style>
