<template>
  <section 
    class="resources-section" 
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
        <h2 class="section-title">Nos ressources <br/>gratuites</h2>
        
        <SectionFilters 
          v-model:searchValue="searchValue"
          v-model:selectedCategory="selectedCategory"
          :categories="categories"
          :suggestions="filteredSuggestions"
          @complete="searchSuggestions"
          @reset="resetFilters"
        />
      </div>
      
      <div class="resources-grid" ref="gridRef">
        <ResourceCard 
          v-for="(resource, index) in resources" 
          :key="index"
          v-bind="resource"
        />
      </div>
    </div>
  </section>
</template>
 
<script setup>
import { ref, onMounted, nextTick, reactive } from 'vue';
import ResourceCard from '../ui/ResourceCard.vue';
import SectionFilters from '../ui/SectionFilters.vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
 
gsap.registerPlugin(ScrollTrigger);
 
const sectionRef = ref(null);
const fogRef = ref(null); 
const gridRef = ref(null);
 
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
  'Guide Marketing Digital',
  'Template Montage CapCut',
  'Ebook Entrepreneuriat',
  'Checklist SEO 2025',
  'Pack Icons Business'
];
 
const categories = [
  { name: 'guides', code: 'GUI' },
  { name: 'templates', code: 'TPL' },
  { name: 'outils', code: 'TL' }
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
 
const resources = [
  {
    title: 'GUIDE COMPLET MARKETING',
    image: '/images/Programmes/Plan de travail1.png'
  },
  {
    title: 'TEMPLATE MONTAGE VIDÉO',
    image: '/images/Programmes/Plan de travail2.png'
  },
  {
    title: 'CHECKLIST E-COMMERCE',
    image: '/images/Programmes/Plan de travail3.png'
  },
  {
    title: 'EBOOK LIBERTÉ FINANCIÈRE',
    image: '/images/Programmes/Plan de travail4.png'
  },
  {
    title: 'PACK RÉSEAUX SOCIAUX',
    image: '/images/Programmes/Plan de travail5.png'
  },
  {
    title: 'PLAN D\'ACTION BUSINESS',
    image: '/images/Programmes/Plan de travail6.png'
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
  font-size: 2.5rem;
  font-weight: 600;
  color: #ffffff;
  line-height: 1;
  margin: 0;
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
  .resources-grid {
    grid-template-columns: 1fr;
    gap: 32px;
  }
}
</style>
