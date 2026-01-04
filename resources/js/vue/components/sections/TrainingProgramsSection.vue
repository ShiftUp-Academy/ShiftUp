<template>
  <section 
    class="training-programs-section" 
    ref="sectionRef"
    @mousemove="handleFlashMove"
    @mouseleave="handleFlashLeave"
    :style="{ '--mouse-x': flash.x, '--mouse-y': flash.y, '--flash-opacity': flash.opacity }"
  >
    <!-- Dynamic Spreading Fog Overlay -->
    <div class="spreading-fog" ref="fogRef"></div>
    
    <!-- Global Section Flashlight -->
    <div class="section-flashlight"></div>

    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Les listes des <br/>consultations</h2>
        
        <SectionFilters 
          v-model:searchValue="searchValue"
          v-model:selectedCategory="selectedCategory"
          :categories="categories"
          :suggestions="filteredSuggestions"
          @complete="searchSuggestions"
          @reset="resetFilters"
          @view-all="() => console.log('View all clicked')"
        />
      </div>
      
      <div class="programs-grid" ref="gridRef">
        <ProgramCard 
          v-for="(program, index) in programs" 
          :key="index"
          v-bind="program"
        />
      </div>

      <div class="consultation-lists" ref="listsRef">
        <ConsultationSessionsList />
        <FreeConsultationsList />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, reactive } from 'vue';
import ProgramCard from '../ui/ProgramCard.vue';
import SectionFilters from '../ui/SectionFilters.vue';
import ConsultationSessionsList from '../ui/ConsultationSessionsList.vue';
import FreeConsultationsList from '../ui/FreeConsultationsList.vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const sectionRef = ref(null);
const fogRef = ref(null); 
const gridRef = ref(null);
const listsRef = ref(null);

const flash = reactive({ x: '50%', y: '50%', opacity: 0 });

function handleFlashMove(event) {
  const rect = event.currentTarget.getBoundingClientRect();
  flash.x = `${(event.clientX - rect.left)}px`;
  flash.y = `${(event.clientY - rect.top)}px`;
  flash.opacity = 1;
}

function handleFlashLeave() {
  flash.opacity = 0;
}

const searchValue = ref('');
const selectedCategory = ref(null);
const filteredSuggestions = ref([]);

const suggestions = [
  'Formation Montage Vidéo',
  'Formation Stratégie Marketing',
  'Étude de Marché Express',
  'Formation Design Graphique',
  'Formation Développement Web'
];

const categories = [
  { name: 'marketing', code: 'MKT' },
  { name: 'design', code: 'DSG' },
  { name: 'développement', code: 'DEV' }
];

const searchSuggestions = (event) => {
  const query = event.query.toLowerCase();
  filteredSuggestions.value = suggestions.filter(item => 
    item.toLowerCase().includes(query)
  );
};

const resetFilters = () => {
  searchValue.value = '';
  selectedCategory.value = null;
};

const programs = [
  {
    title: 'FORMATION MONTAGE VIDÉO',
    image: '/images/Programmes/Plan de travail1.png',
    price: '190k Ar'
  },
  {
    title: 'FORMATION STRATÉGIE MARKETING',
    image: '/images/Programmes/Plan de travail2.png',
    price: '100k Ar'
  },
  {
    title: 'ÉTUDE DE MARCHÉ EXPRESS',
    image: '/images/Programmes/Plan de travail3.png',
    price: '150k Ar'
  },
  {
    title: 'FORMATION MONTAGE VIDÉO',
    image: '/images/Programmes/Plan de travail4.png',
    price: '190k Ar'
  },
  {
    title: 'FORMATION STRATÉGIE MARKETING',
    image: '/images/Programmes/Plan de travail5.png',
    price: '190k Ar'
  },
  {
    title: 'ÉTUDE DE MARCHÉ EXPRESS',
    image: '/images/Programmes/Plan de travail6.png',
    price: '190k Ar'
  }
];

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
  z-index: 1; /* Below content, above background */
  background: radial-gradient(
    600px circle at var(--mouse-x) var(--mouse-y), 
    rgba(255, 255, 255, 0.05), 
    transparent 80%
  );
  opacity: var(--flash-opacity, 0);
  transition: opacity 0.5s ease;
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
    #050505 100%
  );
  pointer-events: none;
  z-index: -1;
  transform: translateY(0);
  margin-top: -30vh;
}

.container {
  max-width: 90%; 
  margin: 0 auto;
  position: relative;
  z-index: 2; /* Content above fog */
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center; /* Changed from flex-end to center for better alignment with the new button */
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

.consultation-lists {
  margin-top: 80px;
  display: flex;
  flex-direction: column;
  gap: 60px;
}

/* Exclusive hover effect - triggered specifically by photo hover */
.programs-grid:has(.image-container:hover) .program-card:not(:has(.image-container:hover)) {
  filter: opacity(0.3) grayscale(0.5) brightness(0.6) sepia(0.2) hue-rotate(220deg) blur(1px);
}

.programs-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px; 
  margin-bottom: 60px;
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
