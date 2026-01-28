<template>
    <PremiumModal :isOpen="isOpen" width="60vw" :header="program?.Titre || 'Détails du Programme'"
        @close="$emit('close')">
        <template #header-actions>
            <div class="title-actions" v-if="program">
                <button class="header-action-btn small" @click="duplicateProgram(program)"><i
                        class="far fa-copy"></i></button>
                <button class="header-action-btn edit-full" @click="$emit('edit-program', program)">Modifier tout le
                    programme</button>
            </div>
        </template>

        <div v-if="program" class="program-details-container">
            <div class="program-header-info">
                <p class="program-description">{{ program.Descriptions || 'Pas de description disponible.' }}</p>
                <div class="program-stats-row">
                    <span class="status-indicator" :class="program.Statut?.toLowerCase()">
                        <i class="fas fa-circle"></i> {{ program.Statut }}
                    </span>
                </div>
            </div>

            <div class="themes-list-wrapper">
                <LessonListAdmin v-for="(theme, index) in displayThemes" :key="theme.IdTheme || 'dummy-' + index"
                    :theme="theme" @duplicate="duplicateLesson" @edit="$emit('edit-lesson', $event)"
                    @delete="deleteLesson" @add="$emit('create-lesson', $event)"
                    @editTheme="$emit('edit-theme', $event)" @deleteTheme="deleteTheme" />
            </div>

            <div class="add-chapter-container">
                <button class="add-chapter-btn" @click="$emit('create-theme')">
                    <i class="fas fa-plus"></i> Ajouter un nouveau chapitre
                </button>
            </div>
        </div>

        <ConfirmModal :isOpen="confirmData.isOpen" :title="confirmData.title" :message="confirmData.message"
            :type="confirmData.type" @confirm="onModalConfirm" @cancel="confirmData.isOpen = false" />
    </PremiumModal>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import PremiumModal from '../../../components/ui/PremiumModal.vue';
import ConfirmModal from '../../../components/ui/ConfirmModal.vue';
import LessonListAdmin from '../../../components/admin/LessonListAdmin.vue';
import Toast from 'primevue/toast'; // Logic uses toast?

const props = defineProps({
    isOpen: Boolean,
    program: Object
});

const emit = defineEmits([
    'close',
    'edit-program',
    'create-lesson',
    'edit-lesson',
    'create-theme',
    'edit-theme'
]);

const toast = useToast();

const confirmData = ref({
    isOpen: false,
    title: '',
    message: '',
    type: 'danger',
    action: null
});

const onModalConfirm = () => {
    if (confirmData.value.action) confirmData.value.action();
    confirmData.value.isOpen = false;
};

const triggerConfirm = (title, message, type, action) => {
    confirmData.value = { isOpen: true, title, message, type, action };
};

const displayThemes = computed(() => {
    if (!props.program) return [];
    return props.program.themes && props.program.themes.length > 0
        ? props.program.themes
        : [{ IdTheme: null, Titre: 'Exemple de Chapitre', Descriptions: 'Ajoutez un chapitre pour commencer.', lecons: [] }];
});

const duplicateProgram = (prog) => {
    triggerConfirm(
        "Dupliquer le programme",
        `Voulez-vous vraiment dupliquer "${prog.Titre}" ?`,
        'info',
        () => router.post('/admin/programmes/' + prog.IdProgrammeFormation + '/duplicate')
    );
};

const duplicateLesson = (lecon) => {
    router.post('/admin/lecons/duplication/' + lecon.IdLecon);
};

const deleteLesson = (lecon) => {
    triggerConfirm(
        "Supprimer la leçon",
        `Voulez-vous vraiment déplacer "${lecon.Titre}" vers la corbeille ?`,
        'danger',
        () => router.delete('/admin/lecons/' + lecon.IdLecon)
    );
};

const deleteTheme = (theme) => {
    triggerConfirm(
        "Supprimer le chapitre",
        `Voulez-vous vraiment supprimer le chapitre "${theme.Titre}" ?`,
        'danger',
        () => {
            router.delete('/admin/themes/' + theme.IdTheme, {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Chapitre supprimé', life: 3000 })
            });
        }
    );
};
</script>

<style scoped>
.header-action-btn {
    height: 40px;
    padding: 0 15px;
    border-radius: 10px;
    border: 1px solid #ddd;
    background: white;
    color: #444;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.header-action-btn.small {
    width: 36px;
    height: 36px;
    padding: 0;
    justify-content: center;
    border-radius: 8px;
}

.header-action-btn:hover {
    background: #f9f9f9;
    border-color: #8A38F5;
    color: #8A38F5;
}

.header-action-btn.edit-full {
    background: #1c1c1c;
    color: white;
    border: none;
    padding: 0 20px;
}

.header-action-btn.edit-full:hover {
    background: #7626d8;
}

.program-header-info {
    margin-bottom: 30px;
}

.program-description {
    font-size: 1rem;
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
}

.program-stats-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.status-indicator {
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-indicator.publié {
    color: #22c55e;
}

.status-indicator.dépublié {
    color: #ef4444;
}

.progression-text {
    font-weight: 700;
    color: #111;
    font-size: 0.95rem;
}

.progress-bar-container {
    width: 100%;
    height: 8px;
    background: #eee;
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: #8A38F5;
    transition: width 0.5s ease;
}

.program-details-container {
    margin-bottom: 4vh;
}

.add-chapter-container {
    margin-top: 20px;
    display: flex;
    justify-content: center;
}

.add-chapter-btn {
    background: transparent;
    border: 2px dashed #2f2f2f;
    color: #2f2f2f;
    padding: 12px 24px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.add-chapter-btn:hover {
    border-color: #8A38F5;
    color: #8A38F5;
    background: rgba(138, 56, 245, 0.05);
}

.title-actions {
    display: flex;
    gap: 8px;
}
</style>
