<template>
    <div class="video-sessions-list">
        <div class="list-container">
            <div class="sidebar">
                <h2 class="sidebar-title">NOS EVENEMENTS</h2>
                <p class="description-text">
                    Multipliez Votre Chiffre est un workshop intensif de 3 jours conçu comme un "MBA accéléré" pour
                    entrepreneurs. Apprenez à maîtriser les 5 piliers de la croissance pour passer du rôle d'exécutant à
                    celui de leader stratégique. Repartez avec un plan concret pour booster vos bénéfices de 30 % à 300 % et
                    piloter votre entreprise en toute sérénité.
                </p>
            </div>

            <div class="main-content scrollable" data-lenis-prevent>
                <div v-for="(item, index) in videoSessions" :key="index" class="session-item">
                    <div class="item-header">SÉQUENCE {{ index + 1 }}</div>

                    <div class="video-wrapper">
                        <iframe
                            :src="`https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(item.videoUrl)}&show_text=0&autoplay=1&loop=1&muted=1`"
                            width="100%" height="100%" style="border:none;overflow:hidden" scrolling="no" frameborder="0"
                            allowfullscreen="true"
                            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
                    </div>

                    <div class="item-footer">
                        <span class="category-tag">catégorie : <span class="category-name">{{ item.category }}</span></span>
                        <a :href="item.videoUrl" target="_blank" class="session-link">
                            VOIR LA SESSION
                            <img :src="ArrowIcon" alt="Flèche" class="arrow-icon" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import ArrowIcon from '../../../assets/images/fleche-lien.svg';
const videoSessions = [
    {
        authorAvatar: '/images/Bibliothèque/Nantenaina.jpg',
        videoUrl: 'https://www.facebook.com/nante.randria.gel/videos/736429424790754/?t=48',
        category: 'Entrepreneuriat'
    },
    {
        authorAvatar: '/images/Bibliothèque/Nantenaina.jpg',
        videoUrl: 'https://www.facebook.com/nante.randria.gel/videos/1426325668316950/?t=83',
        category: 'Développement'
    },
    {
        authorAvatar: '/images/Bibliothèque/Nantenaina.jpg',
        videoUrl: 'https://www.facebook.com/nante.randria.gel/videos/387180633829552/?t=18',
        category: 'Finances'
    }
];
</script>

<style scoped>
.video-sessions-list {
    width: 100%;
    display: flex;
    justify-content: center;
    background: rgb(0, 0, 0);
    padding-bottom: 9vh;
}

.list-container {
    display: flex;
    background: linear-gradient(34deg, #0E7EC3, #8A38F5);
    border-radius: 24px;
    overflow: hidden;
    height: 85vh;
    width: 90%;
}

.sidebar {
    display: flex;
    flex-direction: column;
    flex: 0 0 35%;
    padding: 60px 40px;
    display: flex;
    align-items: flex-start;
}

.sidebar-title {
    font-size: 3.5rem;
    margin-left: 2vw;
    margin-top: 0;
    font-weight: 300;
    line-height: 1.1;
    color: #fff;
    margin-bottom: 0;
    text-transform: uppercase;
}

.description-text {
    color: #ffffff;
    font-size: 1.2rem;
    line-height: 1.3;
    margin-left: 2.3vw;
    margin-bottom: auto;
}

.main-content {
    flex: 1;
    padding: 40px 60px 40px 60px;
    overflow-y: auto;
    overscroll-behavior: auto;
}

/* Custom Scrollbar */
.main-content::-webkit-scrollbar {
    width: 6px;
}

.main-content::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.main-content::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.4);
    border-radius: 10px;
}

.session-item {
    padding: 30px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.session-item:first-child {
    padding-top: 0;
}

.session-item:last-child {
    border-bottom: none;
}

.item-header {
    font-size: 0.8rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    margin-bottom: 15px;
}

.author-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.author-name {
    font-size: 1rem;
    font-weight: 400;
    color: #fff;
}

.video-wrapper {
    width: 100%;
    aspect-ratio: 16 / 9;
    background: #000;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.item-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.category-tag {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
}

.category-name {
    color: #fff;
    font-weight: 500;
}

.session-link {
    font-size: 0.8rem;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    text-transform: uppercase;
}

.arrow-icon {
    width: 13px;
    height: 13px;
    margin-left: 8px;
    margin-bottom: 3px;
    filter: invert(1);
    transition: transform 0.3s ease;
}

.session-link:hover .arrow-icon {
    transform: translateX(3px);
}

@media (max-width: 1024px) {
    .list-container {
        flex-direction: column;
        height: auto;
    }

    .sidebar {
        padding: 40px;
    }

    .sidebar-title {
        font-size: 2.5rem;
    }

    .main-content {
        padding: 40px;
        height: 500px;
    }
}</style>