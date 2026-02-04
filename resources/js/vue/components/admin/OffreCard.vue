<template>
    <div class="offre-card">
        <div class="bg-image"
            :style="{ backgroundImage: 'url(' + (offre.LienPhoto || '/images/placeholder.jpg') + ')' }"></div>
        <div class="card-gradient-overlay"></div>

        <div class="card-admin-tools">
            <button class="tool-btn" title="Modifier" @click="$emit('edit', offre)">
                <i class="fas fa-pen"></i>
            </button>
            <button class="tool-btn delete" title="Supprimer" @click="$emit('delete', offre)">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>

        <div class="card-bottom-info">
            <div class="card-status-row">
                <div class="offre-reduction" v-if="offre.ReductionGlobal > 0">
                    -{{ offre.ReductionGlobal }}% DE REDUCTION
                </div>
            </div>
            <h3 class="card-title-overlay">{{ offre.Titre }}</h3>
            <div class="card-metrics-row">
                <div class="metric-item">
                    <i class="fas fa-clock"></i> {{ offre.DureeJours || 0 }} Jours
                </div>
                <div class="metric-item">
                    <i class="fas fa-graduation-cap"></i> {{ offre.programmes?.length || 0 }} Programmes
                </div>
                <div class="metric-item">
                    <i class="fas fa-video"></i> {{ offre.coachings?.length || 0 }} Coachings
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>

defineProps({
    offre: {
        type: Object,
        required: true
    }
});

defineEmits(['edit', 'delete', 'toggle-status']);
</script>

<style scoped>
.offre-card {
    position: relative;
    height: 320px;
    width: 100%;
    overflow: hidden;
    cursor: pointer;
    background-color: #1a1a1a;
    display: flex;
    flex-direction: column;
    transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 20px;
}

.offre-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
}

.bg-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    transition: transform 0.8s ease;
}

.offre-card:hover .bg-image {
    transform: scale(1.05);
}

.card-gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 30%, rgba(0, 0, 0, 0.8) 100%);
    pointer-events: none;
}

.card-admin-tools {
    position: absolute;
    top: 15px;
    right: 15px;
    display: flex;
    gap: 8px;
    z-index: 10;
}

.tool-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.tool-btn:hover {
    background: white;
    color: #111;
}

.tool-btn.delete:hover {
    background: #bf3434;
    color: white;
}

.card-bottom-info {
    margin-top: auto;
    padding: 25px;
    position: relative;
    z-index: 5;
}

.card-status-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.offre-reduction {
    background: #8A38F5;
    color: white;
    padding: 4px 10px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.75rem;
}

.card-title-overlay {
    font-size: 1.5rem;
    font-weight: 500;
    color: white;
    margin: 0 0 10px 0;
}

.card-metrics-row {
    display: flex;
    gap: 15px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.85rem;
}

.metric-item i {
    margin-right: 5px;
    color: #8A38F5;
}
</style>
