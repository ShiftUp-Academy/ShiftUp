<template>
  <section ref="sectionRef" class="category-section">
    <div class="container two-column-layout">

      <div class="left-content" ref="titleRef">
        <h2 class="title-main">
          TOUTES LES <br> OFFRES <br> DISPONIBLES DU MOMENT
        </h2>
        <p class="description-text">
          Explorez toutes les offres proposées par ShiftUp regroupées en une seule page.
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
          <h3 class="section-title">Les offres disponibles</h3>
          <div class="view-all">
            <Link href="/offres" class="view-all-link">TOUT VOIR</Link>
            <svg viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" class="arrow-icon">
              <polyline points="7 14 14 7 7 0"></polyline>
              <line x1="14" y1="7" x2="0" y2="7"></line>
            </svg>
          </div>
        </div>

        <div class="cards-grid" ref="cardsGridRef">
          <div v-for="(offer, index) in processedOffers" :key="index" class="card-wrapper">
            <CategoryCard v-bind="offer" />

            <div class="card-meta">
              <span class="card-number">{{ String(index + 1).padStart(2, '0') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import CategoryCard from '../ui/CategoryCard.vue';

gsap.registerPlugin(ScrollTrigger);

const sectionRef = ref(null);
const titleRef = ref(null);
const cardsGridRef = ref(null);

const props = defineProps({
  offres: {
    type: Array,
    default: () => []
  }
});

const calcPackPrice = (offre) => {
  let total = 0;
  if (offre.programmes && Array.isArray(offre.programmes)) {
    offre.programmes.forEach(op => {
      const base = Number(op.programme?.Prix || 0);
      const red = Number(op.ReductionSpecifique || 0);
      total += base * (1 - red / 100);
    });
  }
  if (offre.coachings && Array.isArray(offre.coachings)) {
    offre.coachings.forEach(oc => {
      const base = Number(oc.coaching?.Prix || 0);
      const red = Number(oc.ReductionSpecifique || 0);
      total += base * (1 - red / 100);
    });
  }
  const globalRed = Number(offre.ReductionGlobal || 0);
  return Math.round(total * (1 - globalRed / 100));
};

const processedOffers = computed(() => {
  if (!props.offres) return [];
  return props.offres.map(o => ({
    id: o.IdOffre,
    title: o.Titre,
    image: o.LienPhoto || '/images/placeholder.jpg',
    description: o.Descriptions || 'Offre exclusive proposée par ShiftUp.',
    price: calcPackPrice(o).toLocaleString() + ' Ar',
    isOffer: true
  }));
});

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

onMounted(() => {
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
});
</script>

<style scoped>
/* SECTION PRINCIPALE */
.category-section {
  background-color: transparent;
  /* Changed from white to allow body transition */
  padding: 2rem 0 0 0;
  position: relative;
  z-index: 10;
}

.container {
  max-width: 100%;
  margin: 0 auto;
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
  text-transform: uppercase;
  margin-top: 0;
  margin-bottom: 2rem;
}

.description-text {
  color: #161616;
  font-size: 1.2rem;
  line-height: 1.3;
  margin-bottom: 2rem;
}

.navigation-buttons {
  display: flex;
  gap: 15px;
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
  margin-bottom: 15px;
  align-items: flex-end;
  padding-right: 3vw;
}

.section-title {
  font-size: clamp(1.5rem, 3vw, 2rem);
  font-weight: 500;
  color: #111;
  margin: 0;
}

.view-all-link {
  text-decoration: none;
  color: inherit;
  font-weight: inherit;
}

.view-all {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #111;
  cursor: pointer;
  text-transform: uppercase;
}

.arrow-icon {
  width: 16px;
  height: 16px;
}

.cards-grid {
  display: flex;
  overflow-x: scroll;
  gap: 10px;
  padding-bottom: 2rem;
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.cards-grid::-webkit-scrollbar {
  display: none;
}

.card-wrapper {
  flex: 0 0 auto;
  width: 380px;
  position: relative;
  display: flex;
  flex-direction: column;
}

.card-meta {
  margin-top: 10px;
  border-top: 2px solid #111;
  padding-top: 8px;
  display: flex;
  justify-content: flex-start;
}

.card-number {
  font-size: 0.9rem;
  font-weight: 700;
  color: #111;
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
    padding-left: 20px;
    padding-right: 20px;
  }

  .container {
    padding-top: 2rem;
  }

  .section-header {
    padding-left: 20px;
    padding-right: 20px;
  }
}

@media (max-width: 768px) {
  .title-main {
    font-size: 2.2rem;
    font-weight: 500;
    padding-top: 0;
    padding-bottom: 0;
  }

  .description-text {
    font-size: 1.2rem;
    line-height: 1.1;
    margin-top: 0;
    padding-top: 0;
    margin-bottom: 2rem;
  }

  .card-wrapper {
    width: 85vw;
    /* Slightly increased to match card width better */
  }

  .card-meta {
    margin-left: 2vw;
    /* Align with CategoryCard's margin-left */
    width: calc(100% - 4vw);
    /* Align with CategoryCard's width */
  }
}
</style>
