<template>
  <section id="live-trainings" ref="sectionRef" class="live-trainings-section no-global-reveal">
    <div class="scroll-hint-wrapper" ref="scrollHintRef">
      <div class="scroll-hint">
        <span class="scroll-text">{{ $t('LiveTrainings.FaitesDefiler') }}</span>
        <div class="icon-group">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" class="mouse-icon">
            <rect x="2" y="2" width="20" height="20" rx="10" ry="10"></rect>
            <line x1="12" y1="6" x2="12" y2="10"></line>
          </svg>
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" class="arrow-down-icon">
            <polyline points="7 13 12 18 17 13"></polyline>
            <polyline points="7 6 12 11 17 6"></polyline>
          </svg>
        </div>
      </div>
    </div>

    <div class="container two-column-layout">

      <div class="left-content" ref="titleRef">
        <h2 class="title-main">
          {{ $t('LiveTrainings.NosDates') }}
        </h2>
        <p class="description-text">
          {{ $t('LiveTrainings.Description') }}
        </p>

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

      <div class="right-content">
        <div class="section-header">
          <h3 class="section-title">{{ $t('LiveTrainings.ProgrammesDisponibles') }}</h3>
          <div class="view-all">
            <span>{{ $t('LiveTrainings.ToutVoir') }}</span>
            <svg viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" class="arrow-icon">
              <polyline points="7 14 14 7 7 0"></polyline>
              <line x1="14" y1="7" x2="0" y2="7"></line>
            </svg>
          </div>
        </div>

        <div class="cards-grid" ref="cardsGridRef" @mousedown="handleDragStart" @mousemove="handleDragMove"
          @mouseup="handleDragEnd" @mouseleave="handleDragEnd">
          <div v-for="(training, index) in trainings" :key="index" class="card-wrapper">
            <span class="card-number">{{ String(index + 1).padStart(2, '0') }}</span>
            <TrainingCard v-bind="training" />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { usePage } from '@inertiajs/vue3';
import TrainingCard from '../ui/TrainingCard.vue';

const page = usePage();
const $t = (key) => page.props.translations[key] || key;

gsap.registerPlugin(ScrollTrigger);

const sectionRef = ref(null);
const titleRef = ref(null);
const cardsGridRef = ref(null);
const scrollHintRef = ref(null);

const trainings = [
  {
    title: 'Pack journée international du..',
    date: '15 Oct 2025',
    location: 'Ivandry',
    type: 'Présentiel',
    image: '/images/Bibliothèque/aesthetics graphic designer.jpg',
    description: 'Une immersion totale dans les principes du design moderne et de l\'UX/UI.',
    price: '190K Ar'
  },
  {
    title: 'Pack multimédia',
    date: '02 Nov 2025',
    location: 'Analakely',
    type: 'En ligne',
    image: '/images/Bibliothèque/Zoom.jpg',
    description: 'Développez votre leadership et apprenez à gérer des équipes performantes.',
    price: '200K Ar'
  },
  {
    title: 'Tech Innovators',
    date: '20 Nov 2025',
    location: 'Ivandry',
    type: 'En ligne',
    image: '/images/Bibliothèque/image-cours.webp',
    description: 'Les dernières tendances technologiques décryptées par des experts du secteur.',
    price: '190K Ar'
  },
  {
    title: 'Masterclass Design',
    date: '15 Dec 2025',
    location: 'Analakely',
    type: 'Présentiel',
    image: '/images/Bibliothèque/aesthetics graphic designer.jpg',
    description: 'Une immersion totale dans les principes du design moderne et de l\'UX/UI.',
    price: '200K Ar'
  },
];

const scrollCarrousel = (direction) => {
  if (!cardsGridRef.value) return;
  const cardElement = cardsGridRef.value.querySelector('.card-wrapper');
  if (!cardElement) return;
  const cardWidth = cardElement.offsetWidth;
  const gap = 10;
  const scrollDistance = cardWidth + gap;
  gsap.to(cardsGridRef.value, {
    scrollLeft: cardsGridRef.value.scrollLeft + (direction * scrollDistance),
    duration: 0.6,
    ease: 'power2.out',
  });
};

const isDragging = ref(false);
const startX = ref(0);
const scrollStartX = ref(0);



const handleDragStart = (e) => {
  isDragging.value = true;
  startX.value = e.pageX - cardsGridRef.value.offsetLeft;
  scrollStartX.value = cardsGridRef.value.scrollLeft;
  cardsGridRef.value.style.cursor = 'grabbing';
};

const handleDragMove = (e) => {
  if (!isDragging.value) return;
  e.preventDefault();
  const x = e.pageX - cardsGridRef.value.offsetLeft;
  const walk = (x - startX.value) * 1.5; // Scroll multiplier
  cardsGridRef.value.scrollLeft = scrollStartX.value - walk;
};

const handleDragEnd = () => {
  isDragging.value = false;
  if (cardsGridRef.value) {
    cardsGridRef.value.style.cursor = 'grab';
  }
};

const scrollHintAnimation = () => {
  const hint = scrollHintRef.value;
  if (!hint) return;

  // FIX: Separate the "Bounce" from the "ScrollTrigger"
  // 1. The bounce always runs (it's cheap performance-wise)
  const bounceTween = gsap.to(hint, {
    y: 8,
    duration: 1.2,
    yoyo: true,
    repeat: -1,
    ease: 'power1.inOut',
  });

  // 2. The ScrollTrigger ONLY controls opacity/visibility
  gsap.to(hint, {
    opacity: 0,
    y: -50, // Move up slightly when fading out
    duration: 0.8,
    ease: 'power2.in',

    scrollTrigger: {
      trigger: document.body,
      start: 'top top-=10', // Triggers after 10px scroll
      toggleActions: 'play none none reverse',
      // We no longer pause/restart the bounceTween here to avoid "frozen" state bugs
    },
  });
};

onMounted(() => {
  // 1. Animation de la courbe
  gsap.fromTo(sectionRef.value,
    {
      clipPath: 'ellipse(40% 65% at 50% 100%)',
    },
    {
      clipPath: 'ellipse(300% 150% at 50% 100%)',
      ease: 'power1.inOut',
      scrollTrigger: {
        trigger: sectionRef.value,
        start: 'top bottom',
        end: 'bottom center',
        scrub: 0.5,
      }
    }
  );

  // 2. Animation du titre
  gsap.from(titleRef.value.children, {
    opacity: 0,
    y: 30,
    duration: 1,
    stagger: 0.2,
    ease: 'power3.out',
    scrollTrigger: {
      trigger: titleRef.value,
      start: 'top 85%',
    }
  });

  // 3. Animation des cartes
  gsap.from(cardsGridRef.value.querySelectorAll('.card-wrapper'), {
    opacity: 0,
    x: 60,
    duration: 1,
    stagger: 0.2,
    ease: 'power4.out',
    scrollTrigger: {
      trigger: cardsGridRef.value,
      start: 'top 85%',
    }
  });

  // 4. Centrage du carrousel
  ScrollTrigger.create({
    trigger: sectionRef.value,
    start: 'top 80%',
    onEnter: () => {
      const grid = cardsGridRef.value;
      if (grid) {
        const centerPosition = (grid.scrollWidth - grid.clientWidth) / 2;

        gsap.to(grid, {
          scrollLeft: centerPosition,
          duration: 1.2,
          ease: 'power2.out',
        });
      }
    },
    once: true,
  });

  scrollHintAnimation();
});

onBeforeUnmount(() => {
  ScrollTrigger.getAll().forEach(trigger => {
    if (trigger.vars && (
      trigger.vars.trigger === sectionRef.value ||
      trigger.vars.trigger === titleRef.value ||
      trigger.vars.trigger === cardsGridRef.value ||
      trigger.vars.trigger === '#live-trainings'
    )) {
      trigger.kill();
    }
  });
});
</script>

<style scoped>
/* Indicateur */
.scroll-hint-wrapper {
  position: absolute;
  top: 40vh;
  left: 50%;
  transform: translateX(-50%);
  z-index: 20;
  cursor: pointer;
  opacity: 1;
  pointer-events: none;
}

.scroll-hint {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 12px;
  color: #111;
}

.scroll-text {
  font-size: 0.85rem;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  white-space: nowrap;
}

.icon-group {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.mouse-icon {
  width: 24px;
  height: 24px;
  margin-bottom: -8px;
  stroke-width: 1.5;
}

.arrow-down-icon {
  width: 16px;
  height: 24px;
  stroke-width: 2;
}

/* SECTION PRINCIPALE */
.live-trainings-section {
  background-color: white;
  padding: 15rem 0 2rem 0;
  position: relative;
  z-index: 30;
  margin-top: -50vh;
  overflow: hidden;

  clip-path: ellipse(150% 30% at 60% 100%);
}

.container {
  max-width: 100%;
  margin: 0 auto;
  padding-top: 33vh;
}

.two-column-layout {
  display: flex;
  gap: 40px;
}

.left-content {
  flex: 0 0 282px;
  padding-left: 5vw;
  padding-right: 5vw;
  border-right: 1px solid #ccc;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  margin-bottom: auto;
  margin-top: auto;
}

.title-main {
  font-size: clamp(2rem, 4vw, 2.5rem);
  font-weight: 600;
  color: #242424;
  line-height: 1.1;
  margin-top: 0;
  margin-bottom: 0;
}

.description-text {
  color: #161616;
  font-size: 1.2rem;
  line-height: 1.1;
  margin-bottom: auto;
}

.navigation-buttons {
  display: flex;
  gap: 15px;
  margin-top: 10vh;
}

.nav-arrow {
  background-color: #111;
  color: white;
  width: 55px;
  height: 35px;
  border-radius: 10px;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.nav-arrow:hover {
  background-color: #1a7c73;
}

.right-content {
  flex-grow: 1;
  min-width: 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  align-items: flex-end;
}

.section-title {
  font-size: clamp(1.5rem, 3vw, 2rem);
  font-weight: 500;
  color: #111;
  margin: 0;
}

.view-all {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-right: 3vw;
  font-size: 0.9rem;
  font-weight: 600;
  color: #111;
  cursor: pointer;
  text-transform: uppercase;
}

.arrow-icon {
  width: 16px;
  height: 16px;
  transform: rotate(0deg);
}

.cards-grid {
  display: flex;
  overflow-x: scroll;
  gap: 10px;
  padding-bottom: 2rem;
  -ms-overflow-style: none;
  scrollbar-width: none;
  will-change: scroll-position;
  backface-visibility: hidden;
  transform: translateZ(0);
  cursor: grab;
  user-select: none;
}

.cards-grid::-webkit-scrollbar {
  display: none;
}

.card-wrapper {
  flex: 0 0 auto;
  width: 380px;
  position: relative;
}

.card-number {
  position: absolute;
  bottom: 1vh;
  left: 0;
  font-size: 1rem;
  margin-left: 1vw;
  font-weight: 600;
  color: #ccc;
  z-index: 20;
}

.left-arrow {
  transform: rotate(180deg);
}

@media (max-width: 1024px) {
  .two-column-layout {
    flex-direction: column;
    gap: 2rem;
  }

  .left-content {
    flex: auto;
    border-right: none;
    padding-right: 0;
    border-bottom: 1px solid #ccc;
    padding-bottom: 30px;
  }

  .description-text {
    margin-bottom: 30px;
  }

  .container {
    padding-top: 120px;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 0 20px;
    padding-top: 100px;
  }

  .title-main {
    font-size: 2.5rem;
  }

  .card-wrapper {
    width: 80vw;
  }
}
</style>
