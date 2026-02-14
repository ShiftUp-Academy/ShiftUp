<template>
    <AppLayout title="Mon Panier">
        <div class="panier-page">
            <div class="background-noise"></div>

            <div class="panier-wrapper">
                <div class="panier-container">
                    <div class="hero-section" @mousemove="handleMouseMove" @mouseleave="handleMouseLeave" ref="heroRef">
                        <video autoplay muted loop playsinline class="hero-video-bg">
                            <source :src="'/videos/panier.webm'" type="video/webm">
                        </video>
                        <div class="hero-content">
                            <h1 class="hover-title">
                                <span v-for="(char, index) in titleChars" :key="index" class="char"
                                    :class="{ 'break-word': char === ' ' }" :ref="el => charRefs[index] = el"
                                    :style="{ fontWeight: 300 }">
                                    {{ char === ' ' ? '&nbsp;' : char }}
                                </span>
                            </h1>
                            <p class="premium-subtitle" v-if="panier?.items?.length > 0">
                                {{ panier.items.length }} article(s) prêt(s) pour votre achat
                            </p>
                            <p class="premium-subtitle" v-else>
                                Préparez votre ascension vers le succès
                            </p>
                        </div>
                    </div>

                    <div v-if="!panier || panier.items.length === 0" class="empty-panier">
                        <LiquidGlass border-radius="40px" class="empty-glass">
                            <div class="empty-content">
                                <div class="empty-icon text-glow">
                                    <i class="fas fa-shopping-basket"></i>
                                </div>
                                <h2>Votre panier est vide</h2>
                                <p>Il est temps de passer à l'action et de choisir votre programme !</p>
                                <Link href="/programmes" class="browse-btn-premium">
                                    DÉCOUVRIR LES PROGRAMMES
                                </Link>
                            </div>
                        </LiquidGlass>
                    </div>

                    <div v-else class="panier-content">
                        <div class="items-section">
                            <div v-for="item in panier.items" :key="item.IdPanierItem" class="panier-item-card">
                                <LiquidGlass border-radius="20px">
                                    <div class="item-inner">
                                        <div class="item-thumb">
                                            <img :src="item.programme?.LienPhoto || item.offre?.LienPhoto || '/assets/images/placeholder.jpg'"
                                                alt="Cover">
                                        </div>
                                        <div class="item-info">
                                            <div class="item-top">
                                                <span class="badge">{{ item.programme ? 'Formation' : 'Pack Offre'
                                                }}</span>
                                                <h3 class="item-title">{{ item.programme?.Titre || item.offre?.Titre }}
                                                </h3>
                                            </div>
                                            <div class="item-bottom">
                                                <div class="item-price">{{ formatPrice(item.PrixAuMomentDeLAjout) }}
                                                </div>
                                                <button @click="removeItem(item.IdPanierItem)" class="delete-btn"
                                                    title="Supprimer">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </LiquidGlass>
                            </div>
                        </div>

                        <aside class="summary-section">
                            <LiquidGlass border-radius="30px" class="summary-glass">
                                <div class="summary-inner">
                                    <h2 class="summary-title">RÉSUMÉ</h2>
                                    <div class="summary-rows">
                                        <div class="s-row">
                                            <span>Sous-total</span>
                                            <span>{{ formatPrice(total) }}</span>
                                        </div>
                                        <div class="s-row">
                                            <span>TVA (Incluse)</span>
                                            <span>0 Ar</span>
                                        </div>
                                    </div>
                                    <div class="summary-divider"></div>
                                    <div class="total-row">
                                        <span class="total-label">TOTAL À RÉGLER</span>
                                        <span class="final-price">{{ formatPrice(total) }}</span>
                                    </div>
                                    <PremiumButton class="checkout-btn" @click="proceedToCheckout">
                                        <span>PROCÉDER AU PAIEMENT</span>
                                        <i class="fas fa-arrow-right ml-2"></i>
                                    </PremiumButton>

                                    <p class="secure-info">
                                        <i class="fas fa-shield-alt"></i> Paiement 100% sécurisé
                                    </p>
                                </div>
                            </LiquidGlass>
                        </aside>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmModal :isOpen="confirmData.isOpen" :title="confirmData.title" :message="confirmData.message"
            :type="confirmData.type" @confirm="onModalConfirm" @cancel="confirmData.isOpen = false" />
    </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import AppLayout from '@/vue/layout/AppLayout.vue';
import LiquidGlass from '@/vue/components/ui/LiquidGlass.vue';
import PremiumButton from '@/vue/components/ui/PremiumButton.vue';
import ConfirmModal from '@/vue/components/ui/ConfirmModal.vue';
const props = defineProps({
    panier: Object
});

const titleString = "VOTRE PANIER";
const titleChars = computed(() => titleString.split(''));
const charRefs = ref([]);
const heroRef = ref(null);

const handleMouseMove = (e) => {
    if (!heroRef.value) return;

    const mouseX = e.clientX;
    const mouseY = e.clientY;

    charRefs.value.forEach((charEl) => {
        if (!charEl) return;

        const charRect = charEl.getBoundingClientRect();
        const charCenterX = charRect.left + charRect.width / 2;
        const charCenterY = charRect.top + charRect.height / 2;

        const dist = Math.sqrt(Math.pow(mouseX - charCenterX, 2) + Math.pow(mouseY - charCenterY, 2));

        const maxDist = 200;
        let weight = 300;

        if (dist < maxDist) {
            weight = 800 - ((dist / maxDist) * 500);
        }

        gsap.to(charEl, {
            fontWeight: weight,
            color: '#ffffff',
            duration: 0.15,
            ease: "power3.out"
        });
    });
};

const handleMouseLeave = () => {
    charRefs.value.forEach(charEl => {
        gsap.to(charEl, {
            fontWeight: 300,
            color: '#ffffff',
            duration: 0.4,
            ease: "power3.out"
        });
    });
};

const total = computed(() => {
    if (!props.panier?.items) return 0;
    return props.panier.items.reduce((acc, item) => acc + parseFloat(item.PrixAuMomentDeLAjout || 0), 0);
});

const formatPrice = (price) => {
    return Math.round(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + ' Ar';
};

const confirmData = ref({
    isOpen: false,
    title: '',
    message: '',
    type: 'danger',
    itemId: null
});

const triggerConfirm = (title, message, type, itemId) => {
    confirmData.value = { isOpen: true, title, message, type, itemId };
};

const onModalConfirm = () => {
    if (confirmData.value.itemId) {
        router.delete(`/panier/${confirmData.value.itemId}`, {
            preserveScroll: true
        });
    }
    confirmData.value.isOpen = false;
};

const removeItem = (id) => {
    triggerConfirm(
        "Retirer de la sélection",
        "Voulez-vous vraiment retirer cet article de votre panier ?",
        "danger",
        id
    );
};

const proceedToCheckout = () => {
    alert('Bientôt disponible : Interface de paiement sécurisé.');
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

.panier-page {
    min-height: 100vh;
    background-color: #050505;
    color: #e0e0e0;
    font-family: 'Plus Jakarta Sans', sans-serif;
    position: relative;
    overflow-x: hidden;
}

.background-noise {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.05'/%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 0;
}

.panier-wrapper {
    position: relative;
    z-index: 1;
    padding: 20px 5vw 80px;
}

.panier-container {
    max-width: 1200px;
    margin: 0 auto;
}

.hero-section {
    position: relative;
    height: 40vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5vw;
    z-index: 1;
    margin-top: 9vh;
    margin-bottom: 5vh;
    overflow: hidden;
}

.hero-video-bg {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 22%;
    min-width: 200px;
    z-index: 0;
    pointer-events: none;
}

.hero-content {
    text-align: center;
    position: relative;
    z-index: 2;
}

.hover-title {
    font-size: 5rem;
    line-height: 1.1;
    margin: 0 0 20px 0;
    cursor: default;
    user-select: none;
    display: flex;
    flex-wrap: nowrap;
    justify-content: center;
    color: #ffffff;
}

.char {
    display: inline-block;
    color: #ffffff;
    transition: color 0.15s;
}

.premium-subtitle {
    color: rgba(255, 255, 255, 0.859);
    font-size: 1.3rem;
    font-weight: 300;
}

.panier-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
    align-items: flex-start;
}

.items-section {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

/* Item Card */
.item-inner {
    display: flex;
    padding: 24px;
    gap: 24px;
}

.item-thumb {
    width: 180px;
    height: 110px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.item-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.panier-item-card:hover .item-thumb img {
    transform: scale(1.1);
}

.item-info {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.badge {
    display: inline-block;
    padding: 5px 12px;
    background: rgba(138, 56, 245, 0.1);
    border: 1px solid rgba(138, 56, 245, 0.2);
    border-radius: 20px;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #a876ff;
    margin-bottom: 10px;
    font-weight: 700;
}

.item-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

.item-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.item-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
}

.delete-btn {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.39);
    color: rgba(255, 255, 255, 0.975);
    width: 42px;
    height: 42px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.delete-btn:hover {
    background: rgba(255, 59, 48, 0.15);
    border-color: rgba(255, 59, 48, 0.3);
    color: #ff4b4b;
    transform: rotate(9deg) scale(1.1);
}

.summary-inner {
    padding: 35px;
}

.summary-title {
    font-size: 0.9rem;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 30px;
    color: rgb(255, 255, 255);
}

.summary-rows {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin-bottom: 30px;
}

.s-row {
    display: flex;
    justify-content: space-between;
    color: rgba(255, 255, 255, 0.897);
    font-size: 1rem;
}

.summary-divider {
    height: 1px;
    background: linear-gradient(to right, rgba(255, 255, 255, 0.1), transparent);
    margin-bottom: 25px;
}

.total-row {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 40px;
}

.total-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.4);
    letter-spacing: 1px;
}

.final-price {
    font-size: 3rem;
    font-weight: 200;
    color: #ffffff;
    line-height: 1;
}

.checkout-btn {
    width: 100% !important;
    --btn-bg: linear-gradient(130deg, #0d7bc0, #8A38F5);
    height: 60px !important;
    font-size: 1rem !important;
    font-weight: 700 !important;
}

.secure-info {
    margin-top: 20px;
    text-align: center;
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

/* Empty State */
.empty-panier {
    max-width: 700px;
    margin: 40px auto;
}

.empty-content {
    padding: 80px 40px;
    text-align: center;
}

.empty-icon {
    font-size: 5rem;
    margin-bottom: 30px;
    opacity: 0.2;
    color: #8A38F5;
}

.browse-btn-premium {
    display: inline-block;
    margin-top: 40px;
    padding: 18px 36px;
    background: white;
    color: black;
    border-radius: 40px;
    font-weight: 800;
    font-size: 0.9rem;
    letter-spacing: 1px;
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.browse-btn-premium:hover {
    background: #8A38F5;
    color: white;
    transform: translateY(-8px) scale(1.05);
    box-shadow: 0 20px 40px rgba(138, 56, 245, 0.3);
}

@media (max-width: 992px) {
    .panier-content {
        grid-template-columns: 1fr;
    }

    .hover-title {
        margin-top: 3vh;
    font-size: 4rem !important;
    }

    .summary-section {
        position: static;
        margin-top: 20px;
    }

    .hover-title {
        font-size: 3rem;
        flex-wrap: wrap !important;
        padding: 0 20px;
        line-height: 1.2;
    }
}

@media (max-width: 768px) {
    .premium-subtitle {
        font-size: 1.2rem;
        font-weight: 500;
        padding: 0 10vw;
    }

    .break-word {
        flex-basis: 100%;
        height: 0;
    }

    .hero-video-bg {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 25%;
        min-width: 250px;
        z-index: 0;
        pointer-events: none;
    }
}

@media (max-width: 600px) {
    .item-inner {
        flex-direction: column;
    }

    .item-thumb {
        width: 100%;
        height: 180px;
    }
}
</style>
