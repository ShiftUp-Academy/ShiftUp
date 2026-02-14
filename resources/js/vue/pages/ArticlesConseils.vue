<template>
    <div class="articles-page no-global-reveal" ref="pageRef">
        <!-- Hero Section - Minimalist Style -->
        <div class="intro-section">
            <div class="intro-content">
                <span class="category-label" ref="labelRef">RESSOURCES GRATUITES</span>
                <h1 class="impact-title" ref="titleRef">Articles et Conseils</h1>
                <p class="subtitle" ref="subtitleRef">
                    Boostez votre carrière avec nos programmes exclusifs, accessibles gratuitement
                    pour vous accompagner dans votre transformation.
                </p>
            </div>
        </div>

        <div class="articles-container container">
            <div class="articles-grid" v-if="filteredArticles.length > 0" ref="gridRef">
                <div v-for="(article, index) in filteredArticles" :key="article.IdProgrammeFormation"
                    class="article-card-wrapper">
                    <div class="article-card" @click="openReader(article)"
                        :style="{ backgroundColor: getCardColor(index) }">
                        <div class="card-image-container">
                            <img :src="article.LienPhoto || '/images/default-article.jpg'" class="card-image" />
                            <div class="card-overlay"></div>
                        </div>
                        <div class="card-body">
                            <h2 class="article-title">{{ article.Titre }}</h2>
                            <p class="article-desc">{{ truncate(article.Descriptions, 120) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="empty-state" ref="emptyRef">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.246 18.477 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3>Aucun article trouvé</h3>
                <p>Essayez de modifier vos filtres ou revenez plus tard.</p>
                <button @click="resetFilters" class="reset-btn">Réinitialiser les filtres</button>
            </div>
        </div>

        <PremiumModal :isOpen="showReader" @close="closeReader" width="90vw" :dark="true" :noPadding="true"
            :showClose="false">
            <div class="reader-container">
                <div class="reader-sidebar" v-if="hasMultipleLessons">
                    <h3 class="sidebar-title">Sommaire</h3>
                    <div class="lesson-nav">
                        <button v-for="(lesson, idx) in allArticleLessons" :key="lesson.IdLecon" class="nav-item"
                            :class="{ 'active': activeLessonIndex === idx }" @click="activeLessonIndex = idx">
                            <span class="nav-number">{{ idx + 1 }}</span>
                            <span class="nav-title">{{ lesson.Titre }}</span>
                        </button>
                    </div>
                </div>
                <div class="reader-main">
                    <div class="lesson-player-wrapper" data-lenis-prevent>
                        <LessonContentPlayer v-if="activeLesson" :lesson="activeLesson" :fullScreenMode="true" />
                    </div>
                </div>
            </div>
        </PremiumModal>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import PremiumModal from '../components/ui/PremiumModal.vue';
import LessonContentPlayer from '../components/sections/LessonContentPlayer.vue';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    articles: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    }
});

const searchValue = ref('');
const selectedCategory = ref(null);
const pageRef = ref(null);
const titleRef = ref(null);
const subtitleRef = ref(null);
const labelRef = ref(null);
const filterRef = ref(null);
const gridRef = ref(null);

// Reader State
const showReader = ref(false);
const selectedArticle = ref(null);
const activeLessonIndex = ref(0);

const colors = [
    '#F7B455', '#E67E22', '#D35400', '#C0392B',
    '#8E44AD', '#2980B9', '#27AE60', '#16A085',
    '#F39C12', '#D2527F', '#19B5FE', '#2ECC71',
    '#9B59B6', '#34495E', '#E74C3C', '#1A888D'
];

const getCardColor = (index) => colors[index % colors.length];

const formattedCategories = computed(() => {
    return props.categories.map(c => ({
        name: c.NomCategorie,
        code: c.IdCategorie
    }));
});

const filteredArticles = computed(() => {
    return props.articles.filter(article => {
        const matchesSearch = article.Titre.toLowerCase().includes(searchValue.value.toLowerCase()) ||
            (article.Descriptions && article.Descriptions.toLowerCase().includes(searchValue.value.toLowerCase()));
        const matchesCategory = !selectedCategory.value || article.IdCategorie === selectedCategory.value.code;
        return matchesSearch && matchesCategory;
    });
});

const allSuggestions = computed(() => {
    return props.articles.map(a => a.Titre);
});

const filteredSuggestions = ref([]);

const searchSuggestions = (event) => {
    const query = event.query.toLowerCase();
    filteredSuggestions.value = allSuggestions.value.filter(s =>
        s.toLowerCase().includes(query)
    );
};

const resetFilters = () => {
    searchValue.value = '';
    selectedCategory.value = null;
};

const allArticleLessons = computed(() => {
    if (!selectedArticle.value) return [];

    let lessons = [];

    if (selectedArticle.value.lecons && selectedArticle.value.lecons.length > 0) {
        lessons = [...selectedArticle.value.lecons];
    }

    if (selectedArticle.value.themes && selectedArticle.value.themes.length > 0) {
        const themeLessons = selectedArticle.value.themes.flatMap(t => t.lecons || []);
        lessons = [...lessons, ...themeLessons];
    }

    return lessons.sort((a, b) => (a.Ordre || 0) - (b.Ordre || 0));
});

const activeLesson = computed(() => allArticleLessons.value[activeLessonIndex.value]);
const hasMultipleLessons = computed(() => allArticleLessons.value.length > 1);

const openReader = (article) => {
    selectedArticle.value = article;
    activeLessonIndex.value = 0;
    showReader.value = true;
};

const closeReader = () => {
    showReader.value = false;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }).format(date);
};

const truncate = (text, length) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};

// GSAP Animations
let scrollTriggers = [];

const killTriggers = () => {
    scrollTriggers.forEach(t => t.kill());
    scrollTriggers = [];
    ScrollTrigger.refresh();
};

const initAnimations = () => {
    killTriggers();

    nextTick(() => {
        const cards = document.querySelectorAll(".article-card-wrapper");
        if (!cards.length) return;

        cards.forEach((card, i) => {
            const st = ScrollTrigger.create({
                trigger: card,
                start: "top bottom-=50",
                onEnter: () => {
                    gsap.fromTo(card,
                        { opacity: 0, y: 30 },
                        { opacity: 1, y: 0, duration: 0.6, ease: "power2.out", delay: (i % 3) * 0.05 }
                    );
                },
                once: true
            });
            scrollTriggers.push(st);
        });
    });
};

watch(filteredArticles, () => {
    initAnimations();
}, { deep: true });

onMounted(() => {
    initAnimations();
});
</script>

<style scoped>
.articles-page {
    background-color: #0d0616;
    color: #fff;
    min-height: 100vh;
    padding-bottom: 10vh;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.container {
    max-width: 85%;
    margin: 0 auto;
}

.intro-section {
    height: 60vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 140px 5vw 4vw;
    text-align: center;
    margin-bottom: 2rem;
}

.impact-title {
    font-size: clamp(3rem, 8vw, 5rem);
    margin-bottom: 2rem;
    margin-top: 0;
    font-weight: 300;
    line-height: 1;
    text-transform: uppercase;
    letter-spacing: -0.03em;
    color: #fff;
}

.subtitle {
    font-size: clamp(1.1rem, 2vw, 1.4rem);
    line-height: 1.2;
    opacity: 0.6;
    max-width: 50%;
    margin: 0 auto;
    font-weight: 300;
}

.category-label {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    color: rgba(255, 255, 255, 0.666);
    margin-bottom: 1.5rem;
    padding: 0.5rem 1rem;
}

.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 2rem;
}

.article-card-wrapper {
    height: 100%;
}

.article-card {
    background: rgba(255, 255, 255, 0.02);
    border-radius: 0;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: all 0.5s cubic-bezier(0.2, 1, 0.3, 1);
    cursor: pointer;
    position: relative;
}

.article-card:hover {
    transform: translateY(-5px);
    border-color: rgba(255, 255, 255, 0.15);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
}

.card-image-container {
    position: relative;
    aspect-ratio: 16 / 10;
    overflow: hidden;
}

.card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s ease;
}

.article-card:hover .card-image {
    transform: scale(1.05);
}

.card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent 60%, rgba(0, 0, 0, 0.6));
}

.card-body {
    padding: 1.5rem;
    padding-top: 0;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    color: #fff;
}

.article-title {
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 0;
}

.article-desc {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    font-size: 1rem;
    flex-grow: 1;
}

.reader-container {
    display: flex;
    height: 90vh;
    background: #111;
    position: relative;
}

.reader-sidebar {
    width: 320px;
    border-right: 1px solid #222;
    padding: 2.5rem;
    overflow-y: auto;
    background: #080808;
    flex-shrink: 0;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.reader-sidebar::-webkit-scrollbar {
    display: none;
}

.sidebar-title {
    font-size: 0.9rem;
    font-weight: 400;
    letter-spacing: 0.2em;
    color: #cacaca;
    text-transform: uppercase;
    margin-bottom: 1rem;
}

.lesson-nav {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.nav-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 1rem;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 15px;
    color: #555;
    text-align: left;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.nav-item:hover {
    color: #fff;
}

.nav-item.active {
    background: #fff;
    color: #000;
}

.nav-number {
    font-size: 0.65rem;
    font-weight: 800;
    opacity: 0.4;
}

.nav-title {
    font-size: 1rem;
    font-weight: 500;
    line-height: 1.2;
}

.reader-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #000;
    overflow: hidden;
}

.lesson-player-wrapper {
    flex: 1;
    overflow-y: auto;
    background: #111;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.lesson-player-wrapper::-webkit-scrollbar {
    display: none;
}

:deep(.lesson-content-player) {
    margin-bottom: 0 !important;
}

:deep(.text-container) {
    max-height: none !important;
    height: auto !important;
    min-height: 100%;
}

@media (max-width: 1024px) {
    .subtitle {
        max-width: 80%;
    }

    .reader-sidebar {
        display: none;
    }
}

@media (max-width: 768px) {
    .articles-grid {
        grid-template-columns: 1fr;
    }

    .container {
        max-width: 98%;
        margin: 0 auto;
    }

    .intro-section {
        height: auto;
        min-height: 40vh;
        padding: 15vh 10vw 40px;
    }

    .impact-title {
        font-size: 3rem;
        line-height: 1.1;
    }

    .article-title
    {
        font-size: 1.5rem;
        line-height: 1.1;
    }

    .article-desc
    {
        font-size: 1.2rem;
        line-height: 1.1;
        margin-top: 1vh;
    }

    .subtitle {
        max-width: 100%;
        font-weight: 500;
        font-size: 1.1rem;
    }

    .container {
        padding: 0 6vw;
    }

    .reader-container {
        height: 80vh;
    }
}
</style>
