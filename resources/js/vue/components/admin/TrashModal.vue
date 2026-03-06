<template>
    <PremiumModal :isOpen="visible" header="Corbeille" width="70vw" @close="$emit('close')">
        <div class="tabs-header">
            <button v-for="tab in tabs" :key="tab.id" class="tab-btn" :class="{ active: currentTab === tab.id }"
                @click="currentTab = tab.id">
                {{ tab.label }}
            </button>
        </div>

        <div class="modal-body-content">
            <div v-if="loading" class="loading-state">
                <i class="fas fa-spinner fa-spin"></i> Chargement...
            </div>

            <div v-else class="items-list">
                <div v-if="getActiveItems.length === 0" class="empty-state">
                    <i class="fas fa-trash-restore empty-icon"></i>
                    <p>La corbeille est vide pour cette catégorie.</p>
                </div>

                <div v-else class="list-container">
                    <div v-for="item in getActiveItems"
                        :key="item.IdProgrammeFormation || item.IdLive || item.IdOffre || item.IdConsultation || item.IdTemoignage || item.IdTypeCoaching || item.IdReponseConsultation || item.IdCategorie || item.id"
                        class="trash-item-container">
                        <div class="trash-item"
                            :class="{ 'expanded': currentTab === 'programs' && expandedItems.has(item.IdProgrammeFormation) }"
                            @click="currentTab === 'programs' ? toggleExpand(item.IdProgrammeFormation) : null">

                            <div class="item-visual">
                                <img v-if="item.LienPhoto" :src="item.LienPhoto" class="program-thumb" />
                                <div v-else class="program-thumb-placeholder">
                                    <i :class="getIconForTab(currentTab)"></i>
                                </div>
                            </div>

                            <div class="item-details">
                                <h3 class="item-title">{{ item.display_title || item.Titre || item.NomDeType }}</h3>
                                <div class="item-meta">
                                    <span v-if="item.trash_status === 'deleted_program'"
                                        class="badge badge-deleted">Programme Supprimé</span>
                                    <span v-else-if="item.trash_status === 'has_deleted_content'"
                                        class="badge badge-warning">Contenu Supprimé</span>
                                    <span v-else class="badge badge-deleted">{{ item.trash_type ===
                                        'consultation_reponse' ? 'Archive Supprimée' : getBadgeLabel(currentTab)
                                        }}</span>

                                    <span v-if="item.deleted_lessons_count" class="meta-info">
                                        {{ item.deleted_lessons_count }} leçon(s) supprimée(s)
                                    </span>
                                    <span v-if="item.deleted_themes_count" class="meta-info">
                                        {{ item.deleted_themes_count }} chapitre(s) supprimé(s)
                                    </span>
                                </div>
                            </div>

                            <div class="item-actions">
                                <button v-if="currentTab !== 'programs' || item.trash_status === 'deleted_program'"
                                    class="action-btn restore" title="Restaurer"
                                    @click.stop="handleGeneralRestore(item)">
                                    <i class="fas fa-undo"></i> Restaurer
                                </button>
                                <i v-if="currentTab === 'programs' && (item.lecons?.length || item.themes?.length)"
                                    class="fas fa-chevron-down expand-icon"
                                    :class="{ rotated: expandedItems.has(item.IdProgrammeFormation) }"></i>
                            </div>
                        </div>

                        <!-- Sub-items for programs -->
                        <div v-if="currentTab === 'programs' && expandedItems.has(item.IdProgrammeFormation)"
                            class="sub-items-container">
                            <div v-if="item.lecons && item.lecons.length > 0">
                                <h4 class="sub-group-title">Leçons Supprimées</h4>
                                <div v-for="lesson in item.lecons" :key="lesson.IdLecon" class="sub-item"
                                    @click="openLessonDetails(lesson, item.IdProgrammeFormation)">
                                    <span class="sub-type-icon"><i class="fas fa-play-circle"></i></span>
                                    <span class="sub-item-title">{{ lesson.Titre }}</span>
                                    <button class="action-btn-small restore"
                                        @click.stop="handleRestore('lesson', lesson.IdLecon)">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            </div>

                            <div v-if="item.themes && item.themes.length > 0">
                                <h4 class="sub-group-title">Chapitres Supprimés</h4>
                                <div v-for="theme in item.themes" :key="theme.IdTheme" class="sub-item">
                                    <span class="sub-type-icon"><i class="fas fa-layer-group"></i></span>
                                    <span class="sub-item-title">{{ theme.Titre }}</span>
                                    <button class="action-btn-small restore"
                                        @click.stop="handleRestore('theme', theme.IdTheme)">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals for details -->
        <ModalLesson :isOpen="showLessonModal" :programId="selectedProgramId" :defaultThemeId="selectedLesson?.IdTheme"
            :lessonToEdit="selectedLesson" @close="showLessonModal = false" />

        <ConfirmModal :isOpen="confirmData.isOpen" :title="confirmData.title" :message="confirmData.message"
            :type="confirmData.type" confirmLabel="Restaurer" @confirm="onModalConfirm"
            @cancel="confirmData.isOpen = false" />
    </PremiumModal>
</template>


<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import PremiumModal from '../ui/PremiumModal.vue';
import ConfirmModal from '../ui/ConfirmModal.vue';
import ModalLesson from './ComposantModalProgramme/ModalLesson.vue';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close']);

const currentTab = ref('programs');
const loading = ref(false);
const trashData = ref({
    programs: [],
    lives: [],
    consultations: [],
    offres: [],
    temoignages: [],
    coachings: [],
    categories: [],
    reussites: []
});
const expandedItems = ref(new Set());

// Details Modal State
const showLessonModal = ref(false);
const selectedLesson = ref(null);
const selectedProgramId = ref(null);

const confirmData = ref({
    isOpen: false,
    title: '',
    message: '',
    type: 'warning',
    action: null
});

const onModalConfirm = () => {
    if (confirmData.value.action) confirmData.value.action();
    confirmData.value.isOpen = false;
};

const tabs = [
    { id: 'programs', label: 'Programmes' },
    { id: 'consultations', label: 'Consultations' },
    { id: 'lives', label: 'Lives' },
    { id: 'coachings', label: 'Coachings' },
    { id: 'offres', label: 'Offres' },
    { id: 'temoignages', label: 'Témoignages' },
    { id: 'categories', label: 'Catégories' },
    { id: 'reussites', label: 'Réussites' },
];

const getActiveItems = computed(() => {
    return trashData.value[currentTab.value] || [];
});

const getIconForTab = (tab) => {
    switch (tab) {
        case 'lives': return 'fas fa-video';
        case 'consultations': return 'fas fa-comments';
        case 'offres': return 'fas fa-gift';
        case 'temoignages': return 'fas fa-star';
        case 'coachings': return 'fas fa-user-tie';
        case 'categories': return 'fas fa-tags';
        case 'reussites': return 'fas fa-trophy';
        default: return 'fas fa-image';
    }
};

const getBadgeLabel = (tab) => {
    switch (tab) {
        case 'lives': return 'Live Supprimé';
        case 'consultations': return 'Consultation Supprimée';
        case 'offres': return 'Offre Supprimée';
        case 'temoignages': return 'Témoignage Supprimé';
        case 'coachings': return 'Coaching Supprimé';
        case 'categories': return 'Catégorie Supprimée';
        case 'reussites': return 'Réussite Supprimée';
        default: return 'Élément Supprimé';
    }
};

const fetchTrashData = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/programmes/trash/data');
        trashData.value = response.data;
    } catch (error) {
        console.error("Erreur lors de la récupération de la corbeille:", error);
    } finally {
        loading.value = false;
    }
};

watch(() => props.visible, (newVal) => {
    if (newVal) {
        fetchTrashData();
    }
});

const toggleExpand = (id) => {
    if (expandedItems.value.has(id)) {
        expandedItems.value.delete(id);
    } else {
        expandedItems.value.add(id);
    }
};

const openLessonDetails = (lesson, programId) => {
    selectedLesson.value = lesson;
    selectedProgramId.value = programId;
    showLessonModal.value = true;
};

const handleRestore = (type, id) => {
    confirmData.value = {
        isOpen: true,
        title: 'Confirmation de restauration',
        message: 'Voulez-vous vraiment restaurer cet élément ?',
        type: 'warning',
        action: () => {
            router.post(`/admin/programmes/restore/${type}/${id}`, {}, {
                onSuccess: () => {
                    fetchTrashData();
                }
            });
        }
    };
};

const handleGeneralRestore = (item) => {
    let type = '';
    let id = 0;

    if (item.trash_type) {
        type = item.trash_type;
        id = item.IdLive || item.IdConsultation || item.IdOffre || item.IdTemoignage || item.IdTypeCoaching || item.IdReponseConsultation || item.IdCategorie || item.id;
    } else {
        type = 'program';
        id = item.IdProgrammeFormation;
    }

    handleRestore(type, id);
};

</script>


<style scoped>
.tabs-header {
    display: flex;
    padding: 0 1rem;
    gap: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
    overflow-x: auto;
    margin-bottom: 20px;
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
    transition: color 0.2s;
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
    min-height: 400px;
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

/* Items List */
.list-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.trash-item-container {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.2s;
}

.trash-item-container:hover {
    border-color: #d1d5db;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
}

.trash-item {
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
}

.item-visual {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.program-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.program-thumb-placeholder {
    font-size: 1.5rem;
    color: #d1d5db;
}

.item-details {
    flex: 1;
}

.item-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
}

.item-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.badge {
    font-size: 0.70rem;
    padding: 2px 8px;
    border-radius: 9999px;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-deleted {
    background: #fee2e2;
    color: #991b1b;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.meta-info {
    font-size: 0.8rem;
    color: #6b7280;
}

.item-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.expand-icon {
    color: #9ca3af;
    transition: transform 0.3s;
}

.expand-icon.rotated {
    transform: rotate(180deg);
}

.action-btn {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    background: #22c55e;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    border: none;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #16a34a;
}

/* Sub Items */
.sub-items-container {
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    padding: 15px;
}

.sub-group-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
    margin: 10px 0 5px 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.sub-item {
    display: flex;
    align-items: center;
    padding: 10px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.sub-item:hover {
    border-color: #8A38F5;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

.sub-type-icon {
    width: 24px;
    color: #8A38F5;
    display: flex;
    justify-content: center;
    margin-right: 10px;
}

.sub-item-title {
    flex: 1;
    font-size: 0.95rem;
    color: #374151;
}

.action-btn-small {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn-small:hover {
    background: #22c55e;
    color: white;
}

.no-subs {
    font-size: 0.9rem;
    color: #9ca3af;
    font-style: italic;
    text-align: center;
    padding: 10px;
}
</style>
