<template>
  <section class="testimonial-section no-global-reveal" ref="sectionRef">
    
    <div class="container section-header">
      <div class="title-wrapper">
        <h2 class="title">Ce qu'ils disent de nous</h2>
        <p class="subtitle">Découvrez les retours d'expérience de nos apprenants.</p>
      </div>
      
      <div class="navigation-buttons">
          <button class="nav-arrow left-arrow" @click="scrollCarrousel(-1)">
            <svg viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" class="arrow-icon carrousel-arrow left">
              <polyline points="7 14 14 7 7 0"></polyline>
              <line x1="14" y1="7" x2="0" y2="7"></line>
            </svg>
          </button>

          <button class="nav-arrow right-arrow" @click="scrollCarrousel(1)">
            <svg viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" class="arrow-icon carrousel-arrow right">
              <polyline points="7 14 14 7 7 0"></polyline>
              <line x1="14" y1="7" x2="0" y2="7"></line>
            </svg>
          </button>
      </div>
    </div>

    <div 
        class="testimonials-grid" 
        ref="gridRef"
        @mousedown="handleDragStart"
        @mousemove="handleDragMove"
        @mouseup="handleDragEnd"
        @mouseleave="handleDragEnd"
    >
      <div class="testimonial-wrapper" v-for="(testimonial, index) in testimonials" :key="index">
         <TestimonialCard v-bind="testimonial" />
      </div>
    </div>

  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import TestimonialCard from '../ui/TestimonialCard.vue';

gsap.registerPlugin(ScrollTrigger);

const sectionRef = ref(null);
const gridRef = ref(null);
const scrollHintRef = ref(null);

const testimonials = [
  {
    name: "Rasoamanana Koloina",
    role: "Entrepreneuse",
    avatar: "/images/Avatar/téléchargement (11).jpg",
    text: "Une formation incroyable qui a totalement changé ma vision du business. Les outils donnés sont concrets et applicables immédiatement."
  },
  {
    name: "Jean-Pierre Dubois",
    role: "Consultant Senior",
    avatar: "/images/Avatar/Black boy posing _ Free Photo.jpg",
    text: "ShiftUp m'a permis de structurer mon offre et d'augmenter mes tarifs. L'aspect communautaire est aussi un gros plus."
  },
  {
    name: "Andrianina Toky",
    role: "Freelance Designer",
    avatar: "/images/Avatar/téléchargement (13).jpg",
    text: "Enfin une formation adaptée au contexte local tout en gardant des standards internationaux. Je recommande vivement !"
  },
  {
    name: "Marie Shelby",
    role: "Directrice Marketing",
    avatar: "/images/Avatar/téléchargement (14).jpg",
    text: "J'ai adoré l'approche participative. On ne fait pas qu'écouter, on pratique et on échange avec des experts passionnés."
  },
  {
    name: "Rakoto Faneva",
    role: "CEO Start-up",
    avatar: "/images/Avatar/téléchargement (15).jpg",
    text: "Le retour sur investissement a été immédiat. Les stratégies de vente enseignées sont redoutables."
  }
];

// --- Logique de Scroll & Drag (Identique à LiveTrainings) ---
const isDragging = ref(false);
const startX = ref(0);
const scrollStartX = ref(0);

const scrollCarrousel = (direction) => {
  if (!gridRef.value) return;
  const cardWidth = 400; // Largeur approx d'une carte + gap
  const scrollDistance = cardWidth;
  gsap.to(gridRef.value, {
    scrollLeft: gridRef.value.scrollLeft + (direction * scrollDistance),
    duration: 0.6,
    ease: 'power2.out',
  });
};


const handleDragStart = (e) => {
  isDragging.value = true;
  startX.value = e.pageX - gridRef.value.offsetLeft;
  scrollStartX.value = gridRef.value.scrollLeft;
  gridRef.value.style.cursor = 'grabbing';
};

const handleDragMove = (e) => {
  if (!isDragging.value) return;
  e.preventDefault();
  const x = e.pageX - gridRef.value.offsetLeft;
  const walk = (x - startX.value) * 1.5; 
  gridRef.value.scrollLeft = scrollStartX.value - walk;
};

const handleDragEnd = () => {
  isDragging.value = false;
  if(gridRef.value) gridRef.value.style.cursor = 'grab';
};

onMounted(() => {
    // Animation d'apparition simple
    gsap.from(gridRef.value.children, {
        opacity: 0,
        y: 30,
        duration: 0.8,
        stagger: 0.1,
        scrollTrigger: {
            trigger: sectionRef.value,
            start: "top 80%"
        }
    });

    // Animation de l'indicateur de scroll
    if (scrollHintRef.value) {
        gsap.to(scrollHintRef.value.querySelector('.circle-icon'), {
            x: 10,
            duration: 1,
            yoyo: true,
            repeat: -1,
            ease: "power1.inOut"
        });
    }
});
</script>

<style scoped>
.testimonial-section {
  background-color: #050505;
  padding: 20vh 0;
  padding-top: 0;
  color: white;
  position: relative;
  overflow: hidden;
}

.container {
  max-width: 90%;
  margin: 0 auto 40px auto;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0;
}

.subtitle {
  font-size: 1.1rem;
  color: #aaa;
  margin: 0;
}

.navigation-buttons {
  display: flex;
  gap: 15px;
}

.nav-arrow {
  background-color: #111111;
  color: white;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  border: 0.5px solid #656565;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.nav-arrow .left-arrow
{
  transform: rotate(180deg);
}

.left-arrow {
  transform: rotate(180deg);
}

.nav-arrow:hover {
  background-color: #fff;
  color: #000;
  border-color: #fff;
}

.testimonials-grid {
  display: flex;
  gap: 20px;
  overflow-x: auto;
  padding: 0 5% 50px 5%;
  scrollbar-width: none;
  cursor: grab;
  padding-bottom: 0;
  -ms-overflow-style: none; 
}
.testimonials-grid::-webkit-scrollbar { display: none; }

.testimonial-wrapper {
  min-width: 400px;
  flex: 0 0 auto;
}

.circle-icon {
    display: flex;
    align-items: center;
}

@media (max-width: 768px) {
    .testimonial-wrapper {
        min-width: 85vw;
    }
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
    .title {
        font-size: 2rem;
    }
}
</style>
