<template>
  <section class="resources-section no-global-reveal" ref="sectionRef" @mousemove="handleFlashMove" @mouseleave="handleFlashLeave">
    <div class="spreading-fog" ref="fogRef"></div>

    <div class="section-flashlight"></div>

    <div class="container">
      <div class="section-header">
        <h2 class="section-title">{{ $t('RessourceGratuites.nos_ressources') }} <br />{{
          $t('RessourceGratuites.gratuites') }}</h2>

        <SectionFilters v-model:searchValue="searchValue" v-model:selectedCategory="selectedCategory"
          :categories="categories" :suggestions="filteredSuggestions" @complete="searchSuggestions"
          @reset="resetFilters" @view-all="router.visit('/programmes')" />
      </div>

      <div :class="['resources-grid', { 'is-hovered': isGridHovered }]" ref="gridRef">
        <ResourceCard v-for="(resource, index) in displayResources" :key="index" v-bind="resource"
          @mouseenter="isGridHovered = true" @mouseleave="isGridHovered = false" />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, nextTick, reactive, computed } from 'vue';
import ResourceCard from '../ui/ResourceCard.vue';
import SectionFilters from '../ui/SectionFilters.vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { usePage, router } from '@inertiajs/vue3';

gsap.registerPlugin(ScrollTrigger);

const page = usePage();
const $t = (key) => page.props.translations?.[key] || key;

const sectionRef = ref(null);
const fogRef = ref(null);
const gridRef = ref(null);

const flashOpacity = ref(0);
let bounds = null;

function handleFlashMove(event) {
  if (!sectionRef.value) return;

  const rect = event.currentTarget.getBoundingClientRect();
  const x = event.clientX - rect.left;
  const y = event.clientY - rect.top;

  sectionRef.value.style.setProperty('--mouse-x', `${x}px`);
  sectionRef.value.style.setProperty('--mouse-y', `${y}px`);

  if (flashOpacity.value !== 1) {
    flashOpacity.value = 1;
    sectionRef.value.style.setProperty('--flash-opacity', 1);
  }
}

function handleFlashLeave() {
  flashOpacity.value = 0;
  if (sectionRef.value) {
    sectionRef.value.style.setProperty('--flash-opacity', 0);
  }
}

const searchValue = ref('');
const selectedCategory = ref(null);
const isGridHovered = ref(false);
const filteredSuggestions = ref([]);

const suggestions = computed(() => [
  $t('Suggestions.guide_marketing'),
  $t('Suggestions.template_montage'),
  $t('Suggestions.ebook_entrepreneuriat'),
  $t('Suggestions.checklist_seo'),
  $t('Suggestions.pack_icons')
]);

const categories = computed(() => {
  return props.categories.map(c => ({
    name: c.Nom,
    code: c.IdCategorie
  }));
});

const displayResources = computed(() => {
    return props.resources.filter(resource => {
        const title = resource.title || '';
        const search = searchValue.value.toLowerCase();
        const matchesSearch = title.toLowerCase().includes(search);
        const matchesCategory = !selectedCategory.value || resource.categoryId === selectedCategory.value.code;
        return matchesSearch && matchesCategory;
    });
});

const searchSuggestions = (event) => {
  const query = event.query.toLowerCase();
  filteredSuggestions.value = suggestions.value.filter(item =>
    item.toLowerCase().includes(query)
  );
};

const resetFilters = () => {
  searchValue.value = '';
  selectedCategory.value = null;
};

const props = defineProps({
  resources: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  }
});

onMounted(async () => {
  await nextTick();

  if (sectionRef.value && fogRef.value) {
    // 1. Spreading Fog Animation
    gsap.fromTo(fogRef.value,
      { y: '10%' },
      {
        y: '-30%',
        ease: "power2.inOut",
        scrollTrigger: {
          trigger: sectionRef.value,
          start: "top bottom",
          end: "top 10%",
          scrub: 1
        }
      }
    );

    // 2. Text Reveal Animation - REMOVED scroll effect as requested
  }
});
</script>

<style scoped>
.resources-section {
  position: relative;
  padding-top: 7vh;
  background-color: #050505;
  z-index: 10;
  color: #ffffff;
  overflow: hidden;
}

.section-flashlight {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 1;
  background: radial-gradient(600px circle at var(--mouse-x) var(--mouse-y),
      rgba(255, 255, 255, 0.05),
      transparent 80%);
  opacity: var(--flash-opacity, 0);
  transition: opacity 0.5s ease;
  will-change: transform, opacity;
  transform: translateZ(0);
  /* Force GPU */
}

.spreading-fog {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 120vh;
  background: linear-gradient(to bottom,
      transparent 0%,
      rgba(0, 0, 0, 0.2) 40%,
      #050505 100%);
  /* Simplified gradient */
  pointer-events: none;
  z-index: -1;
  transform: translateY(0) translateZ(0);
  margin-top: -30vh;
  will-change: transform;
}

.container {
  max-width: 90%;
  margin: 0 auto;
  position: relative;
  z-index: 2;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6vh;
  gap: 3vw;
}

.section-title {
  font-size: 2.5vw;
  font-weight: 600;
  color: #ffffff;
  line-height: 1;
  margin: 0;
}

.resources-grid.is-hovered .program-card:not(:hover) {
  filter: grayscale(0.5) sepia(0.2) hue-rotate(220deg) brightness(0.8) blur(1px);
  opacity: 0.6;
}

.resources-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 60px;
}

@media (max-width: 1280px) {
  .resources-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 1024px) {
  .resources-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .section-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .section-title {
    font-size: 1.8rem;
    margin-bottom: 2vh;
  }

  .resources-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }
}
</style>
