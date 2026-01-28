<template>
  <div class="lessons-main-container">
    <div class="chapter-header">
      <h3 class="chapter-title">
        {{ theme.Titre }}
      </h3>
      <div class="chapter-actions">
        <button class="chapter-btn"><i class="far fa-copy"></i></button>
        <button class="chapter-btn" @click="$emit('editTheme', theme)"><i class="fas fa-pen"></i></button>
        <button class="chapter-btn delete" @click="$emit('deleteTheme', theme)"><i
            class="fas fa-trash-alt"></i></button>
      </div>
    </div>
    <p class="chapter-subtitle">{{ theme.Descriptions || 'Ajoutez une description au chapitre...' }}</p>

    <div class="chapter-meta">
      <span><i class="far fa-file-alt"></i> {{ theme.lecons?.length || 0 }} leçons</span>
      <span class="status-indicator" :class="theme.Statut === 'Publié' ? 'publié' : 'dépublié'">
        <i class="fas fa-circle"></i> {{ theme.Statut || 'Dépublié' }}
      </span>
    </div>

    <div class="lessons-list">
      <div v-for="lecon in theme.lecons" :key="lecon.IdLecon" class="lesson-row">
        <div class="lesson-info">
          <h4>{{ lecon.Titre }}</h4>
          <div class="lesson-meta-info">
            <span><i class="fas fa-lock"></i> 0 étapes</span>
            <span><i class="fas fa-history"></i> {{ lecon.DelaiDrop ? lecon.DelaiDrop + 'h' : '0h' }}</span>
          </div>
        </div>

        <div class="lesson-row-actions">
          <button class="lesson-action-pill" @click="$emit('duplicate', lecon)"><i class="far fa-copy"></i></button>
          <button class="lesson-action-pill" @click="$emit('edit', lecon)"><i class="fas fa-pen"></i></button>
          <button class="lesson-action-pill delete" @click="$emit('delete', lecon)"><i
              class="fas fa-trash-alt"></i></button>
        </div>
      </div>

      <template v-if="!theme.lecons || theme.lecons.length === 0">
        <div class="lesson-row guide">
          <div class="lesson-info" style="justify-content:center; text-align:center; opacity:0.6;">
            <p>Ce chapitre ne contient aucune leçon.</p>
          </div>
        </div>
      </template>

      <button class="add-lesson-btn-main" @click="$emit('add', theme)">
        <i class="fas fa-plus"></i> Ajouter une leçon
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  theme: {
    type: Object,
    required: true
  }
});
defineEmits(['edit', 'duplicate', 'delete', 'add', 'editTheme', 'deleteTheme']);
</script>

<style scoped>
.lessons-main-container {
  background: #f8f9fa;
  padding: 25px;
  border-radius: 15px;
  border: 1px solid #eee;
}

.chapter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.chapter-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #111;
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
}

.chapter-edit-icon {
  font-size: 0.8rem;
  color: #8A38F5;
  opacity: 0.6;
  cursor: pointer;
}

.chapter-actions {
  display: flex;
  gap: 8px;
}

.chapter-btn {
  background: white;
  border: 1px solid #ddd;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  cursor: pointer;
  color: #666;
  transition: all 0.2s;
  font-size: 0.8rem;
}

.chapter-btn:hover {
  border-color: #8A38F5;
  color: #8A38F5;
}

.chapter-btn.delete:hover {
  border-color: #ef4444;
  color: #ef4444;
}

.chapter-subtitle {
  font-size: 0.9rem;
  color: #888;
  margin-bottom: 15px;
  margin-top: 0;
}

.chapter-meta {
  display: flex;
  align-items: center;
  gap: 20px;
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 12px;
}

.chapter-meta i {
  color: #aaa;
}

.chapter-progress-mini {
  width: 100%;
  height: 4px;
  background: #eee;
  border-radius: 2px;
  margin-bottom: 25px;
}

.mini-fill {
  height: 100%;
  background: #8A38F5;
}

.lessons-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.lesson-row {
  display: flex;
  align-items: center;
  background: #8A38F5;
  padding: 15px 20px;
  border-radius: 15px;
  color: white;
  box-shadow: 0 4px 15px rgba(138, 56, 245, 0.2);
  transition: transform 0.2s;
}

.lesson-row:hover {
  transform: translateX(5px);
}

.lesson-progress-circle {
  width: 44px;
  height: 44px;
  margin-right: 18px;
  position: relative;
}

.circular-chart-purple {
  width: 100%;
  height: 100%;
}

.circle-bg {
  fill: none;
  stroke: rgba(255, 255, 255, 0.2);
  stroke-width: 3;
}

.circle-fill {
  fill: none;
  stroke: white;
  stroke-width: 3;
  stroke-linecap: round;
  transition: stroke-dasharray 0.3s ease;
}

.lesson-info {
  flex: 1;
}

.lesson-info h4 {
  margin: 0 0 5px 0;
  font-size: 1.05rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.edit-icon-inline {
  font-size: 0.75rem;
  opacity: 0.7;
  cursor: pointer;
}

.lesson-meta-info {
  font-size: 0.8rem;
  opacity: 0.8;
  display: flex;
  gap: 15px;
}

.lesson-meta-info i {
  margin-right: 4px;
}

.lesson-row-actions {
  display: flex;
  gap: 8px;
}

.lesson-action-pill {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.lesson-action-pill:hover {
  background: white;
  color: #8A38F5;
}

.lesson-action-pill.delete:hover {
  color: #ff6b6b;
}

.add-lesson-btn-main {
  margin-top: 15px;
  width: 100%;
  padding: 16px;
  border: 2px dashed #4e4e4e;
  border-radius: 15px;
  background: transparent;
  color: #4e4e4e;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}

.add-lesson-btn-main:hover {
  border-color: #8A38F5;
  color: #8A38F5;
  background: rgba(138, 56, 245, 0.05);
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
  font-size: 0.85rem;
}
</style>
