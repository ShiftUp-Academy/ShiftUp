<template>
  <div class="programmes-page">
    <div class="content-wrapper">
      <!-- LEFT COLUMN -->
      <div class="info-col left">
        <!-- Top Half: Title (Above Line) -->
        <div class="col-split top">
            <div class="title-mask">
              <h1 class="program-title" ref="titleRef">
                {{ currentProgram.title }}
              </h1>
            </div>
        </div>
        
        <!-- Bottom Half: Price (Below Line) -->
        <div class="col-split bottom">
            <div class="price-mask">
               <div class="program-price" ref="priceRef">
                 {{ currentProgram.price }}
               </div>
            </div>
        </div>
      </div>

      <!-- CENTER COLUMN: Scrollable Images -->
      <div class="scroll-col">
        <div 
          v-for="(program, index) in programs" 
          :key="index"
          class="program-image-wrapper"
          :ref="el => imageRefs[index] = el"
        >
          <img :src="program.image" :alt="program.title" class="program-img" />
        </div>
      </div>

      <!-- RIGHT COLUMN -->
      <div class="info-col right">
        <!-- Top Half: Filters (Above Line) -->
        <div class="col-split top">
            <div class="filter-wrapper">
            <SectionFilters 
                v-model:searchValue="searchValue"
                v-model:selectedCategory="selectedCategory"
                :categories="categories"
                :suggestions="suggestions"
                :vertical="true"
                @reset="resetFilters"
            />
            </div>
        </div>

        <!-- Bottom Half: Number (Below Line) -->
        <div class="col-split bottom">
            <div class="number-mask">
               <!-- Render digits individually for animation -->
              <div class="digit-wrapper" v-for="(digit, i) in digits" :key="i">
                  <span class="program-number" :ref="el => { if(el) digitRefs[i] = el }">
                    {{ digit }}
                  </span>
              </div>
            </div>
        </div>
      </div>
    </div>
    
    <!-- Thin Center Line -->
    <div class="separator-line" ref="separatorRef"></div>

    <div class="scroll-indicator">
        FAITES DEFILER
    </div>

    <div class="bottom-label">
        NOS PROGRAMMES DE FORMATION
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, reactive } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import SectionFilters from '../components/ui/SectionFilters.vue';

gsap.registerPlugin(ScrollTrigger);

const programs = [
  {
    title: 'Formation montage vidéo',
    image: '/images/Programmes/Plan de travail1.png',
    price: '190k Ar'
  },
  {
    title: 'Formation stratégie marketing',
    image: '/images/Programmes/Plan de travail2.png',
    price: '100k Ar'
  },
  {
    title: 'Etude de marché express',
    image: '/images/Programmes/Plan de travail3.png',
    price: '150k Ar'
  },
  {
    title: 'Formation montage vidéo',
    image: '/images/Programmes/Plan de travail4.png',
    price: '190k Ar'
  },
  {
    title: 'Formation stratégie marketing',
    image: '/images/Programmes/Plan de travail5.png',
    price: '190k Ar'
  },
  {
    title: 'Etude de marché express',
    image: '/images/Programmes/Plan de travail6.png',
    price: '190k Ar'
  }
];

const imageRefs = ref([]);
const titleRef = ref(null);
const priceRef = ref(null);
const separatorRef = ref(null);
const digitRefs = ref([]); 
const activeIndex = ref(0);

// Filter State
const searchValue = ref('');
const selectedCategory = ref(null);
const categories = [
  { name: 'Marketing', code: 'MKT' },
  { name: 'Design', code: 'DSG' },
  { name: 'Développement', code: 'DEV' }
];
const suggestions = [
  'Formation Montage', 'Vidéographie', 'Design Expert'
];

const resetFilters = () => {
  searchValue.value = '';
  selectedCategory.value = null;
};

const getFormattedString = (index) => {
    const num = index + 1;
    return num < 10 ? `0${num}` : `${num}`;
};

const currentProgram = computed(() => programs[activeIndex.value]);
const digits = computed(() => getFormattedString(activeIndex.value).split(''));

const animateTransition = (newIndex) => {
    if (newIndex === activeIndex.value) return;

    const direction = newIndex > activeIndex.value ? 1 : -1;
    const yExit = direction * -100; 
    const yEnter = direction * 100; 

    // TITLE & PRICE ANIM
    const tl = gsap.timeline();
    tl.to([titleRef.value, priceRef.value], {
        y: yExit, 
        opacity: 0,
        duration: 0.4,
        ease: "power2.in",
        stagger: 0.05,
        onComplete: () => {
            activeIndex.value = newIndex;
            gsap.set([titleRef.value, priceRef.value], { y: yEnter }); 
        }
    })
    .to([titleRef.value, priceRef.value], {
        y: 0,
        opacity: 1,
        duration: 0.5,
        ease: "power3.out",
        stagger: 0.1
    });

    // DIGITS ANIM
    const oldStr = getFormattedString(activeIndex.value);
    const newStr = getFormattedString(newIndex);
    
    for (let i = 0; i < 2; i++) {
        if (oldStr[i] !== newStr[i]) {
            const el = digitRefs.value[i];
            if (!el) continue;

            const dTl = gsap.timeline();
            dTl.to(el, {
                y: yExit,
                opacity: 0,
                duration: 0.4,
                ease: "power2.in",
                onComplete: () => {
                   gsap.set(el, { y: yEnter });
                }
            })
            .to(el, {
                y: 0,
                opacity: 1,
                duration: 0.5,
                ease: "power3.out",
                delay: 0.05
            });
        }
    }
};

onMounted(() => {
    imageRefs.value.forEach((img, i) => {
        ScrollTrigger.create({
            trigger: img,
            start: "top center",
            end: "bottom center",
            onEnter: () => animateTransition(i),
            onEnterBack: () => animateTransition(i),
        });
    });

    if (separatorRef.value && imageRefs.value.length > 0) {
        const lastImg = imageRefs.value[imageRefs.value.length - 1];
        gsap.to(separatorRef.value, {
            y: "-100vh",
            ease: "none",
            scrollTrigger: {
                trigger: lastImg,
                start: "center center",
                end: "bottom top",
                scrub: true
            }
        });
    }
});

onBeforeUnmount(() => {
    ScrollTrigger.getAll().forEach(t => t.kill());
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

.programmes-page {
    background-color: #000;
    color: white;
    position: relative;
    min-height: 100vh;
}

.content-wrapper {
    position: relative;
    width: 100%;
}

/* --- FIXED SIDE COLUMNS --- */
/* Fixed ensures elements don't scroll with the page, avoiding layout fade effects */
.info-col {
    position: fixed;
    top: 0;
    height: 100vh;
    display: flex;
    flex-direction: column;
    padding: 2vh 4vw;
    width: 25vw;
    z-index: 9999; /* Always on top */
    pointer-events: none;
}

/* Re-enable interaction for content inside fixed columns */
.info-col * {
    pointer-events: auto;
}

/* Left Column Styling */
.info-col.left {
    left: 0;
}

.info-col.left .col-split {
    align-items: flex-start; /* Title & Price align Left */
    text-align: left;
}

/* Right Column Styling */
.info-col.right {
    right: 0;
}

.info-col.right .col-split {
    align-items: flex-end; /* Filters & Numbers align Right */
    text-align: right;
}

/* --- VERTICAL SPLITS --- */
.col-split {
    height: 50vh;
    display: flex;
    flex-direction: column;
    width: 100%;
}

.col-split.top {
    justify-content: flex-end;
    padding-bottom: 20px;
}

.col-split.bottom {
    justify-content: flex-start;
    padding-top: 20px;
}

/* Top-Right Specific: Align relative to header */
.info-col.right .col-split.top {
    justify-content: flex-start;
    padding-top: 40px; 
    align-items: flex-end;
}

/* --- FILTERS --- */
.filter-wrapper {
    width: 100%;
    z-index: 60;
    display: flex;
    flex-direction: column;
    align-items: flex-end; /* Push filter content to right */
    pointer-events: auto;
}

/* Force internal filter elements to align right */
.filter-wrapper :deep(.filters-container),
.filter-wrapper :deep(.is-vertical),
.filter-wrapper :deep(.filter-inputs),
.filter-wrapper :deep(.actions-wrapper) {
    align-items: flex-end !important;
    text-align: right;
}

/* --- CENTER SCROLL COLUMN --- */
.scroll-col {
    width: 50vw; 
    margin: 0 auto; /* Centers the column relative to viewport */
    padding-top: 20vh;
    padding-bottom: 60vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
}

.program-image-wrapper {
    width: 100%;
    height: 60vh;
    margin-bottom: 15vh; 
    z-index: 5; /* Higher than separator line (z:2) */
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 30px;
    overflow: hidden;
    background: #111;
    
    /* Radius preservation fixes */
    -webkit-mask-image: -webkit-radial-gradient(white, black);
    mask-image: radial-gradient(white, black);
    transform: translateZ(0); 
}

.program-img {
    width: 100%;
    height: 100%;
    object-fit: cover; 
    transition: transform 0.5s ease;
}

.program-image-wrapper:hover .program-img {
    transform: scale(1.05);
}

/* --- DECORATIVE ELEMENTS --- */
.separator-line {
    position: fixed;
    top: 50%;
    left: 0;
    width: 100%;
    height: 1px;
    background-color: rgba(255, 255, 255, 0.279);
    z-index: 2; /* Low z-index: Behind images (z:5), above bg */
    pointer-events: none;
}

.title-mask, .price-mask {
    overflow: hidden;
    width: 100%;
}

.program-title {
    font-size: 3.2rem;
    font-weight: 400;
    line-height: 1;
    margin: 0;
    width: 100%; 
    max-width: 100%; 
}

.program-price {
    font-size: 2rem;
    font-weight: 400;
    color: #888;
}

.number-mask {
    overflow: hidden;
    display: flex;
    gap: 2px;
}

.digit-wrapper {
    display: inline-block;
}

.program-number {
    font-size: 4rem;
    font-weight: 300;
    display: inline-block;
}

/* --- ABSOLUTE LABELS (Scroll with section) --- */
.scroll-indicator {
    position: absolute; /* Changes from fixed to absolute */
    bottom: 40px;
    left: 40px;
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    color: white;
    z-index: 20;
}

.bottom-label {
    position: absolute; /* Changes from fixed to absolute */
    bottom: 40px;
    right: 40px;
    font-size: 0.8rem;
    font-weight: 800;
    color: #666;
    text-transform: uppercase;
    z-index: 20;
}
</style>
