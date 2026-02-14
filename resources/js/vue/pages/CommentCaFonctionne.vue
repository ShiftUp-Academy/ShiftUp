<template>
    <div class="page-container no-global-reveal how-it-works-page" ref="pageRef">
        <div class="back-link" @click="goBack">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M5 12L12 19M5 12L12 5" />
            </svg>
            <span>Retour</span>
        </div>

        <div class="intro-section">
            <div class="intro-content">
                <h1 class="impact-title">Principes Opérationnels</h1>
                <p class="subtitle">
                    Voici les Principes Opérationnels que respecte chaque membre de l’équipe de ShiftUp et les
                    entrepreneurs Libres :
                </p>
                <div class="scroll-indicator">
                    <span>Scroller pour découvrir</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 13L12 18L17 13M7 6L12 11L17 6" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="horizontal-scroll-container" ref="scrollContainer">
            <div class="cards-track" ref="trackRef">
                <div v-for="(principle, index) in principles" :key="index" class="principle-card"
                    :class="{ 'has-long-text': isLongText(principle.body), 'is-expanded': hoveredIndex === index }"
                    :style="{ backgroundColor: getCardColor(index) }" @mouseenter="handleMouseEnter(index)"
                    @mouseleave="handleMouseLeave">
                    <div class="card-content">
                        <div class="card-number">{{ String(index + 1).padStart(2, '0') }}</div>
                        <h3 class="card-title">{{ principle.title }}</h3>
                        <div class="card-body" v-html="formatBody(principle.body)" data-lenis-prevent @wheel.stop></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const pageRef = ref(null);
const scrollContainer = ref(null);
const trackRef = ref(null);
const hoveredIndex = ref(null);

const handleMouseEnter = (index) => {
    hoveredIndex.value = index;
    document.body.classList.add('is-hovering-expandable');
};

const handleMouseLeave = () => {
    hoveredIndex.value = null;
    document.body.classList.remove('is-hovering-expandable');
};

const goBack = () => {
    window.history.back();
};

const principles = [
    {
        title: "L’Entrepreneur Libre choisit une direction claire et fixe des objectifs ambitieux.",
        body: "Le but de l’Organisme est d’aider plus d’un million d’entrepreneurs francophones à devenir des Entrepreneurs Libres. Toutes nos actions, et les objectifs trimestriels de chaque membre de l’équipe, visent à atteindre cet objectif."
    },
    {
        title: "L’Entrepreneur Libre « regarde droit devant » et avance avec détermination parce que son objectif est clair.",
        body: "C’est pourquoi nous avons mis par écrit notre raison d’être, notre Objectif Stratégique, dans le Manifeste de l’Organisme, que nous avons aussi retranscrit nos Principes Opérationnels, et nous prenons le temps de créer des Procédures de Travail. Nous croyons fermement qu’une entreprise se définit aussi bien par les tentations qu’elle décline, que par les opportunités qu’elle saisit. Pour cette raison nous passons régulièrement en revue notre Objectif Stratégique avant de lancer de nouveaux projets – et nous disons « NON » à la majority des opportunités, pour pouvoir dire « OUI » à notre mission."
    },
    {
        title: "L’Entrepreneur Libre donne la priorité aux priorités.",
        body: "1. Nous travaillons sur les tâches les plus importantes en premier.<br>2. Nous passons le maximum de temps possible sur les tâches “non-urgentes et importantes” telles que définies par Stephen R. Covey : plutôt que de sauter de crise en crise, nous donnons priorité aux activités qui suivent l’Objectif Stratégique. Cela se reflète, par exemple, dans nos réunions d’équipe où la majeure partie de la réunion vise à avancer vers l’Objectif Stratégique – et seule la dernière partie de la réunion est consacrée aux crises et dossiers « urgents ».<br>3. Nous ne travaillons pas en multitâche. Quand nous communiquons avec quelqu’un, nous sommes présents à 100 pourcent. Quand nous suivons une formation ou exécutons une mission, nous coupons toute source potentielle d’interruption."
    },
    {
        title: "L’Entrepreneur Libre voit toujours plus grand.",
        body: "Nous combattons activement ce grand ennemi : le Conformisme. Nous avons une vision de l’entreprise bien différente de la « boîte enfermante » qu’on y associe souvent. Au contraire, nous bâtissons cette entreprise sous la forme d’un Organisme et de Mouvement qui nous permet d’exprimer notre créativité, vivre nos valeurs, et travailler avec passion. Nous ne faisons pas la « course au profit » ; nous préférons nous concentrer sur l’apport maximum de valeur à nos clients. Et paradoxalement, cela augmente notre rentabilité et permet à l’ensemble de l’équipe d’en tirer les fruits avec une rémunération stable, à la hauteur des efforts fournis."
    },
    {
        title: "Gratitude et Optimisme",
        body: "L’Entrepreneur Libre sait qu’il a déjà accompli beaucoup, et que le meilleur reste à venir. Nous mettons un point d’honneur à célébrer nos accomplissements, petits et grands. C’est pourquoi nous démarrons nos rendez-vous d’équipe en félicitant les membres pour leurs réussites; et c’est également pour cela que nous prenons le temps d’un moment de « débriefing » décontracté après un gros projet ou un séminaire."
    },
    {
        title: "L’Entrepreneur Libre se voit comme le « Champion » d’une cause – il transforme des vies.",
        body: "Nous travaillons uniquement avec des personnes qui croient profondément à notre vision de l’Entrepreneur Libre. Nos collaborateurs vivent leur vie selon les valeurs de l’Entrepreneur Libre et ont conscience de l’importance de la mission qui leur est confiée : transformer des millions de chefs d’entreprise et de porteurs de projet en Entrepreneurs Libres !"
    },
    {
        title: "L’Entrepreneur Libre n’a pas peur d’échouer.",
        body: "C’est pourquoi nous continuons toujours à nous améliorer en tirant les leçons de nos expériences passées. Le travail du gérant, des managers et chefs de département est de créer, superviser, et améliorer en permanence les systèmes de l’entreprise. Nous construisons une machine bien huilée actionnée par des personnes compétentes – pas un ”bricolage” nécessitant des tours de passe-passe ou un écopage permanent."
    },
    {
        title: "L’Entrepreneur Libre est exigeant envers les autres… Parce qu’il est exigeant envers lui-même.",
        body: "1. Nous employons des personnes qui se donnent à 100% dans leur travail, et nous les rémunérons généreusement en conséquence. Quand nous sommes au travail, nous travaillons dur, de façon concentrée, et en échange l’entreprise fournit de l’ambiance et des avantages.<br> 2. « Les paroles s’envolent mais les écrits restent » : nous mettons tout au clair par écrit.<br>3. Notre lieu de travail est impeccable : propre et ordonné. <br> 4. Notre tenue est le reflet de notre entreprise : professionnelle, sobre.<br>5. Nous prêtons une attention toute particulière à la langue française.<br>6. Nous faisons relire tout document avant publication.<br>7. Tout événement est annoncé dans l’agenda.<br>8. Nous tenons nos promesses de livraison.<br>9. Nous maintenons notre équipement fonctionnel à 100%."
    },
    {
        title: "L’Entrepreneur Libre crée un entourage qui le soutient et l’inspire à aller toujours plus haut.",
        body: "Notre climat de travail est sérieux et calme, mais surtout serein, apaisant, et amical. Chacun peut y donner le meilleur de lui-même et être respecté pour la qualité de son travail et de ses idées.<br><br>1. Nous communiquons régulièrement via les « Stand-Up ».<br>2. Nous n’utilisons pas la communication par email au sein de l’équipe.<br>3. L’accueil et la formation des nouveaux collaborateurs sont structurés."
    },
    {
        title: "L’Entrepreneur Libre tire le maximum de ses ressources pour créer quelque chose de nouveau.",
        body: "L’entreprise opère en suivant des procédures rédigées noir sur blanc.<br>1. Si un problème se présente de façon récurrente, nous mettons en place un système.<br>2. Chaque membre a la responsabilité de maintenir ses procédures.<br>3. Nous automatisons les tâches répétitives de plus de 3 heures."
    },
    {
        title: "L’entrepreneur Libre est un Agisseur : il décide vite et agit avec détermination.",
        body: "En toute situation, nous appliquons la solution la plus simple.<br>2. Nous évitons de créer des procédures pour des situations uniques.<br>3. “On ne touche un message qu’une fois” : traitement immédiat pour rester réatif."
    },
    {
        title: "L’Entrepreneur Libre apporte sans cesse davantage de valeur à ses clients.",
        body: "1. Mise à jour constante des formations.<br>2. Écoute active des suggestions (communautés, coaching, SAV).<br>3. Proposer des alternatives constructives lors de refus."
    },
    {
        title: "L’Entrepreneur Libre gère ses finances avec discipline.",
        body: "Un Ariary économisé est un Ariary gagné pour l’entreprise et ses collaborateurs ! Nous veillons à ce que les dépenses se fassent dans l'optique de l'Objectif Stratégique."
    },
    {
        title: "L’Entrepreneur Libre met un grain de folie dans sa vie.",
        body: "Lorsque nous avons terminé notre journée de travail, nous décrochons complètement. Nous valorisons le repos, l'alimentation saine et les rituels dynamisants pour garder une énergie positive."
    },
    {
        title: "L’Entrepreneur Libre s’inspire des meilleurs.",
        body: "Nous améliorons en permanence notre savoir et notre savoir-faire en lisant, en nous formant et en échangeant. Nous mettons en pratique nos apprentissages immédiatement."
    },
    {
        title: "L’Entrepreneur Libre inspire ceux qui le suivent.",
        body: "1. Nous montrons l’exemple en vivant nous-mêmes une vie libre.<br>2. Nous apportons le maximum de valeur gratuite (vidéos, newsletters).<br>3. Nous maintenons une communication bienveillante en toute circonstance."
    }
];

const colors = [
    '#F7B455', '#E67E22', '#D35400', '#C0392B',
    '#8E44AD', '#2980B9', '#27AE60', '#16A085',
    '#F39C12', '#D2527F', '#19B5FE', '#2ECC71',
    '#9B59B6', '#34495E', '#E74C3C', '#1A888D'
];

const getCardColor = (index) => colors[index % colors.length];

const formatBody = (text) => {
    return text.replace(/\n/g, '<br>');
};

const isLongText = (text) => {
    return text.length > 250 || text.includes('<br><br>');
};

let ctx;

onMounted(async () => {
    await nextTick();

    ctx = gsap.context(() => {
        const scrollSection = scrollContainer.value;
        const track = trackRef.value;
        const isMobile = window.innerWidth <= 768;

        let cardWidth;
        let expansionDelta = 0;

        if (isMobile) {
            // Mobile: 85vw, min-width 320px
            const vw85 = window.innerWidth * 0.85;
            cardWidth = Math.max(vw85, 320);
        } else {
            // Desktop: 28vw, min-width 400px
            const vw28 = window.innerWidth * 0.27;
            cardWidth = Math.max(vw28, 300);
            const vw45 = window.innerWidth * 0.45;
            expansionDelta = (window.innerWidth * 0.17);
        }

        // Total width
        const stableTrackWidth = (principles.length * cardWidth) + expansionDelta;

        track.style.width = `${stableTrackWidth}px`;
        const scrollDistance = stableTrackWidth - window.innerWidth;

        gsap.to(track, {
            x: -scrollDistance,
            ease: "none",
            scrollTrigger: {
                trigger: scrollSection,
                start: "top top",
                end: () => `+=${scrollDistance}`,
                pin: true,
                scrub: 1,
                anticipatePin: 1,
                invalidateOnRefresh: true
            }
        });

        gsap.from('.impact-title', { y: 50, opacity: 0, duration: 1, ease: 'power3.out', delay: 0.2 });
        gsap.from('.subtitle', { y: 30, opacity: 0, duration: 1, ease: 'power3.out', delay: 0.4 });
    }, pageRef.value);
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
    document.body.classList.remove('is-hovering-expandable');
});
</script>

<style scoped>
.page-container {
    min-height: 100vh;
    background-color: #0d0616;
    color: white;
    width: 100%;
}

.back-link {
    position: fixed;
    top: 2rem;
    left: 2rem;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 0.8rem 1.2rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50px;
    backdrop-filter: blur(15px);
    transition: all 0.3s cubic-bezier(0.19, 1, 0.22, 1);
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

@media (max-width: 768px) {
    .back-link {
        display: none !important;
    }
}

.intro-section {
    height: 87vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 140px 5vw 5vw;
    text-align: center;
}

.impact-title {
    font-size: clamp(3rem, 8vw, 5rem);
    margin-bottom: 2rem;
    font-weight: 400;
    line-height: 1;
    text-transform: uppercase;
    letter-spacing: -0.03em;
}

.subtitle {
    font-size: clamp(1.1rem, 3vw, 1.8rem);
    line-height: 1.5;
    opacity: 0.85;
    max-width: 850px;
    margin: 0 auto 5rem;
    font-weight: 300;
}

.scroll-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    opacity: 0.5;
    animation: bounce 2.5s infinite ease-in-out;
}

.horizontal-scroll-container {
    height: 100vh;
    width: 100vw;
    display: flex;
    overflow: hidden;
    position: relative;
    background: #0d0616;
}

.cards-track {
    display: flex;
    height: 100%;
}

.principle-card {
    flex: 0 0 28vw;
    min-width: 400px;
    height: 100vh;
    padding: 100px 50px 150px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    color: white;
    border-right: 1px solid rgba(255, 255, 255, 0.08);
    transition: flex-basis 0.6s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.4s ease;
    overflow: hidden;
}

.principle-card.is-expanded.has-long-text {
    flex-basis: 45vw;
}

.card-number {
    font-size: clamp(4rem, 10vw, 7rem);
    font-weight: 900;
    opacity: 0.12;
    margin-bottom: -1rem;
    line-height: 1;
}

.card-title {
    font-size: clamp(1.5rem, 4vw, 2.2rem);
    font-weight: 700;
    margin-bottom: 2rem;
    line-height: 1.1;
    letter-spacing: -0.02em;
}

.card-body {
    font-size: clamp(1rem, 2vw, 1.2rem);
    line-height: 1.5;
    opacity: 0.92;
    max-height: 52vh;
    overflow-y: auto;
    padding-right: 20px;
    padding-bottom: 120px;
    /* Increased security space at the bottom */
}

.card-body::-webkit-scrollbar {
    width: 4px;
}

.card-body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
}

@keyframes bounce {

    0%,
    100% {
        transform: translateY(0);
        opacity: 0.4;
    }

    50% {
        transform: translateY(15px);
        opacity: 0.8;
    }
}

@media (max-width: 1024px) {
    .principle-card {
        flex-basis: 45vw;
    }
}

@media (max-width: 768px) {
    .principle-card {
        flex-basis: 85vw;
        min-width: 320px;
        padding: 100px 30px;
    }

    .principle-card.is-expanded.has-long-text {
        flex-basis: 85vw;
    }
}
</style>
