<template>
        <div class="privacy-page" ref="pageRef">
            <div class="back-link-glass" @click="goBack">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M19 12H5M5 12L12 19M5 12L12 5" />
                </svg>
                <span>{{ $t('PolitiqueDeConfidentialite.retour') }}</span>
            </div>

            <div class="legal-hero">
                <div class="hero-content">
                    <div class="tagline">{{ $t('PolitiqueDeConfidentialite.politiques__juridique') }}</div>
                    <h1 class="premium-title">{{ $t('PolitiqueDeConfidentialite.politique_de') }} <span class="accent-text">{{ $t('PolitiqueDeConfidentialite.confidentialit') }}</span></h1>
                    <p class="hero-desc">
                        {{ $t('PolitiqueDeConfidentialite.shiftup_sengage') }}
                    </p>
                </div>
            </div>

            <div class="content-wrapper">
                <div class="legal-section" v-for="(section, idx) in legalSections" :key="idx">
                    <div class="section-badge">ARTICLE {{ idx + 1 }}</div>
                    <h2 class="section-title">{{ section.title }}</h2>
                    <div class="section-body" v-html="section.content"></div>
                </div>

                <div class="bottom-card">
                    <h3>{{ $t('PolitiqueDeConfidentialite.une_question_sur') }}</h3>
                    <p>{{ $t('PolitiqueDeConfidentialite.contactez_notre_responsable') }}
                    </p>
                    <PremiumButton @click="openMail" class="premium-btn">{{ $t('PolitiqueDeConfidentialite.contactshiftupacademy') }}</PremiumButton>
                </div>
            </div>
        </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import PremiumButton from '../components/ui/PremiumButton.vue';
import { gsap } from 'gsap';

const pageRef = ref(null);

const goBack = () => {
    window.history.back();
};

const openMail = () => {
    window.location.href = 'mailto:contact@shiftup.academy';
};

const legalSections = [
    {
        title: "Introduction & Engagement",
        content: `
            <p>ShiftUp s’engage à ce que la collecte et le traitement d’informations à caractère personnel soient conformes au Règlement UE n°2016/679 du Parlement Européen et du Conseil du 27 avril 2016 (RGPD) relatif à la Protection des Personnes Physiques.</p>
            <div class="info-box">
                <strong>Rappel :</strong> Une <em>donnée personnelle</em> est toute information identifiant directement ou indirectement une personne physique (nom, n°, photographie, IP...).
            </div>
        `
    },
    {
        title: "Responsable du traitement",
        content: `
            <p>Le Responsable du traitement est <strong>Shift-Up</strong>, dont le siège social est situé à Antananarivo / Madagascar.</p>
            <p>Téléphone : +261 34 20 236 88<br>Site web : <a href="https://www.shiftup.academy/" target="_blank">www.shiftup.academy</a></p>
        `
    },
    {
        title: "Données Collectées & Bases Légales",
        content: `
            <p>Vos données sont collectées sur les bases légales suivantes :</p>
            <ul class="premium-list">
                <li>Exécution d’un contrat (Abonnement, Formation)</li>
                <li>Intérêts légitimes de l'organisme</li>
                <li>Fondement de votre consentement explicite</li>
            </ul>
            <p>Nous collectons notamment vos données d'identification (Nom, Prénom, Email) et vos données de connexion (IP, logs).</p>
        `
    },
    {
        title: "Opérations sur vos données",
        content: `
            <p>Certaines opérations utilisent vos données car elles sont nécessaires à la fourniture des services :</p>
            <ul class="premium-list">
                <li>Prise en compte de votre souscription et facturation</li>
                <li>Relation client : service accessible par téléphone ou chat</li>
                <li>Recueil des avis clients et amélioration de l'expérience</li>
                <li>Mise aux normes et sécurité de la base de données</li>
            </ul>
        `
    },
    {
        title: "Conservation & Durée",
        content: `
            <p>Vos données personnelles sont conservées pendant une durée conforme aux dispositions légales ou proportionnelle aux finalités pour lesquelles elles ont été collectées.</p>
            <p>Elles peuvent être conservées plus longtemps si une obligation légale ou réglementaire nous l’impose.</p>
        `
    },
    {
        title: "Vos Droits & Libertés",
        content: `
            <p>Vous disposez d'un droit d'accès, de rectification et d'opposition. Le <strong>droit à l’effacement</strong> s'applique notamment :</p>
            <ul class="premium-list">
                <li>Si les données ne sont plus nécessaires aux finalités prévues</li>
                <li>Si vous retirez votre consentement</li>
                <li>Si le traitement est jugé illicite</li>
                <li>Si l'effacement est requis par une obligation légale</li>
            </ul>
        `
    }
];

onMounted(() => {
    const tl = gsap.timeline();

    tl.from(".hero-content", { y: 60, opacity: 0, duration: 1.2, ease: "power4.out" })
        .from(".legal-section", {
            y: 40,
            opacity: 0,
            duration: 0.8,
            stagger: 0.2,
            ease: "power3.out"
        }, "-=0.6")
        .from(".back-link-glass", { x: -20, opacity: 0, duration: 0.8 }, "-=1");
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');

.privacy-page {
    background: #0d0616;
    color: #ffffff;
    min-height: 100vh;
    font-family: 'Outfit', sans-serif;
    padding-bottom: 100px;
    overflow-x: hidden;
}

/* Glass back link */
.back-link-glass {
    position: fixed;
    top: 30px;
    left: 40px;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 50px;
    backdrop-filter: blur(20px);
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.19, 1, 0.22, 1);
    font-weight: 600;
    font-size: 0.85rem;
    letter-spacing: 1px;
}

.back-link-glass:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateX(5px);
    border-color: #8A38F5;
}

.legal-hero {
    padding: 180px 5vw 100px;
    text-align: center;
}

.tagline {
    font-weight: 500;
    font-size: 0.9rem;
    margin-bottom: 0;
    color: #9f9f9f;
    letter-spacing: 4px;
}

.premium-title {
    font-size: clamp(1rem, 8vw, 7rem);
    font-weight: 500;
    margin-top: 2vh;
    line-height: 1;
    margin-bottom: 30px;
    letter-spacing: -2px;
}

.accent-text {
    background: linear-gradient(135deg, #8A38F5 0%, #B275FF 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-desc {
    font-size: 1.25rem;
    line-height: 1.1;
    max-width: 50%;
    margin: 0 auto;
    opacity: 0.7;
    font-weight: 300;
}

.content-wrapper {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 5vw;
}

.legal-section {
    margin-bottom: 80px;
    position: relative;
    padding: 40px;
    transition: transform 0.3s;
}

.section-badge {
    position: absolute;
    top: -12px;
    left: 40px;
    color: rgb(205, 205, 205);
    font-size: 0.9rem;
    font-weight: 500;
    letter-spacing: 3px;
}

.section-title {
    font-size: 1.8rem;
    margin-top: 0;
    font-weight: 600;
    color: #ffffff;
}

.section-body :deep(p) {
    font-size: 1.1rem;
    line-height: 1.8;
    color: rgba(255, 255, 255, 0.75);
    margin-bottom: 1.5rem;
}

.section-body :deep(strong) {
    color: #ffffff;
    font-weight: 600;
}

.section-body :deep(a) {
    color: #8A38F5;
    text-decoration: none;
    border-bottom: 1px dashed #8A38F5;
    transition: all 0.3s;
}

.section-body :deep(a:hover) {
    color: #B275FF;
    border-bottom-style: solid;
}

.section-body :deep(.premium-list) {
    list-style: none;
    padding: 0;
    margin-bottom: 2rem;
}

.section-body :deep(.premium-list li) {
    padding-left: 28px;
    position: relative;
    margin-bottom: 12px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 1.05rem;
}

.section-body :deep(.premium-list li::before) {
    content: "→";
    position: absolute;
    left: 0;
    color: #8A38F5;
    font-weight: 800;
}

.section-body :deep(.info-box) {
    padding: 25px;
    background: rgba(138, 56, 245, 0.1);
    border-left: 4px solid #d1d1d1;
    margin: 2rem 0;
    font-size: 1rem;
}

.bottom-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    justify-content: center;
    margin-top: 100px;
}

.bottom-card h3 {
    font-size: 2rem;
    margin-bottom: 15px;
}

.bottom-card p {
    opacity: 0.6;
    font-size: 1.3rem;
    font-weight: 300;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .back-link-glass {
        display: none;
    }

    .legal-hero {
        padding-top: 120px;
    }

    .legal-section {
        padding: 30px 20px;
    }
    
    .section-title {
        padding: 0;
        font-size: 1.4rem;
    }

    .premium-title {
    font-size: 3.5rem;
    margin-top: 2vh;
    margin-bottom: 30px;
    letter-spacing: -2px;
}

.hero-desc {
    font-size: 1.25rem;
    line-height: 1.1;
    max-width: 90%;
    margin: 0 auto;
}
}

@media (max-width: 480px) {
    .premium-title {
        font-size: 2.5rem;
    }

    .hero-desc {
        font-size: 1rem;
    }

    .legal-hero {
        padding-top: 100px;
        padding-bottom: 40px;
    }

    .legal-section {
        padding: 20px 10px;
        margin-bottom: 40px;
    }

    .section-title {
        font-size: 1.25rem;
    }

    .section-body :deep(p) {
        font-size: 0.95rem;
    }

    .bottom-card h3 {
        font-size: 1.5rem;
    }

    .bottom-card p {
        font-size: 1rem;
    }
}
</style>
