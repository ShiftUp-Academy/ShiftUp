<template>
  <div class="nav-bar-container">
    <div class="nav-links" ref="navLinks">
      <Link v-for="item in menuItems" :key="item.name" :href="item.route" class="nav-item"
        :class="{ active: page?.url?.startsWith(item.route) }">
        {{ item.label }}
      </Link>
      <div class="sliding-line" ref="slidingLine"></div>
    </div>

    <a href="/" target="_blank" class="visit-site">
      VISITER LE SITEWEB ↗
    </a>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const slidingLine = ref(null);
const navLinks = ref(null);

const menuItems = computed(() => {
  const allItems = [
    { name: 'programmes', label: 'PROGRAMMES', route: '/admin/programmes' },
    { name: 'consultations', label: 'CONSULTATION', route: '/admin/consultations' },
    { name: 'lives', label: 'LIVES', route: '/admin/lives' },
    { name: 'coachings', label: 'COACHINGS', route: '/admin/coachings' },
    { name: 'offres', label: 'OFFRES', route: '/admin/offres' },
    { name: 'utilisateurs', label: 'UTILISATEURS', route: '/admin/utilisateurs' },
    { name: 'temoignages', label: 'TÉMOIGNAGE', route: '/admin/temoignages' },
  ];

  const user = page.props.auth.user;
  if (!user) return [];
  if (user.Role === 'admin') return allItems;

  if (user.Role === 'moderateur') {
    const permissions = user.PermissionsModerateur || [];
    // We use the 'name' to match permissions (e.g., 'programmes')
    return allItems.filter(item => permissions.includes(item.name));
  }

  return [];
});

const moveLine = (target) => {
  if (!target || !slidingLine.value) return;

  const parentRect = navLinks.value.getBoundingClientRect();
  const targetRect = target.getBoundingClientRect();

  const left = targetRect.left - parentRect.left;
  const width = targetRect.width;

  slidingLine.value.style.width = `${width}px`;
  slidingLine.value.style.transform = `translateX(${left}px)`;
  slidingLine.value.style.opacity = '1';
};

const resetLine = () => {
  const activeLink = navLinks.value?.querySelector('.nav-item.active');
  if (activeLink) {
    moveLine(activeLink);
  } else {
    if (slidingLine.value) slidingLine.value.style.opacity = '0';
  }
};

onMounted(() => {
  nextTick(() => {
    resetLine();
  });
});

watch(() => page.url, () => {
  nextTick(() => {
    resetLine();
  });
});
</script>

<style scoped>
.nav-bar-container {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  position: relative;
}

.nav-links {
  display: flex;
  gap: 4vw;
  position: relative;
  margin-left: 2vw;
  padding-bottom: 1.7vh;
}

.nav-item {
  text-decoration: none;
  color: #313131;
  font-size: 1rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 5px 0;
  transition: all 0.3s;
  position: relative;
}

.nav-item:hover,
.nav-item.active {
  color: #000;
  font-weight: 700;
}

.sliding-line {
  position: absolute;
  bottom: 0px;
  left: 0;
  height: 3px;
  background: #000;
  transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
  will-change: width, transform;
  pointer-events: none;
}

.visit-site {
  text-decoration: none;
  font-size: 1rem;
  font-weight: 500;
  color: #000;
  text-transform: uppercase;
  padding-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: gap 0.3s;
}

.visit-site:hover {
  gap: 12px;
}
</style>
