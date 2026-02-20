<template>
    <div class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Utilisateurs</h1>
            <div class="header-actions">
                <PremiumButton @click="showNewsletterModal = true" class="newsletter-btn">
                    <i class="fas fa-paper-plane"></i> Rédiger une newsletter
                </PremiumButton>
            </div>
        </div>

        <div class="main-toggles" v-if="userRole === 'admin'">
            <StatusToggle leftLabel="Membres" rightLabel="Administrateurs" v-model="activeTab" />
        </div>

        <div class="content-area">
            <div class="filters-container">
                <AdminFilters v-model="filters" @refresh="onRefresh" :showDates="false" />
                <PremiumButton v-if="activeTab === 'right'" @click="openAddModerator" class="add-mod-btn">
                    <i class="fas fa-user-plus"></i> Ajouter un modérateur
                </PremiumButton>
            </div>

            <div class="type-filters-bar" v-if="activeTab === 'left'">
                <div class="type-filters">
                    <button v-for="opt in activityOptions" :key="opt.value"
                        :class="{ active: filters.activity === opt.value }" @click="filters.activity = opt.value">
                        {{ opt.label }}
                    </button>
                </div>
            </div>

            <!-- Barre d'actions groupées -->
            <transition name="fade">
                <div class="bulk-actions" v-if="selectedUserIds.length > 0 && activeTab === 'left'">
                    <div class="selection-info">
                        <i class="fas fa-check-circle"></i>
                        <span class="selection-count">{{ selectedUserIds.length }} utilisateur(s) sélectionné(s)</span>
                    </div>
                    <div class="bulk-buttons">
                        <PremiumButton @click="showAssignModal = true" class="bulk-offer-btn-premium">
                            Attribuer une offre à la sélection
                        </PremiumButton>
                    </div>
                </div>
            </transition>

            <div class="users-list-container">
                <div class="users-table">
                    <div class="table-header-row" :class="{ 'newsletter-view': filters.activity === 'newsletter' }">
                        <div class="col-check" v-if="activeTab === 'left' && filters.activity !== 'newsletter'">
                            <Checkbox v-model="selectAll" :binary="true" @change="toggleSelectAll" />
                        </div>

                        <template v-if="filters.activity === 'newsletter'">
                            <div class="col-user">Email de l'abonné</div>
                            <div class="col-actions text-right">Actions</div>
                        </template>
                        <template v-else>
                            <div class="col-user">Profil Utilisateur</div>
                            <div class="col-date">Date d'inscription</div>
                            <div class="col-login">Dernière activité</div>
                            <div class="col-actions text-right">Actions</div>
                        </template>
                    </div>

                    <div class="table-body">
                        <transition-group name="list">
                            <div v-for="user in filteredUsers" :key="user.IdUtilisateur" class="table-row"
                                :class="{ 'selected-row': selectedUserIds.includes(user.IdUtilisateur), 'newsletter-row': filters.activity === 'newsletter' }">

                                <template v-if="filters.activity === 'newsletter'">
                                    <div class="col-user">
                                        <div class="user-cell">
                                            <div class="avatar-container shadow-sm">
                                                <div class="avatar-placeholder-premium">
                                                    <i class="fas fa-envelope"></i>
                                                </div>
                                            </div>
                                            <div class="user-info">
                                                <span class="full-name">{{ user.isNewsletterOnly ? 'Abonné Newsletter' :
                                                    (user.profil ? (user.profil.Prenom + ' ' + user.profil.Nom) :
                                                        'Membre') }}</span>
                                                <span class="email-sub">{{ user.Email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-actions">
                                        <div class="action-buttons-group">
                                            <button v-if="user.isNewsletterOnly" class="btn-action delete shadow-sm"
                                                @click="deleteSub(user)" title="Supprimer définitivement">
                                                <i class="fas fa-trash"></i>
                                                <span>Supprimer définitivement</span>
                                            </button>
                                            <button v-else class="btn-action info shadow-sm" @click="openUserInfo(user)"
                                                title="Profil lié">
                                                <i class="fas fa-user"></i>
                                                <span>Voir Profil</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <div class="col-check" v-if="activeTab === 'left'">
                                        <Checkbox v-model="selectedUserIds" :value="user.IdUtilisateur" />
                                    </div>
                                    <div class="col-user">
                                        <div class="user-cell">
                                            <div class="avatar-container shadow-sm">
                                                <img v-if="user.profil?.PhotoProfil" :src="user.profil.PhotoProfil"
                                                    alt="Avatar" />
                                                <div v-else class="avatar-placeholder-premium">
                                                    {{ user.profil?.Prenom?.[0] || user.Email[0].toUpperCase() }}
                                                </div>
                                            </div>
                                            <div class="user-info">
                                                <span class="full-name">{{ user.isNewsletterOnly ? 'Abonné Newsletter' :
                                                    (user.profil ? (user.profil.Prenom + ' ' + user.profil.Nom) :
                                                        'Utilisateur sans nom') }}</span>
                                                <div class="email-row">
                                                    <span class="email-sub">{{ user.Email }}</span>
                                                    <span v-if="user.Role === 'moderateur'"
                                                        class="mod-tag">Modérateur</span>
                                                    <span v-if="user.Role === 'admin'"
                                                        class="admin-tag">Administrateur</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-date">
                                        <span class="date-badge"><i></i> {{
                                            formatDate(user.DateCreation) }}</span>
                                    </div>

                                    <div class="col-login">
                                        <span v-if="user.DerniereConnexion" class="login-badge success">
                                            <i></i> {{ formatDate(user.DerniereConnexion, true) }}
                                        </span>
                                        <span v-else class="login-badge neutral">Jamais connecté</span>
                                    </div>

                                    <div class="col-actions">
                                        <div class="action-buttons-group">
                                            <button v-if="activeTab === 'left'" class="btn-action offer shadow-sm"
                                                @click="openAssignSingle(user)" title="Attribuer une offre">
                                                <span>Attribuer une offre</span>
                                            </button>
                                            <button v-if="user.Role === 'moderateur'"
                                                class="btn-action mod-edit shadow-sm" @click="openEditModerator(user)"
                                                title="Modifier permissions">
                                                <i class="fas fa-user-cog"></i>
                                                <span>Permissions</span>
                                            </button>
                                            <button class="btn-action info shadow-sm" @click="openUserInfo(user)"
                                                title="Détails de l'utilisateur">
                                                <i class="fas fa-eye"></i>
                                                <span>Infos</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </transition-group>

                        <div v-if="filteredUsers.length === 0" class="empty-state">
                            <div class="empty-illustration">
                                <i class="fas fa-users-viewfinder"></i>
                            </div>
                            <h3>Aucun résultat trouvé</h3>
                            <p>Essayez de modifier vos filtres ou vos termes de recherche.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Newsletter -->
        <PremiumModal :isOpen="showNewsletterModal" header="Rédaction Newsletter" width="850px"
            @close="showNewsletterModal = false">
            <form @submit.prevent="submitNewsletter" class="newsletter-editor-form">
                <div class="premium-field">
                    <label>Sujet de la communication</label>
                    <InputText v-model="newsletterForm.subject" placeholder="Ex: Découvrez nos nouveaux programmes..."
                        class="premium-input" required />
                </div>
                <div class="premium-field">
                    <label>Contenu du message</label>
                    <div class="editor-wrapper shadow-sm">
                        <Editor v-model="newsletterForm.content" editorStyle="height: 350px"
                            placeholder="Cher membre, nous avons le plaisir de..." />
                    </div>
                </div>
                <div class="modal-actions-footer">
                    <button type="button" class="btn-link" @click="showNewsletterModal = false">Annuler</button>
                    <PremiumButton type="submit" :loading="newsletterForm.processing" class="btn-send-mail">
                        <i class="fas fa-paper-plane"></i> Envoyer aux abonnés
                    </PremiumButton>
                </div>
            </form>
        </PremiumModal>

        <PremiumModal :isOpen="showAssignModal" header="Attribution d'une offre" width="600px"
            @close="showAssignModal = false">
            <div class="assign-container">
                <div class="target-summary" v-if="singleTargetUser">
                    <div class="target-avatar">
                        <img v-if="singleTargetUser.profil?.PhotoProfil" :src="singleTargetUser.profil.PhotoProfil" />
                        <div v-else class="avatar-placeholder-small">{{ singleTargetUser.profil?.Prenom?.[0] }}</div>
                    </div>
                    <div class="target-text">
                        <span class="value">{{ singleTargetUser.profil ? `${singleTargetUser.profil.Prenom}
                            ${singleTargetUser.profil.Nom}` : singleTargetUser.Email }}</span>
                    </div>
                </div>
                <div class="target-summary multiple" v-else>
                    <div class="target-icon-box">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="target-text">
                        <span class="label">Attribution groupée</span>
                        <span class="value">{{ selectedUserIds.length }} comptes sélectionnés</span>
                    </div>
                </div>

                <div class="selection-box">
                    <div class="custom-search-selector">
                        <span class="search-icon-inside"><i class="fas fa-search"></i></span>
                        <InputText v-model="offerSearch" placeholder="Rechercher une offre commerciale..."
                            class="search-input" @focus="showOfferResults = true" />

                        <div v-if="showOfferResults && filteredOffresForSelect.length > 0"
                            class="search-results-box dark-dropdown scroll-premium">
                            <div v-for="o in filteredOffresForSelect" :key="o.IdOffre" class="search-result-item"
                                @mousedown.prevent="selectOfferForAssign(o)">
                                <img v-if="o.LienPhoto" :src="o.LienPhoto" class="result-img-xl" />
                                <div v-else class="result-icon-box-xl"><i class="fas fa-gift"></i></div>
                                <div class="result-content">
                                    <span class="result-title">{{ o.Titre }}</span>
                                    <span class="result-price">{{ o.ReductionGlobal }}% de réduction</span>
                                </div>
                                <div class="add-indicator"><i class="fas fa-plus"></i></div>
                            </div>
                        </div>
                        <div v-if="showOfferResults" class="dropdown-overlay-closer" @click="showOfferResults = false">
                        </div>
                    </div>

                    <transition name="fade">
                        <div v-if="assignForm.offerId" class="selected-offer-card-premium">
                            <div class="detail-item-xl-card-dark no-hover">
                                <div class="img-container-small">
                                    <img v-if="getSelectedOffer?.LienPhoto" :src="getSelectedOffer.LienPhoto"
                                        class="selected-item-img-xl" />
                                    <div v-else class="selected-item-icon-xl"><i class="fas fa-gift"></i></div>
                                </div>
                                <div class="item-main-content">
                                    <div class="item-info">
                                        <span class="item-name">{{ getSelectedOffer?.Titre }}</span>
                                    </div>
                                    <button type="button" class="remove-offer-btn" @click="assignForm.offerId = null">
                                        Retirer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>

                <div class="modal-actions-footer">
                    <PremiumButton @click="submitAssignOffer" :loading="assignForm.processing"
                        :disabled="!assignForm.offerId" class="btn-confirm-assign">
                        Valider l'attribution
                    </PremiumButton>
                </div>
            </div>
        </PremiumModal>

        <ConfirmModal :isOpen="confirmDeleteVisible" title="Suppression définitive"
            :message="`Voulez-vous vraiment supprimer définitivement l'inscription de ${userToDelete?.Email} ? cette action est irreversible.`"
            confirmLabel="Supprimer" @confirm="handleConfirmDelete" @cancel="confirmDeleteVisible = false" />

        <UserInfoModal :isOpen="showInfoModal" :user="selectedUser" :details="userDetails" :loading="loadingDetails"
            @close="showInfoModal = false" />

        <!-- Modal Modérateur Premium -->
        <PremiumModal :isOpen="showModeratorModal"
            :header="moderatorForm.userId ? 'Configurations Modérateur' : 'Nouveau Modérateur'" width="650px"
            @close="showModeratorModal = false">
            <div class="mod-modal-premium">
                <div v-if="!moderatorForm.userId" class="search-step">
                    <div class="step-header">
                        <span class="step-number">01</span>
                        <h3>Sélectionner un utilisateur</h3>
                    </div>
                    <div class="search-box-premium">
                        <i class="fas fa-search search-icon"></i>
                        <InputText v-model="modSearch" @input="onModSearch" placeholder="Nom, prénom ou email..."
                            class="premium-search-input" />
                        <div v-if="loadingModSearch" class="loader-inline"><i class="fas fa-spinner fa-spin"></i></div>
                    </div>

                    <div v-if="modSearchResults.length > 0" class="results-grid-premium scroll-premium">
                        <div v-for="u in modSearchResults" :key="u.IdUtilisateur" class="result-card-premium"
                            @click="selectModUser(u)">
                            <div class="user-avatar-premium">
                                <img v-if="u.profil?.PhotoProfil" :src="u.profil.PhotoProfil" />
                                <div v-else class="avatar-init">{{ u.profil?.Prenom?.[0] || u.Email[0] }}</div>
                            </div>
                            <div class="user-meta-premium">
                                <span class="u-name">{{ u.profil ? u.profil.Prenom + ' ' + u.profil.Nom : 'Membre'
                                    }}</span>
                                <span class="u-email">{{ u.Email }}</span>
                            </div>
                            <div class="select-indicator"><i class="fas fa-chevron-right"></i></div>
                        </div>
                    </div>
                </div>

                <div v-else class="config-step">
                    <div class="selected-user-banner">
                        <div class="banner-avatar">
                            <div class="avatar-init highlight">{{ moderatorForm.userLabel[0] }}</div>
                        </div>
                        <div class="banner-info">
                            <span class="banner-label">Utilisateur sélectionné</span>
                            <span class="banner-value">{{ moderatorForm.userLabel }}</span>
                        </div>
                        <button v-if="!moderatorForm.id" class="btn-change-user" @click="moderatorForm.userId = null">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>

                    <div class="permissions-container-premium">
                        <div class="step-header">
                            <span class="step-number">02</span>
                            <h3>Définir les permissions d'accès</h3>
                        </div>

                        <div class="permissions-flex-grid">
                            <div v-for="menu in adminMenus" :key="menu.id" class="perm-card-modern">
                                <div class="perm-icon-box">
                                    <i :class="menu.icon"></i>
                                </div>
                                <div class="perm-info-box">
                                    <span class="perm-name">{{ menu.label }}</span>
                                    <span class="perm-desc">Accès au menu {{ menu.label }}</span>
                                </div>
                                <div class="perm-toggle-box">
                                    <PremiumSlideToggle
                                        :modelValue="moderatorForm.permissions.includes(menu.id) ? 'Publié' : 'Dépublié'"
                                        @update:modelValue="(val) => {
                                            if (val === 'Publié') { if (!moderatorForm.permissions.includes(menu.id)) moderatorForm.permissions.push(menu.id); }
                                            else { moderatorForm.permissions = moderatorForm.permissions.filter(p => p !== menu.id); }
                                        }" checkedValue="Publié" uncheckedValue="Dépublié" checkedLabel="Activé"
                                        uncheckedLabel="Désactivé" activeColor="#8A38F5" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mod-footer-actions">
                        <button v-if="moderatorForm.userId" type="button" class="btn-destructive-link"
                            @click="removeModerator">
                            <i class="fas fa-user-minus"></i> Révoquer tous les accès
                        </button>
                        <PremiumButton @click="submitModerator" :loading="moderatorForm.processing"
                            class="btn-save-mod">
                            <i class="fas fa-check-double"></i> Enregistrer les configurations
                        </PremiumButton>
                    </div>
                </div>
            </div>
        </PremiumModal>

        <!-- Modal Admin Lock (Password Prompt) -->
        <PremiumModal :isOpen="showAdminLockModal" header="Accès Sécurisé" width="400px"
            @close="showAdminLockModal = false">
            <div class="admin-lock-content">
                <div class="lock-icon-container">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Vérification requise</h3>
                <p>Veuillez confirmer votre mot de passe administrateur pour accéder à ces informations.</p>

                <form @submit.prevent="verifyAdminPassword" class="lock-form">
                    <div class="premium-field">
                        <InputText type="password" v-model="adminPromptPassword" placeholder="Votre mot de passe..."
                            class="premium-input-lock" autofocus required />
                    </div>
                    <PremiumButton type="submit" :loading="verifyingAdmin" class="btn-verify-lock">
                        Déverrouiller
                    </PremiumButton>
                </form>
            </div>
        </PremiumModal>

        <!-- Modal Admin Settings -->
        <PremiumModal :isOpen="showAdminSettingsModal" header="Profil Administrateur" width="550px"
            @close="showAdminSettingsModal = false">
            <form @submit.prevent="submitAdminProfile" class="admin-settings-form">
                <div class="settings-section">
                    <h4>Informations Générales</h4>
                    <div class="form-grid-2">
                        <div class="premium-field">
                            <label>Prénom</label>
                            <InputText v-model="adminProfileForm.prenom" class="premium-input" />
                        </div>
                        <div class="premium-field">
                            <label>Nom</label>
                            <InputText v-model="adminProfileForm.nom" class="premium-input" />
                        </div>
                    </div>
                    <div class="premium-field">
                        <label>Email Administrateur</label>
                        <InputText v-model="adminProfileForm.email" class="premium-input" />
                    </div>
                </div>

                <div class="settings-section divider-top">
                    <h4>Sécurité & Mot de passe</h4>
                    <p class="settings-hint">Laissez vide pour conserver le mot de passe actuel.</p>
                    <div class="premium-field">
                        <label>Nouveau mot de passe</label>
                        <InputText type="password" v-model="adminProfileForm.password" class="premium-input" />
                    </div>
                    <div class="premium-field">
                        <label>Confirmer le mot de passe</label>
                        <InputText type="password" v-model="adminProfileForm.password_confirmation"
                            class="premium-input" />
                    </div>
                </div>

                <div class="modal-actions-footer">
                    <button type="button" class="btn-link" @click="showAdminSettingsModal = false">Annuler</button>
                    <PremiumButton type="submit" :loading="adminProfileForm.processing" class="btn-save-settings">
                        Enregistrer les modifications
                    </PremiumButton>
                </div>
            </form>
        </PremiumModal>

        <Toast />
    </div>
</template>

<script setup>
import { ref, computed, watchEffect } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import StatusToggle from '../../components/ui/StatusToggle.vue';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Editor from 'primevue/editor';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import PremiumButton from '../../components/ui/PremiumButton.vue';
import PremiumModal from '../../components/ui/PremiumModal.vue';
import AdminFilters from '../../components/admin/AdminFilters.vue';
import ConfirmModal from '../../components/ui/ConfirmModal.vue';
import UserInfoModal from '../../components/admin/UserInfoModal.vue';
import axios from 'axios';
import PremiumSlideToggle from '../../components/ui/PremiumSlideToggle.vue';

const props = defineProps({
    utilisateurs: { type: Array, default: () => [] },
    offres: { type: Array, default: () => [] }
});

const page = usePage();
const userRole = computed(() => page.props.auth.user?.Role);

const toast = useToast();
const activeTab = ref('left');

watchEffect(() => {
    if (userRole.value === 'moderateur' && activeTab.value === 'right') {
        activeTab.value = 'left';
    }
});

const refreshing = ref(false);
const filters = ref({ search: '', dateStart: null, dateEnd: null, activity: 'Tous' });

const adminMenus = [
    { id: 'programmes', label: 'Programmes', icon: 'fas fa-graduation-cap' },
    { id: 'consultations', label: 'Consultations', icon: 'fas fa-comments' },
    { id: 'lives', label: 'Lives', icon: 'fas fa-video' },
    { id: 'coachings', label: 'Coachings', icon: 'fas fa-headset' },
    { id: 'offres', label: 'Offres', icon: 'fas fa-gift' },
    { id: 'utilisateurs', label: 'Utilisateurs', icon: 'fas fa-users' },
    { id: 'temoignages', label: 'Témoignages', icon: 'fas fa-star' },
];

// Selection
const selectedUserIds = ref([]);
const selectAll = ref(false);

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedUserIds.value = filteredUsers.value.map(u => u.IdUtilisateur);
    } else {
        selectedUserIds.value = [];
    }
};

const activityOptions = [
    { label: 'Tous', value: 'Tous' },
    { label: 'Ayant acheter un programme', value: 'bought_program' },
    { label: 'Ayant reserver un séminaire', value: 'reserved_seminar' },
    { label: 'Ayant reserver un coaching', value: 'reserved_coaching' },
    { label: 'Ayant acheter une offre', value: 'bought_offer' },
    { label: 'Ayant souscrit à la newsletter', value: 'newsletter' },
];

const filteredUsers = computed(() => {
    let list = props.utilisateurs || [];
    const targetRole = activeTab.value === 'left' ? 'utilisateur' : ['admin', 'moderateur'];

    if (activeTab.value === 'left' && filters.value.activity !== 'Tous') {
        switch (filters.value.activity) {
            case 'bought_program':
                list = list.filter(u => u.hasBoughtProgram && !u.isNewsletterOnly);
                break;
            case 'reserved_seminar':
                list = list.filter(u => u.hasReservedSeminar && !u.isNewsletterOnly);
                break;
            case 'reserved_coaching':
                list = list.filter(u => u.hasReservedCoaching && !u.isNewsletterOnly);
                break;
            case 'bought_offer':
                list = list.filter(u => u.hasBoughtOffer && !u.isNewsletterOnly);
                break;
            case 'newsletter':
                list = list.filter(u => u.Newsletter);
                break;
        }
    } else {
        // Mode "Tous" ou Admin : on cache les abonnés newsletter sans compte
        list = list.filter(u => !u.isNewsletterOnly);
    }

    // Filtrage par rôle strict pour séparer les onglets
    if (Array.isArray(targetRole)) {
        list = list.filter(u => targetRole.includes(u.Role) || u.isNewsletterOnly);
    } else {
        list = list.filter(u => u.Role === targetRole || u.isNewsletterOnly);
    }

    if (filters.value.search) {
        const s = filters.value.search.toLowerCase();
        list = list.filter(u =>
            u.Email.toLowerCase().includes(s) ||
            (u.profil?.Prenom?.toLowerCase().includes(s)) ||
            (u.profil?.Nom?.toLowerCase().includes(s))
        );
    }
    return list;
});

// Forms
const newsletterForm = useForm({ subject: '', content: '' });
const assignForm = useForm({ userIds: [], offerId: null });

// Offer Selection
const offerSearch = ref('');
const showOfferResults = ref(false);

const filteredOffresForSelect = computed(() => {
    if (!offerSearch.value) return props.offres;
    const q = offerSearch.value.toLowerCase();
    return props.offres.filter(o => o.Titre.toLowerCase().includes(q));
});

const selectOfferForAssign = (offer) => {
    assignForm.offerId = offer.IdOffre;
    showOfferResults.value = false;
    offerSearch.value = offer.Titre;
};

const getSelectedOffer = computed(() => {
    return props.offres.find(o => o.IdOffre === assignForm.offerId);
});

const getOfferTitle = (id) => {
    const o = props.offres.find(off => off.IdOffre === id);
    return o ? o.Titre : '';
};

// Modals
const showNewsletterModal = ref(false);
const showAssignModal = ref(false);
const showInfoModal = ref(false);
const singleTargetUser = ref(null);
const selectedUser = ref(null);
const confirmDeleteVisible = ref(false);
const userToDelete = ref(null);
const userDetails = ref({ purchases: [], progressions: [], coachings: [], assignedOffers: [], questions: [] });
const loadingDetails = ref(false);

// Moderator
const showModeratorModal = ref(false);
const modSearch = ref('');
const modSearchResults = ref([]);
const loadingModSearch = ref(false);
const moderatorForm = useForm({
    userId: null,
    permissions: [],
    isModerator: true,
    userLabel: ''
});

const onModSearch = async () => {
    if (modSearch.value.length < 2) {
        modSearchResults.value = [];
        return;
    }
    loadingModSearch.value = true;
    try {
        const res = await axios.get('/admin/utilisateurs/search', { params: { q: modSearch.value } });
        modSearchResults.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loadingModSearch.value = false;
    }
};

const selectModUser = (u) => {
    moderatorForm.userId = u.IdUtilisateur;
    moderatorForm.userLabel = (u.profil?.Prenom || '') + ' ' + (u.profil?.Nom || '') + ' (' + u.Email + ')';
    modSearchResults.value = [];
    modSearch.value = '';
};

const openAddModerator = () => {
    moderatorForm.reset();
    moderatorForm.userId = null;
    moderatorForm.permissions = [];
    moderatorForm.isModerator = true;
    showModeratorModal.value = true;
};

const openEditModerator = (user) => {
    moderatorForm.userId = user.IdUtilisateur;
    moderatorForm.userLabel = (user.profil?.Prenom || '') + ' ' + (user.profil?.Nom || '') + ' (' + user.Email + ')';
    moderatorForm.permissions = user.PermissionsModerateur || [];
    moderatorForm.isModerator = true;
    showModeratorModal.value = true;
};

const submitModerator = () => {
    moderatorForm.post('/admin/utilisateurs/update-moderator', {
        onSuccess: () => {
            showModeratorModal.value = false;
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Modérateur mis à jour', life: 3000 });
        }
    });
};

const removeModerator = () => {
    moderatorForm.isModerator = false;
    submitModerator();
};

const onRefresh = () => {
    refreshing.value = true;
    router.reload({
        only: ['utilisateurs'],
        onFinish: () => {
            refreshing.value = false;
            toast.add({ severity: 'success', summary: 'Mis à jour', detail: 'Affichage actualisé', life: 2000 });
        }
    });
};

const openAssignSingle = (user) => {
    singleTargetUser.value = user;
    assignForm.userIds = [user.IdUtilisateur];
    assignForm.offerId = null;
    offerSearch.value = '';
    showAssignModal.value = true;
};

// Admin Settings & Security
const showAdminLockModal = ref(false);
const showAdminSettingsModal = ref(false);
const adminPromptPassword = ref('');
const verifyingAdmin = ref(false);

const adminProfileForm = useForm({
    email: '',
    prenom: '',
    nom: '',
    password: '',
    password_confirmation: ''
});

const openAdminLock = (user) => {
    selectedUser.value = user;
    adminPromptPassword.value = '';
    showAdminLockModal.value = true;
};

const verifyAdminPassword = async () => {
    verifyingAdmin.value = true;
    try {
        const res = await axios.post('/admin/admin-verify-password', { password: adminPromptPassword.value });
        if (res.data.success) {
            showAdminLockModal.value = false;
            adminProfileForm.email = selectedUser.value.Email;
            adminProfileForm.prenom = selectedUser.value.profil?.Prenom || '';
            adminProfileForm.nom = selectedUser.value.profil?.Nom || '';
            adminProfileForm.password = '';
            adminProfileForm.password_confirmation = '';
            showAdminSettingsModal.value = true;
        }
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Mot de passe incorrect', life: 3000 });
    } finally {
        verifyingAdmin.value = false;
    }
};

const submitAdminProfile = () => {
    adminProfileForm.post('/admin/admin-update-profile', {
        onSuccess: () => {
            showAdminSettingsModal.value = false;
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Profil administrateur mis à jour', life: 3000 });
        }
    });
};

const openUserInfo = async (user) => {
    if (user.Role === 'admin') {
        openAdminLock(user);
        return;
    }
    selectedUser.value = user;
    showInfoModal.value = true;
    loadingDetails.value = true;
    try {
        const response = await axios.get(`/admin/utilisateurs/${user.IdUtilisateur}/details`);
        userDetails.value = response.data;
    } catch (e) {
        console.error(e);
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les détails', life: 3000 });
    } finally {
        loadingDetails.value = false;
    }
};

const submitNewsletter = () => {
    newsletterForm.post('/admin/newsletter/send', {
        onSuccess: () => {
            showNewsletterModal.value = false;
            newsletterForm.reset();
            toast.add({ severity: 'success', summary: 'Newsletter envoyée', detail: 'Le message est en cours d\'expédition', life: 3000 });
        }
    });
};

const submitAssignOffer = () => {
    const ids = singleTargetUser.value ? [singleTargetUser.value.IdUtilisateur] : selectedUserIds.value;

    router.post('/admin/utilisateurs/assign-offer', {
        offerId: assignForm.offerId,
        userIds: ids
    }, {
        onSuccess: () => {
            showAssignModal.value = false;
            selectedUserIds.value = [];
            selectAll.value = false;
            singleTargetUser.value = null;
            toast.add({ severity: 'success', summary: 'Attribution réussie', detail: 'Les accès ont été activés', life: 3000 });
        }
    });
};

const deleteSub = (user) => {
    userToDelete.value = user;
    confirmDeleteVisible.value = true;
};

const handleConfirmDelete = () => {
    if (!userToDelete.value) return;

    router.delete(`/admin/newsletter/subscription/${userToDelete.value.IdUtilisateur}`, {
        onSuccess: () => {
            confirmDeleteVisible.value = false;
            userToDelete.value = null;
            toast.add({ severity: 'success', summary: 'Supprimé', detail: 'Inscription retirée avec succès', life: 3000 });
        }
    });
};

const formatDate = (dateString, withTime = false) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const opt = { day: '2-digit', month: 'short', year: 'numeric' };
    if (withTime) { opt.hour = '2-digit'; opt.minute = '2-digit'; }
    return date.toLocaleDateString('fr-FR', opt);
};
</script>

<style scoped>
.admin-content {
    padding: 40px;
    background-color: #fcfcfc;
    min-height: 100vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.page-title {
    font-size: 3rem;
    font-weight: 600;
    margin: 0;
}

.header-actions {
    display: flex;
}

.newsletter-btn {
    background: #0f172a;
    color: white;
    border-radius: 15px;
    font-weight: 700;
    transition: transform 0.2s;
}

.newsletter-btn:hover {
    transform: translateY(-2px);
}

.main-toggles {
    margin-bottom: 40px;
}

.filters-container {
    display: flex;
    justify-content: center;
    margin-bottom: 30px;
}

.type-filters-bar {
    display: flex;
    justify-content: center;
    margin: 20px 0;
    margin-bottom: 40px;
}

.type-filters {
    display: flex;
    background: #eee;
    padding: 5px;
    border-radius: 15px;
    gap: 5px;
}

.type-filters button {
    padding: 8px 30px;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    background: transparent;
    color: #666;
    transition: all 0.2s;
}

.type-filters button.active {
    background: white;
    color: #111;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Barre d'actions groupées */
.bulk-actions {
    background: #0f172a;
    color: white;
    padding: 12px 25px;
    border-radius: 14px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    animation: slideDown 0.3s ease-out;
}

.selection-count {
    margin-left: 2vw;
    font-size: 1.2rem;
}

.bulk-offer-btn-premium {
    background: white !important;
    color: #0f172a !important;
    font-weight: 700 !important;
    border-radius: 20px !important;
}

/* Table */
.users-list-container {
    background: white;
    border-radius: 20px;
    border: 1px solid #edf2f7;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
}

.table-header-row {
    display: flex;
    padding: 18px 25px;
    background: #f8fafc;
    border-bottom: 1px solid #edf2f7;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 1px;
    color: #64748b;
}

.table-row {
    display: flex;
    padding: 12px 25px;
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s;
}

.table-row:hover {
    background: #fdfdfd;
}

.selected-row {
    background: #f8faff;
}

.col-check {
    width: 45px;
}

.col-user {
    flex: 2;
}

.col-date {
    flex: 1.2;
}

.col-login {
    flex: 1.5;
}

.col-actions {
    flex: 1.5;
    text-align: right;
}

/* Newsletter View Custom Layout */
.newsletter-view .col-user,
.newsletter-row .col-user {
    flex: 4;
}

.newsletter-view .col-actions,
.newsletter-row .col-actions {
    flex: 1;
}

.btn-action.delete span {
    font-weight: 700;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 15px;
}

.avatar-container {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    background: #f1f5f9;
    flex-shrink: 0;
    border: 2px solid white;
}

.avatar-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder-premium {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #0f172a;
    color: white;
    font-weight: 800;
    font-size: 1.1rem;
}

.full-name {
    font-weight: 600;
    font-size: 1.2rem;
    line-height: 1.1;
    display: block;
}

.email-sub {
    font-size: 1rem;
    color: #606060;
    font-weight: 500;
}

.date-badge {
    font-size: 0.9rem;
    font-weight: 500;
    color: #64748b;
}

.login-badge {
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
}

.login-badge.success {
    background: #dcfce7;
    color: #166534;
}

.login-badge.neutral {
    background: #f1f5f9;
    color: #64748b;
}

.btn-action {
    border: none;
    padding: 1.6vh 1.2vw;
    border-radius: 15px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: 0.9rem;
}

.btn-action.offer {
    background: #0f172a;
    color: white;
}

.btn-action.info {
    background: white;
    color: #0f172a;
    border: 1px solid #e2e8f0;
}

.btn-action.delete {
    background: #d80000;
    color: #ffffff;
}

.btn-action:hover {
    transform: scale(1.05);
}

.action-buttons-group {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

/* Modals */
.premium-field {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.premium-field label {
    font-weight: 700;
    color: #0f172a;
    font-size: 0.95rem;
}

.premium-input {
    border-radius: 10px !important;
    padding: 12px !important;
    border-color: #e2e8f0 !important;
}

.editor-wrapper {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.modal-actions-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 20px;
}

.btn-link {
    background: none;
    border: none;
    font-weight: 700;
    color: #94a3b8;
    cursor: pointer;
}

/* Profile View */
.profile-dashboard {
    padding: 0;
}

.profile-header {
    background: white;
    padding: 30px;
    border-radius: 20px;
    border: 1px solid #f1f5f9;
}

.profile-main-info {
    display: flex;
    gap: 25px;
    align-items: center;
}

.profile-avatar-large {
    width: 90px;
    height: 90px;
    border-radius: 25px;
    overflow: hidden;
    border: 4px solid #f8fafc;
}

.profile-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-initials {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #0f172a;
    color: white;
    font-size: 2.5rem;
    font-weight: 800;
}

.profile-text h2 {
    margin: 0;
    font-size: 1.8rem !important;
    line-height: 1;
    font-weight: 600 !important;
}

.email-link {
    color: #64748b;
    font-weight: 500;
    font-size: 1.1rem;
    margin-top: 2px;
}

.badge-row {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.role-pill {
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
}

.role-pill.admin {
    background: #fee2e2;
    color: #991b1b;
}

.role-pill.utilisateur {
    background: #dcfce7;
    color: #166534;
}

.newsletter-pill {
    background: #eff6ff;
    color: #1d4ed8;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 800;
}

.stats-overview-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.stat-info-card {
    background: #f8fafc;
    padding: 15px;
    border-radius: 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stat-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
}

.history-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: white;
    border-radius: 12px;
    border: 1px solid #f1f5f9;
    margin-bottom: 10px;
}

.cmd-ref {
    font-weight: 700;
    color: #0f172a;
    display: block;
}

.cmd-date {
    font-size: 0.8rem;
    color: #94a3b8;
    font-weight: 500;
}

.cmd-total {
    font-weight: 800;
    font-size: 1.1rem;
}

.empty-state {
    padding: 60px;
    text-align: center;
    color: #94a3b8;
}

.empty-illustration {
    font-size: 3.5rem;
    margin-bottom: 20px;
    opacity: 0.2;
}

/* Custom Search UI - DARK DROPDOWN */
.custom-search-selector {
    position: relative;
    width: 100%;
}

.search-icon-inside {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #8A38F5;
    opacity: 0.5;
    z-index: 2;
}

.search-input {
    width: 100% !important;
    padding-left: 45px !important;
    border-radius: 15px !important;
    height: 50px;
    font-size: 1rem;
    border: 2px solid #eee !important;
}

.search-results-box.dark-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    scrollbar-width: none;
    right: 0;
    background: #111;
    border: 1px solid #333;
    border-radius: 25px;
    max-height: 28vh;
    overflow-y: auto;
    z-index: 2001;
    /* Higher than modal */
    margin-top: 10px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.search-result-item {
    display: flex;
    align-items: center;
    padding: 15px;
    gap: 20px;
    cursor: pointer;
    transition: all 0.2s;
    border-bottom: 1px solid #222;
    position: relative;
    min-height: 80px;
}

.search-result-item:hover {
    background: #1a1a1a;
}

.result-img-xl {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    object-fit: cover;
}

.result-icon-box-xl {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    background: #222;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8A38F5;
    font-size: 1.2rem;
}

.result-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.result-title {
    font-weight: 600;
    color: #fff;
    font-size: 1.1rem;
}

.result-price {
    font-size: 0.85rem;
    color: #999;
}

.add-indicator {
    color: #8A38F5;
    font-size: 1.2rem;
    opacity: 0.4;
}

.offer-badge-premium {
    background: #f1f5f9;
    padding: 12px 20px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    font-weight: 700;
    color: #0f172a;
    border: 1px solid #e2e8f0;
}

.newsletter-editor-form .premium-field {
    margin-top: 25px;
}

.newsletter-editor-form .modal-actions-footer {
    margin-top: 35px;
}

.newsletter-btn i,
.btn-send-mail i,
.btn-confirm-assign i,
.selection-info i,
.date-badge i,
.login-badge i,
.newsletter-pill i,
.remove-offer-btn i {
    margin-right: 10px;
}



.date-badge i::before {
    content: "\f133";
    font-family: "Font Awesome 5 Free";
    font-weight: 400;
}

.login-badge i::before {
    content: "\f017";
    font-family: "Font Awesome 5 Free";
    font-weight: 400;
}

.newsletter-pill i::before {
    content: "\f0e0";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
}

.remove-offer-btn i::before {
    content: "\f00d";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
}

.premium-input {
    width: 100%;
}

.assign-container {
    display: flex;
    flex-direction: column;
    gap: 30px;
    padding: 20px 0;
}

.selection-box {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.btn-confirm-assign {
    width: 100%;
    padding: 1.5vh 3vw !important;
}

.selected-offer-preview {
    margin-top: 15px;
}

.stats-overview-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 25px;
}

.tabs-minimal {
    margin-top: 35px;
}

.history-explorer {
    margin-top: 20px;
}

.detail-item-xl-card-dark {
    display: flex;
    height: 25vh;
    background: #111;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s;
    width: 100%;
}

.detail-item-xl-card-dark.no-hover:hover {
    transform: none;
}

.img-container-small {
    width: 18vw;
    flex-shrink: 0;
}

.selected-item-img-xl {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.9;
}

.selected-item-icon-xl {
    width: 100%;
    height: 100%;
    background: #1a1a1a;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8A38F5;
    font-size: 2rem;
}

.item-main-content {
    flex: 1;
    padding: 15px 25px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.item-name {
    font-weight: 600;
    margin-top: 5vh;
    line-height: 1.1;
    color: #fff;
    font-size: 1.3rem;
}

.item-type-badge {
    display: inline-block;
    align-self: flex-start;
    color: #8A38F5;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.remove-offer-btn {
    align-self: flex-end;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.remove-offer-btn:hover {
    background: #ff4d4d;
}

.target-summary {
    display: flex;
    align-items: center;
    gap: 15px;
}

.target-avatar {
    width: 5vw;
    margin-left: 2vw;
    height: 5vw;
    border-radius: 50px;
    overflow: hidden;
    flex-shrink: 0;
}

.target-avatar img {
    width: 100%;
    height: 100%;

    object-fit: cover;
}

.target-text {
    display: flex;
    flex-direction: column;
}

.target-text .label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
}

.target-text .value {
    margin-left: 1.1vw;
    font-weight: 500;
    font-size: 1.3rem;
    line-height: 1.1;
    color: #0f172a;
}

.dropdown-overlay-closer {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 2000;
    background: transparent;
}

/* Animations */
@keyframes slideDown {
    from {
        transform: translateY(-10px);
        opacity: 0;
    }

    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s, transform 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}

.add-mod-btn {
    background: #0f172a !important;
    color: white !important;
    margin-left: 1.1vw;
    border-radius: 12px !important;
    font-size: 0.9rem !important;
}

.email-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mod-tag {
    background: #e2e8f0;
    color: #475569;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
}

.btn-action.mod-edit {
    background: #f1f5f9;
    color: #0f172a;
    border: 1px solid #e2e8f0;
}

/* Moderator Modal Premium Redesign */
.mod-modal-premium {
    padding: 10px 5px;
}

.step-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.step-number {
    background: #8A38F5;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.8rem;
}

.step-header h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #111;
}

.search-box-premium {
    position: relative;
    margin-bottom: 25px;
}

.search-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.premium-search-input {
    width: 100% !important;
    padding: 15px 15px 15px 45px !important;
    border-radius: 15px !important;
    border: 1px solid #e2e8f0 !important;
    background: #f8fafc !important;
    font-size: 1rem !important;
    transition: all 0.3s !important;
}

.premium-search-input:focus {
    background: #fff !important;
    border-color: #8A38F5 !important;
    box-shadow: 0 0 0 4px rgba(138, 56, 245, 0.1) !important;
}

.results-grid-premium {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    max-height: 350px;
    overflow-y: auto;
    padding-right: 5px;
}

.result-card-premium {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 15px;
    cursor: pointer;
    transition: all 0.2s;
}

.result-card-premium:hover {
    border-color: #8A38F5;
    background: #faf5ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.user-avatar-premium {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    overflow: hidden;
    background: #f1f5f9;
}

.user-avatar-premium img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-init {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #475569;
    background: #e2e8f0;
}

.avatar-init.highlight {
    background: #8A38F5;
    color: white;
}

.user-meta-premium {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.u-name {
    font-weight: 700;
    color: #1e293b;
    font-size: 0.95rem;
}

.u-email {
    font-size: 0.8rem;
    color: #64748b;
}

.select-indicator {
    color: #cbd5e1;
}

.selected-user-banner {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #111;
    padding: 15px 20px;
    border-radius: 18px;
    margin-bottom: 30px;
}

.banner-avatar .avatar-init {
    width: 40px;
    height: 40px;
}

.banner-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.banner-label {
    font-size: 0.65rem;
    color: #94a3b8;
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: 0.5px;
}

.banner-value {
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
}

.btn-change-user {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-change-user:hover {
    background: #8A38F5;
}

.permissions-flex-grid {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 30px;
}

.perm-card-modern {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 15px;
    background: #fff;
    border: 1px solid #f1f5f9;
    border-radius: 15px;
}

.perm-icon-box {
    width: 40px;
    height: 40px;
    background: #f8fafc;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8A38F5;
    font-size: 1.1rem;
}

.perm-icon-box i {
    color: #8A38F5;
}

.perm-info-box {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.perm-name {
    font-weight: 700;
    color: #1e293b;
    font-size: 0.95rem;
}

.perm-desc {
    font-size: 0.75rem;
    color: #94a3b8;
}

.mod-footer-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #f1f5f9;
    padding-top: 25px;
    margin-top: 10px;
}

.btn-destructive-link {
    background: none;
    border: none;
    color: #ef4444;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-destructive-link:hover {
    text-decoration: underline;
}

/* Admin Lock Styles */
.admin-lock-content {
    text-align: center;
    padding: 20px 10px;
}

.lock-icon-container {
    width: 80px;
    height: 80px;
    background: #faf5ff;
    color: #8A38F5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.22rem;
    margin: 0 auto 20px;
}

.admin-lock-content h3 {
    margin: 0 0 10px;
    font-size: 1.4rem;
    font-weight: 800;
    color: #111;
}

.admin-lock-content p {
    color: #64748b;
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 25px;
}

.premium-input-lock {
    width: 100% !important;
    padding: 15px !important;
    border-radius: 12px !important;
    text-align: center !important;
    font-size: 1.2rem !important;
    letter-spacing: 5px !important;
}

.btn-verify-lock {
    width: 100% !important;
    margin-top: 20px !important;
}

/* Admin Settings Styles */
.admin-settings-form {
    padding: 10px 5px;
}

.settings-section {
    margin-bottom: 30px;
}

.settings-section h4 {
    font-size: 0.95rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.settings-hint {
    font-size: 0.8rem;
    color: #94a3b8;
    margin-bottom: 20px;
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.divider-top {
    border-top: 1px solid #f1f5f9;
    padding-top: 30px;
}

.premium-field label {
    display: block;
    font-weight: 700;
    font-size: 0.85rem;
    color: #475569;
    margin-bottom: 8px;
}

/* Scrollbar Modérateur */
.scroll-premium::-webkit-scrollbar {
    width: 5px;
}

.scroll-premium::-webkit-scrollbar-track {
    background: transparent;
}

.scroll-premium::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

.mod-tag,
.admin-tag {
    display: inline-block !important;
    padding: 2px 10px !important;
    border-radius: 6px !important;
    font-size: 0.65rem !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    margin-left: 8px !important;
    vertical-align: middle !important;
}

.mod-tag {
    background: #f5f3ff !important;
    color: #7c3aed !important;
    border: 1px solid #ddd6fe !important;
}

.admin-tag {
    background: #fffbeb !important;
    color: #b45309 !important;
    border: 1px solid #fde68a !important;
}
</style>
