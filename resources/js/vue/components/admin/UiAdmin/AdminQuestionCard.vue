<template>
    <div class="question-admin-card">
        <div class="q-card-header">
            <div class="user-info">
                <div class="user-avatar">{{ item.utilisateur?.Prenom?.[0] || '?' }}</div>
                <div class="user-details">
                    <span class="user-name">{{ item.utilisateur?.Prenom }} {{ item.utilisateur?.Nom }}</span>
                    <span class="q-date">{{ formatDate(item.DateCreation) }}</span>
                </div>
            </div>
            <div class="header-right">
                <div v-if="item.Statut" class="q-status-pill" :class="item.Statut.toLowerCase().replace(' ', '-')">
                    {{ item.Statut }}
                </div>
                <button class="delete-btn-minimal" title="Supprimer" @click.stop="$emit('delete', item)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>

        <div class="q-card-body">
            <h3 class="q-title">{{ item.Titre }}</h3>
            <p class="q-text">{{ item.Question }}</p>

            <div class="full-context" v-if="item.lecon" @click="$emit('view-lesson', item.lecon)">
                <div class="context-path">
                    <span class="path-segment prog">
                        <i class="fas fa-layer-group"></i> {{ item.lecon?.theme?.programme?.Titre || 'N/A' }}
                    </span>
                    <span class="path-sep">/</span>
                    <span class="path-segment theme"> {{ item.lecon?.theme?.Titre || 'N/A' }} </span>
                    <span class="path-sep">/</span>
                    <span class="path-segment lesson highlight"> {{ item.lecon?.Titre || 'N/A' }} </span>
                </div>
            </div>
        </div>

        <div class="q-card-footer">
            <div class="q-actions">
                <button class="action-btn reply" @click="$emit('reply', item)">
                    <i class="fas fa-reply"></i> Répondre
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    item: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['reply', 'view-lesson', 'delete']);

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const datePart = date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
    const timePart = date.getHours().toString().padStart(2, '0') + 'h' + date.getMinutes().toString().padStart(2, '0');
    return `${datePart} ${timePart}`;
};
</script>

<style scoped>
.question-admin-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #f0f0f0;
    display: flex;
    flex-direction: column;
    gap: 15px;
    transition: all 0.3s ease;
}

.q-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #8A38F5;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.user-name {
    display: block;
    font-weight: 600;
    color: #111;
}

.q-date {
    font-size: 0.8rem;
    color: #999;
}

.q-status-pill {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.q-status-pill.ouverte {
    background: #e0f2fe;
    color: #0369a1;
}

.q-status-pill.en-cours {
    background: #fef9c3;
    color: #854d0e;
}

.q-status-pill.fermée {
    background: #f1f5f9;
    color: #475569;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.delete-btn-minimal {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
    font-size: 0.9rem;
    opacity: 0.3;
    transition: opacity 0.2s;
    padding: 5px;
}

.delete-btn-minimal:hover {
    opacity: 1;
}

.q-title {
    font-size: 1.2rem;
    font-weight: 700;
    margin: 0 0 10px 0;
}

.q-text {
    line-height: 1.5;
    color: #444;
    margin-bottom: 15px;
}

.full-context {
    background: #f8f9fa;
    padding: 12px 15px;
    border-radius: 12px;
    border: 1px dashed #dee2e6;
    cursor: pointer;
    transition: all 0.2s;
}

.full-context:hover {
    background: #f1f3f5;
    border-color: #8A38F5;
}

.context-path {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 5px;
    font-size: 0.85rem;
}

.path-segment {
    padding: 2px 6px;
    border-radius: 4px;
}

.path-segment.prog {
    background: #8A38F5;
    color: white;
}

.path-segment.theme {
    color: #666;
    font-weight: 500;
}

.path-segment.lesson {
    color: #111;
    font-weight: 700;
}

.path-segment.lesson.highlight {
    color: #8A38F5;
}

.path-sep {
    color: #ccc;
}

.q-card-footer {
    padding-top: 15px;
    border-top: 1px solid #f5f5f5;
}

.q-actions {
    display: flex;
    gap: 10px;
}

.action-btn {
    flex: 1;
    padding: 10px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.action-btn.reply {
    background: #111;
    color: white;
}

.action-btn.reply:hover {
    background: #8A38F5;
}
</style>
