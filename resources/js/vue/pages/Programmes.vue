<template>
    <div class="programmes-page">
        <div class="content-wrapper">
            <div class="info-col left">
                <div class="col-split top">
                    <div class="title-mask">
                        <h1 class="program-title" ref="titleRef">
                            {{ currentProgram?.title }}
                        </h1>
                    </div>
                </div>

                <div class="col-split bottom">
                    <div class="price-mask">
                        <div class="program-price" ref="priceRef">
                            {{ currentProgram?.price }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="scroll-col">
                <div v-for="(program, index) in displayPrograms" :key="index" class="program-image-wrapper"
                    :ref="el => imageRefs[index] = el" @click="goToDetail(program.id)">
                    <img :src="program.image" :alt="program.title" class="program-img" />
                </div>
            </div>

            <div class="info-col right">
                <div class="col-split top">
                    <div class="filter-wrapper">
                        <SectionFilters v-model:searchValue="searchValue" v-model:selectedCategory="selectedCategory"
                            :categories="categories" :suggestions="filteredSuggestions" :vertical="true"
                            :show-view-all="false" @reset="resetFilters" @complete="filterSuggestions"
                            @select-suggestion="handleSelectSuggestion" />
                    </div>
                </div>

                <div class="col-split bottom">
                    <div class="number-mask">
                        <div class="digit-wrapper" v-for="(digit, i) in digits" :key="i">
                            <span class="program-number" :ref="el => { if (el) digitRefs[i] = el }">
                                {{ digit }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="separator-line" ref="separatorRef"></div>

        <div class="scroll-indicator" ref="indicatorRef">
            FAITES DEFILER
        </div>

        <div class="bottom-label" ref="labelRef">
            NOS PROGRAMMES DE FORMATION
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, reactive, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import SectionFilters from '../components/ui/SectionFilters.vue';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    programmes: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    }
});

const programs = computed(() => {
    if (!props.programmes || props.programmes.length === 0) return [];
    return props.programmes
        .filter(p => p.Type !== 'Seminaire')
        .map(p => ({
            id: p.IdProgrammeFormation,
            title: p.Titre,
            image: p.LienPhoto || '/images/Programmes/Plan de travail1.png',
            price: Number(p.Prix) > 0 ? Number(p.Prix).toLocaleString() + ' Ar' : 'Gratuit',
            progression: p.progression,
            categoryId: p.IdCategorie,
            type: p.Type
        }));
});

const displayPrograms = computed(() => {
    return programs.value.filter(program => {
        const matchesSearch = program.title.toLowerCase().includes(searchValue.value.toLowerCase());
        const matchesCategory = !selectedCategory.value || program.categoryId === selectedCategory.value.code;
        return matchesSearch && matchesCategory;
    });
});

const imageRefs = ref([]);
const titleRef = ref(null);
const priceRef = ref(null);
const separatorRef = ref(null);
const indicatorRef = ref(null);
const labelRef = ref(null);
const digitRefs = ref([]);
const activeIndex = ref(0);

const searchValue = ref('');
const selectedCategory = ref(null);

const categories = computed(() => {
    return props.categories.map(c => ({
        name: c.NomCategorie,
        code: c.IdCategorie
    }));
});

const allSuggestions = computed(() => {
    return [
        ...displayPrograms.value.map(p => p.title)
    ];
});

const filteredSuggestions = ref([]);

const filterSuggestions = (event) => {
    const query = event.query.toLowerCase();
    filteredSuggestions.value = allSuggestions.value.filter(s =>
        s.toLowerCase().includes(query)
    );
};

const handleSelectSuggestion = (title) => {
    const index = displayPrograms.value.findIndex(p => p.title === title);
    if (index !== -1 && imageRefs.value[index]) {
        const target = imageRefs.value[index];
        if (window.lenis) {
            window.lenis.scrollTo(target, { offset: -window.innerHeight / 4 });
        } else {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
};

const resetFilters = () => {
    searchValue.value = '';
    selectedCategory.value = null;
};

const goToDetail = (id) => {
    if (id) {
        router.visit(`/programmes/${id}`);
    }
};

const getFormattedString = (index) => {
    const num = index + 1;
    return num < 10 ? `0${num}` : `${num}`;
};

const currentProgram = computed(() => displayPrograms.value[activeIndex.value] || { title: '', price: '' });
const digits = computed(() => getFormattedString(activeIndex.value).split(''));

const initScrollTriggers = () => {
    ScrollTrigger.getAll().forEach(t => t.kill());
    imageRefs.value = []; // Reset refs before re-render

    nextTick(() => {
        imageRefs.value.forEach((img, i) => {
            if (!img) return;
            ScrollTrigger.create({
                trigger: img,
                start: "top center",
                end: "bottom center",
                onEnter: () => animateTransition(i),
                onEnterBack: () => animateTransition(i),
            });
        });

        // Speed-based scaling effect
        ScrollTrigger.create({
            onUpdate: (self) => {
                const velocity = Math.abs(self.getVelocity());
                const targetScale = 1 - Math.min(velocity / 12000, 0.15);
                const inverseScale = 1 / targetScale;

                gsap.to(imageRefs.value, {
                    scale: targetScale,
                    duration: 0.8,
                    ease: "power2.out",
                    overwrite: "auto"
                });

                const images = imageRefs.value.filter(Boolean).map(ref => ref.querySelector('.program-img'));
                gsap.to(images, {
                    scale: inverseScale,
                    duration: 0.8,
                    ease: "power2.out",
                    overwrite: "auto"
                });
            }
        });
    });
};

watch(displayPrograms, () => {
    activeIndex.value = 0;
    initScrollTriggers();
}, { deep: true });

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
    const entranceTl = gsap.timeline({
        defaults: { ease: "power3.out", duration: 1.2 }
    });

    gsap.set([".info-col", ".separator-line", ".scroll-indicator", ".bottom-label", ".program-image-wrapper"], {
        opacity: 0
    });
    gsap.set(".info-col.left", { x: -50 });
    gsap.set(".info-col.right", { x: 50 });
    gsap.set(".separator-line", { scaleX: 0 });
    gsap.set([".scroll-indicator", ".bottom-label"], { y: 20 });
    gsap.set(".program-image-wrapper", { y: 40 });

    entranceTl
        .to(".separator-line", { scaleX: 1, opacity: 1, duration: 1.5 })
        .to(".info-col", { x: 0, opacity: 1, stagger: 0.2 }, "-=1")
        .to(".program-image-wrapper", { y: 0, opacity: 1, stagger: 0.1, duration: 1 }, "-=0.8")
        .to([".scroll-indicator", ".bottom-label"], { y: 0, opacity: 1, stagger: 0.1 }, "-=1");

    initScrollTriggers();

    if (displayPrograms.value.length > 0) {
        const lastImg = imageRefs.value[displayPrograms.value.length - 1];
        const elementsToAnimate = [
            separatorRef.value,
            indicatorRef.value,
            labelRef.value,
            document.querySelectorAll('.info-col')
        ];

        gsap.to(elementsToAnimate, {
            y: "-100vh",
            ease: "none",
            scrollTrigger: {
                trigger: lastImg,
                start: "bottom bottom",
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
    display: flex;
    justify-content: space-between;
}

.info-col {
    position: sticky;
    top: 0;
    height: 100vh;
    display: flex;
    flex-direction: column;
    padding: 2vh 4vw;
    width: 25vw;
    z-index: 10;
    /* Lowered from 9999 */
    pointer-events: none;
}

/* Re-enable interaction for content inside fixed columns */
.info-col * {
    pointer-events: auto;
}

.info-col.left .col-split {
    align-items: flex-start;
    /* Title & Price align Left */
    text-align: left;
}

.info-col.right .col-split {
    align-items: flex-end;
    /* Filters & Numbers align Right */
    text-align: right;
}

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
    padding-top: 0;
}

/* Top-Right Specific: Align relative to header */
.info-col.right .col-split.top {
    justify-content: flex-start;
    padding-top: 20vh;
    align-items: flex-end;
}

.filter-wrapper {
    width: 100%;
    z-index: 60;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    pointer-events: auto;
}

.filter-wrapper :deep(.filters-container) {
    padding: 0 !important;
    align-items: flex-end !important;
}

.filter-wrapper :deep(.actions-wrapper) {
    justify-content: flex-end !important;
    padding: 0 !important;
    gap: 0 !important;
}

.filter-wrapper :deep(.text-reset-button) {
    padding-right: 0 !important;
    padding-left: 10px !important;
}

.filter-wrapper :deep(.filter-inputs) {
    align-items: flex-end !important;
}

.scroll-col {
    width: 50vw;
    margin: 0;
    padding-top: 20vh;
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
    z-index: 5;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 30px;
    overflow: hidden;
    background: #111;
    cursor: pointer;

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

.separator-line {
    position: fixed;
    top: 50%;
    left: 0;
    width: 100%;
    height: 1px;
    background-color: rgba(255, 255, 255, 0.279);
    z-index: 0;
    pointer-events: none;
}

.title-mask,
.price-mask {
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
    margin-top: 1.5vh;
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

.scroll-indicator {
    position: fixed;
    bottom: 40px;
    left: 40px;
    margin-left: 1.5vw;
    font-size: 1.1rem;
    font-weight: 800;
    text-transform: uppercase;
    color: white;
    z-index: 20;
}

.bottom-label {
    position: fixed;
    bottom: 40px;
    right: 40px;
    margin-right: 1.5vw;
    font-size: 1.1rem;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    z-index: 20;
}

@media (max-width: 1024px) {
    .info-col {
        width: 30vw;
    }

    .program-title {
        font-size: 2.2rem;
    }

    .program-number {
        font-size: 3rem;
    }
}

@media (max-width: 768px) {
    .content-wrapper {
        flex-direction: column;
    }

    .info-col {
        position: relative;
        width: 100%;
        height: auto;
        padding: 40px 6vw;
        pointer-events: auto;
    }

    .info-col.left {
        position: fixed;
        height: 23%;
        left: 0;
        z-index: 10;
        padding: 15px 6vw;
        padding-top: 12vh;
    }

    .info-col.left::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, #212121, #21212188);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        mask-image: linear-gradient(to bottom, black 50%, transparent 100%);
        -webkit-mask-image: linear-gradient(to bottom, black 50%, transparent 100%);
        z-index: -1;
    }

    .info-col.right {
        order: -1;
        padding-top: 25vh;
        text-align: center;
    }

    .info-col.right .col-split.top {
        padding-top: 0;
        align-items: center;
    }

    .col-split {
        height: auto;
    }

    .col-split.top,
    .col-split.bottom {
        padding: 0;
        justify-content: center;
        align-items: center !important;
    }

    .program-title {
        font-size: 1.6rem;
        text-align: center;
    }

    .program-price {
        font-size: 1.2rem;
        margin-top: 5px;
        color: #ffffff;
        text-align: center;
    }

    .scroll-col {
        width: 100%;
        padding: 1px 3vw 100px;
    }

    .program-image-wrapper {
        height: 30vh;
        margin-bottom: 20px;
        border-radius: 20px;
    }

    .separator-line {
        display: none;
    }

    .program-number {
        display: none;
    }

    .scroll-indicator,
    .bottom-label {
        font-size: 0.8rem;
        bottom: 20px;
    }

    .scroll-indicator {
        left: 20px;
    }

    .bottom-label {
        right: 20px;
    }

    .filter-wrapper {
        align-items: center;
    }

    .filter-wrapper :deep(.filters-container),
    .filter-wrapper :deep(.filter-inputs) {
        align-items: center !important;
    }
}
</style>
