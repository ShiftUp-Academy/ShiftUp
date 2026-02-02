<template>
  <section class="training-programs-section" ref="sectionRef" @mousemove="handleFlashMove"
    @mouseleave="handleFlashLeave">
    <div class="spreading-fog" ref="fogRef"></div>

    <div class="section-flashlight"></div>

    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Les listes des <br />Programmes de formation</h2>

        <SectionFilters v-model:searchValue="searchValue" v-model:selectedCategory="selectedCategory"
          :categories="categories" :suggestions="filteredSuggestions" @complete="searchSuggestions"
          @reset="resetFilters" @view-all="() => console.log('View all clicked')" />
      </div>

      <div class="programs-grid" ref="gridRef">
        <ProgramCard v-for="(program, index) in displayPrograms" :key="index" v-bind="program" />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, reactive, computed } from 'vue';
import ProgramCard from '../ui/ProgramCard.vue';
import SectionFilters from '../ui/SectionFilters.vue';

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const sectionRef = ref(null);
const fogRef = ref(null);
const gridRef = ref(null);


const flashOpacity = ref(0);

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
const filteredSuggestions = ref([]);

const displayPrograms = computed(() => {
  return props.programs.filter(program => {
    const isNotSeminaire = program.type !== 'Seminaire';
    const matchesSearch = program.title.toLowerCase().includes(searchValue.value.toLowerCase());
    const matchesCategory = !selectedCategory.value || program.categoryId === selectedCategory.value.code;
    return isNotSeminaire && matchesSearch && matchesCategory;
  });
});

const categories = computed(() => {
  return props.categories.map(c => ({
    name: c.NomCategorie,
    code: c.IdCategorie
  }));
});

const searchSuggestions = (event) => {
  const query = event.query.toLowerCase();
  const allTitles = props.programs.map(p => p.title);
  filteredSuggestions.value = allTitles.filter(item =>
    item.toLowerCase().includes(query)
  );
};

const resetFilters = () => {
  searchValue.value = '';
  selectedCategory.value = null;
};

const props = defineProps({
  programs: {
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

    // 2. Text Reveal Animation 
    gsap.from([
      sectionRef.value.querySelectorAll('.section-title'),
      sectionRef.value.querySelectorAll('.filters-container')
    ], {
      y: 40,
      opacity: 0,
      duration: 1,
      stagger: 0.1,
      ease: "power3.out",
      scrollTrigger: {
        trigger: sectionRef.value,
        start: "top 70%",
      }
    });
  }
});
</script>

<style scoped>
.training-programs-section {
  position: relative;
  padding: 80px 0;
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
  will-change: opacity, transform;
  /* Hardware acceleration */
}

.spreading-fog {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 120vh;
  background: linear-gradient(to bottom,
      transparent 0%,
      rgba(0, 0, 0, 0.05) 15%,
      rgba(0, 0, 0, 0.2) 35%,
      rgba(0, 0, 0, 0.5) 55%,
      rgba(0, 0, 0, 0.8) 80%,
      #050505 100%);
  pointer-events: none;
  z-index: -1;
  transform: translateY(0);
  margin-top: -30vh;
}

.container {
  max-width: 90%;
  margin: 0 auto;
  position: relative;
  z-index: 2;
  /* Content above fog */
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  /* Changed from flex-end to center for better alignment with the new button */
  margin-bottom: 6vh;
  gap: 3vw;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 600;
  color: #ffffff;
  line-height: 1;
  margin: 0;
}

.programs-grid:has(.image-container:hover) .program-card:not(:has(.image-container:hover)) {
  filter: opacity(0.7) grayscale(0.5) brightness(0.9) sepia(0.2) hue-rotate(220deg) blur(1px);
}

.programs-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

@media (max-width: 1280px) {
  .programs-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 1024px) {
  .programs-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .section-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .programs-grid {
    grid-template-columns: 1fr;
    gap: 32px;
  }
}
</style>
