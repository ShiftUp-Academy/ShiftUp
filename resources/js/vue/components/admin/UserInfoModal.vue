<template>
    <PremiumModal :isOpen="isOpen" :header="user?.Email ? `Détails : ${user.Email}` : 'Détails Utilisateur'"
        width="75vw" @close="$emit('close')">

        <template #header-extra v-if="user?.Role && user.Role !== 'utilisateur'">
            <div class="header-badges">
                <span v-if="user.Role === 'admin'" class="admin-badge-premium">Administrateur</span>
                <span v-if="user.Role === 'moderateur'" class="mod-badge-premium">Modérateur</span>
            </div>
        </template>

        <div class="tabs-header">
            <button v-for="tab in tabs" :key="tab.id" class="tab-btn" :class="{ active: currentTab === tab.id }"
                @click="currentTab = tab.id">
                <i :class="tab.icon" class="mr-2"></i> {{ tab.label }}
            </button>
        </div>

        <div class="modal-body-content custom-scrollbar">
            <div v-if="loading" class="loading-state">
                <i class="fas fa-spinner fa-spin"></i> Chargement des données...
            </div>

            <div v-else class="data-content">
                <!-- Tab: Profil -->
                <div v-if="currentTab === 'profil'" class="profil-tab-content">
                    <div class="profil-grid">
                        <div class="profil-main-card shadow-sm">
                            <div class="profil-avatar-section">
                                <img v-if="user?.profil?.PhotoProfil" :src="user.profil.PhotoProfil"
                                    class="profil-img-large" />
                                <div v-else class="profil-placeholder-large">
                                    {{ user?.profil?.Prenom?.[0] || user?.Email?.[0] }}
                                </div>
                            </div>
                            <div class="profil-info-section">
                                <h2 class="user-fullname">{{ user?.profil ? `${user.profil.Prenom} ${user.profil.Nom}` :
                                    'Utilisateur sans nom' }}</h2>
                                <p class="user-email-text">{{ user?.Email }}</p>
                                <div class="role-display" v-if="user?.Role">
                                    <span :class="['role-pill', user.Role]">
                                        <i class="fas"
                                            :class="user.Role === 'admin' ? 'fa-crown' : (user.Role === 'moderateur' ? 'fa-user-shield' : 'fa-user')"></i>
                                        {{ user.Role.toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="profil-details-grid">
                            <div class="detail-tile">
                                <span class="tile-label">Date d'inscription</span>
                                <span class="tile-value">{{ formatDate(user?.DateCreation) }}</span>
                            </div>
                            <div class="detail-tile">
                                <span class="tile-label">Dernière connexion</span>
                                <span class="tile-value">{{ formatDate(user?.DerniereConnexion, true) || 'Jamais connecté'}}</span>
                            </div>
                            <div class="detail-tile">
                                <span class="tile-label">Téléphone</span>
                                <span class="tile-value">{{ user?.profil?.NumeroTelephone || 'Non renseigné' }}</span>
                            </div>
                            <div class="detail-tile">
                                <span class="tile-label">Adresse</span>
                                <span class="tile-value">{{ user?.profil?.Adresse || 'Non renseigné' }}</span>
                            </div>
                        </div>

                        <div class="profil-bio-section shadow-sm"
                            v-if="user?.profil?.Metier || user?.profil?.Biographie">
                            <div class="bio-item" v-if="user.profil.Metier">
                                <span class="tile-label">Métier</span>
                                <p class="bio-text-premium">{{ user.profil.Metier }}</p>
                            </div>
                            <div class="bio-item" v-if="user.profil.Biographie">
                                <span class="tile-label">Biographie</span>
                                <p class="bio-text-premium">{{ user.profil.Biographie }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Programmes -->
                <div v-if="currentTab === 'programmes'" class="items-list">
                    <div v-if="!programPurchases.length" class="empty-state">
                        <i class="fas fa-graduation-cap empty-icon"></i>
                        <p>Aucun programme acheté.</p>
                    </div>
                    <div v-else class="list-container">
                        <div v-for="prog in programPurchases" :key="prog.IdProgrammeFormation"
                            class="info-card-container">
                            <div class="info-card" @click="toggleExpand(prog.IdProgrammeFormation)">
                                <div class="item-visual">
                                    <img v-if="prog.LienPhoto" :src="prog.LienPhoto" class="item-thumb" />
                                    <div v-else class="item-thumb-placeholder"><i class="fas fa-book"></i></div>
                                </div>
                                <div class="item-details">
                                    <h3 class="item-title">{{ prog.Titre }}</h3>
                                    <div class="item-meta">
                                        <span class="badge badge-success">Acheté</span>
                                        <span class="meta-info">{{ getProgressionPercent(prog.IdProgrammeFormation) }}%
                                            terminé</span>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-down expand-icon"
                                    :class="{ rotated: expandedItems.has(prog.IdProgrammeFormation) }"></i>
                            </div>

                            <transition name="expand">
                                <div v-if="expandedItems.has(prog.IdProgrammeFormation)" class="sub-items-container">
                                    <div v-if="getProgressionDetails(prog.IdProgrammeFormation).length">
                                        <div v-for="step in getProgressionDetails(prog.IdProgrammeFormation)"
                                            :key="step.IdProgression" class="sub-item">
                                            <div class="step-info">
                                                <span class="step-type-icon"><i class="fas fa-check-circle"></i></span>
                                                <div class="step-text">
                                                    <span class="step-name">{{ step.etape?.Titre || 'Étape' }}</span>
                                                    <span class="step-context">{{ step.theme?.Titre }} > {{
                                                        step.lecon?.Titre }}</span>
                                                </div>
                                            </div>
                                            <span class="step-date">Le {{ formatDate(step.DateSelection) }}</span>
                                        </div>
                                    </div>
                                    <div v-else class="no-subs">Aucune progression enregistrée pour ce programme.</div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>

                <!-- Tab: Séminaires -->
                <div v-if="currentTab === 'seminaires'" class="items-list">
                    <div v-if="!seminarPurchases.length" class="empty-state">
                        <i class="fas fa-calendar-alt empty-icon"></i>
                        <p>Aucun séminaire réservé.</p>
                    </div>
                    <div v-else class="list-container">
                        <div v-for="semi in seminarPurchases" :key="semi.IdProgrammeFormation" class="info-card simple">
                            <div class="item-visual">
                                <img v-if="semi.LienPhoto" :src="semi.LienPhoto" class="item-thumb" />
                                <div v-else class="item-thumb-placeholder"><i class="fas fa-users"></i></div>
                            </div>
                            <div class="item-details">
                                <h3 class="item-title">{{ semi.Titre }}</h3>
                                <div class="item-meta">
                                    <span class="badge badge-info">{{ semi.ModaliteSeminaire || 'Séminaire' }}</span>
                                    <span v-if="semi.DateSeminaire" class="meta-info">Prévu le {{
                                        formatDate(semi.DateSeminaire) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Coaching -->
                <div v-if="currentTab === 'coachings'" class="items-list">
                    <div v-if="!details.coachings?.length" class="empty-state">
                        <i class="fas fa-headset empty-icon"></i>
                        <p>Aucune session de coaching.</p>
                    </div>
                    <div v-else class="list-container">
                        <div v-for="c in details.coachings" :key="c.IdReservation" class="info-card simple">
                            <div class="item-visual coaching">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="item-details">
                                <h3 class="item-title">{{ c.type?.NomDeType || 'Coaching' }}</h3>
                                <div class="item-meta">
                                    <span class="badge" :class="getStatusClass(c.StatutReservation)">{{
                                        c.StatutReservation }}</span>
                                    <span class="meta-info">{{ formatDate(c.HeureDebutReservation, true) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Offres -->
                <div v-if="currentTab === 'offres'" class="items-list">
                    <div v-if="!allOffers.length" class="empty-state">
                        <i class="fas fa-gift empty-icon"></i>
                        <p>Aucune offre attribuée.</p>
                    </div>
                    <div v-else class="list-container">
                        <div v-for="o in allOffers" :key="o.IdOffre" class="info-card simple">
                            <div class="item-visual offer">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="item-details">
                                <h3 class="item-title">{{ o.Titre }}</h3>
                                <div class="item-meta">
                                    <span class="badge badge-premium">Offre active</span>
                                    <span class="meta-info" v-if="o.ReductionGlobal">{{ o.ReductionGlobal }}% de
                                        réduction</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Questions -->
                <div v-if="currentTab === 'questions'" class="items-list">
                    <div v-if="!details.questions?.length" class="empty-state">
                        <i class="fas fa-question-circle empty-icon"></i>
                        <p>Aucune question posée.</p>
                    </div>
                    <div v-else class="list-container">
                        <div v-for="q in details.questions" :key="q.IdConsultation"
                            class="info-card simple questions-card">
                            <div class="item-visual question">
                                <i class="fas fa-question"></i>
                            </div>
                            <div class="item-details">
                                <h3 class="item-title">{{ q.Titre }}</h3>
                                <p class="question-preview">{{ q.Question }}</p>
                                <div class="item-meta">
                                    <span class="badge"
                                        :class="q.Statut === 'Répondu' ? 'badge-success' : 'badge-warning'">
                                        {{ q.Statut || 'En attente' }}
                                    </span>
                                    <span class="meta-info">{{ formatDate(q.DateCreation, true) }}</span>
                                    <span v-if="q.lecon" class="context-pill">
                                        <i class="fas fa-book-open"></i> {{ q.lecon?.Titre }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PremiumModal>
</template>

<script setup>
import { ref, computed } from 'vue';
import PremiumModal from '../ui/PremiumModal.vue';

const props = defineProps({
    isOpen: Boolean,
    user: Object,
    details: {
        type: Object,
        default: () => ({ purchases: [], progressions: [], coachings: [], assignedOffers: [], questions: [] })
    },
    loading: Boolean
});

defineEmits(['close']);

const currentTab = ref('profil');
const expandedItems = ref(new Set());

const tabs = [
    { id: 'profil', label: 'Compte & Profil', icon: 'fas fa-user-circle' },
    { id: 'programmes', label: 'Programmes', icon: 'fas fa-graduation-cap' },
    { id: 'seminaires', label: 'Séminaires', icon: 'fas fa-calendar-alt' },
    { id: 'coachings', label: 'Coachings', icon: 'fas fa-headset' },
    { id: 'offres', label: 'Offres', icon: 'fas fa-gift' },
    { id: 'questions', label: 'Questions', icon: 'fas fa-question-circle' },
];

const programPurchases = computed(() => {
    if (!props.details.purchases) return [];
    const progs = [];
    props.details.purchases.forEach(order => {
        order.items.forEach(item => {
            if (item.programme && item.programme.Type !== 'Seminaire') {
                progs.push(item.programme);
            }
        });
    });
    return progs;
});

const seminarPurchases = computed(() => {
    if (!props.details.purchases) return [];
    const semis = [];
    props.details.purchases.forEach(order => {
        order.items.forEach(item => {
            if (item.programme && item.programme.Type === 'Seminaire') {
                semis.push(item.programme);
            }
        });
    });
    return semis;
});

const allOffers = computed(() => {
    const fromPurchases = [];
    if (props.details.purchases) {
        props.details.purchases.forEach(order => {
            order.items.forEach(item => {
                if (item.offre) fromPurchases.push(item.offre);
            });
        });
    }
    const manuallyAssigned = (props.details.assignedOffers || []).map(ao => ao.offre).filter(Boolean);

    // Unique by IdOffre
    const map = new Map();
    [...fromPurchases, ...manuallyAssigned].forEach(o => map.set(o.IdOffre, o));
    return Array.from(map.values());
});

const toggleExpand = (id) => {
    if (expandedItems.value.has(id)) expandedItems.value.delete(id);
    else expandedItems.value.add(id);
};

const getProgressionDetails = (programId) => {
    if (!props.details.progressions) return [];
    return props.details.progressions.filter(p => p.IdProgramme === programId);
};

const getProgressionPercent = (programId) => {
    // This is a simplified calculation, ideally you'd have the total steps for each program loaded
    // For now we just return a mock or placeholder if we don't have total steps
    const solved = getProgressionDetails(programId).length;
    if (solved > 0) return Math.min(100, solved * 5); // Placeholder math
    return 0;
};

const formatDate = (dateString, withTime = false) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const opt = { day: '2-digit', month: 'short', year: 'numeric' };
    if (withTime) { opt.hour = '2-digit'; opt.minute = '2-digit'; }
    return date.toLocaleDateString('fr-FR', opt);
};

const getStatusClass = (status) => {
    if (status === 'Confirmé') return 'badge-success';
    if (status === 'En attente') return 'badge-warning';
    return 'badge-secondary';
};

</script>

<style scoped>
.tabs-header {
    display: flex;
    padding: 0 1rem;
    gap: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 20px;
    background: #fff;
    overflow-x: auto;
}

.tab-btn {
    background: none;
    border: none;
    padding: 1rem 0;
    font-size: 0.95rem;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    position: relative;
    white-space: nowrap;
    transition: all 0.2s;
}

.tab-btn:hover {
    color: #111827;
}

.tab-btn.active {
    color: #8A38F5;
    font-weight: 600;
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 2px;
    background: #8A38F5;
    border-radius: 2px 2px 0 0;
}

.modal-body-content {
    min-height: 50vh;
    max-height: 65vh;
    overflow-y: auto;
    padding-right: 10px;
}

.loading-state,
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 300px;
    color: #9ca3af;
    gap: 1rem;
}

.empty-icon {
    font-size: 3rem;
    opacity: 0.5;
}

.list-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-card-container {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.info-card {
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    cursor: pointer;
    transition: background 0.2s;
}

.info-card:hover {
    background: #f9fafb;
}

.info-card.simple {
    cursor: default;
}

.info-card.simple:hover {
    background: white;
}

.item-visual {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    overflow: hidden;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.item-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-thumb-placeholder {
    font-size: 1.5rem;
    color: #d1d5db;
}

.item-visual.coaching {
    background: #e0f2fe;
    color: #0369a1;
    font-size: 1.5rem;
}

.item-visual.offer {
    background: #fef3c7;
    color: #92400e;
    font-size: 1.5rem;
}

.item-details {
    flex: 1;
}

.item-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 5px 0;
}

.item-meta {
    display: flex;
    align-items: center;
    gap: 10px;
}

.badge {
    font-size: 0.7rem;
    padding: 2px 10px;
    border-radius: 20px;
    font-weight: 700;
    text-transform: uppercase;
    background: #f3f4f6;
    color: #4b5563;
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

.badge-info {
    background: #e0f2fe;
    color: #0369a1;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-premium {
    background: #faf5ff;
    color: #6b21a8;
    border: 1px solid #e9d5ff;
}

.meta-info {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 500;
}

.expand-icon {
    color: #9ca3af;
    transition: transform 0.3s;
    margin-right: 10px;
}

.expand-icon.rotated {
    transform: rotate(180deg);
}

.sub-items-container {
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
    padding: 15px;
}

.sub-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 10px 15px;
    border-radius: 8px;
    border: 1px solid #eee;
    margin-bottom: 8px;
}

.step-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.step-type-icon {
    color: #22c55e;
    font-size: 0.9rem;
}

.step-text {
    display: flex;
    flex-direction: column;
}

.step-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: #333;
}

.step-context {
    font-size: 0.75rem;
    color: #888;
}

.step-date {
    font-size: 0.75rem;
    color: #bbb;
}

.no-subs {
    text-align: center;
    color: #999;
    font-style: italic;
    font-size: 0.9rem;
    padding: 10px;
}

/* Scrollbar premium */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #ddd;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #ccc;
}

.mr-2 {
    margin-right: 0.5rem;
}

/* Transitions */
.expand-enter-active,
.expand-leave-active {
    transition: all 0.3s ease;
    max-height: 1000px;
    overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
    max-height: 0;
    opacity: 0;
}

/* Profil Tab Styles */
.profil-tab-content {
    padding: 10px;
}

.profil-grid {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.profil-main-card {
    display: flex;
    align-items: center;
    gap: 30px;
    padding: 30px;
    background: #fff;
    border-radius: 20px;
    border: 1px solid #f1f5f9;
}

.profil-img-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #f8fafc;
}

.profil-placeholder-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #8A38F5;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 800;
}

.user-fullname {
    font-size: 1.8rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}

.user-email-text {
    font-size: 1.1rem;
    color: #64748b;
    margin: 5px 0 15px;
}

.role-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 15px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 800;
}

.role-pill.utilisateur {
    background: #f1f5f9;
    color: #475569;
}

.role-pill.admin {
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #ffedd5;
}

.role-pill.moderateur {
    background: #f5f3ff;
    color: #6d28d9;
    border: 1px solid #ede9fe;
}

.profil-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.detail-tile {
    padding: 20px;
    background: #f8fafc;
    border-radius: 15px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.tile-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tile-value {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
}

.profil-bio-section {
    padding: 30px;
    background: #fff;
    border-radius: 20px;
    border: 1px solid #f1f5f9;
}

.bio-item {
    margin-bottom: 20px;
}

.bio-item:last-child {
    margin-bottom: 0;
}

.bio-text-premium {
    font-size: 1.05rem;
    line-height: 1.6;
    color: #334155;
    margin-top: 10px;
}

.admin-badge-premium,
.mod-badge-premium {
    font-size: 0.7rem;
    padding: 4px 12px;
    border-radius: 50px;
    font-weight: 800;
    margin-left: 15px;
}

.admin-badge-premium {
    background: #c2410c;
    color: white;
}

.mod-badge-premium {
    background: #6d28d9;
    color: white;
}

.header-badges {
    display: flex;
    align-items: center;
}

.item-visual.question {
    background: #e0e7ff;
    color: #4f46e5;
    font-size: 1.5rem;
}

.question-preview {
    font-size: 0.95rem;
    color: #4b5563;
    margin: 5px 0 10px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.5;
}

.questions-card {
    align-items: flex-start;
}

.context-pill {
    font-size: 0.75rem;
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 4px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 5px;
}
</style>
