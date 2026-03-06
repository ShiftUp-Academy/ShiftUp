<template>
    <div class="empty-state-container" :class="customClass">
        <div class="empty-box shadow-sm">
            <i :class="icon"></i>
            <h2>{{ title }}</h2>
            <p>{{ description }}</p>
            <div class="empty-actions">
                <slot name="actions"></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    icon: { type: String, default: 'fas fa-calendar-xmark' },
    title: String,
    description: String,
    customClass: String
});
</script>

<style scoped>
.empty-state-container {
    text-align: center;
    padding: 60px 20px;
    width: 100%;
}

.empty-box {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    padding: 60px 40px;
    border-radius: 40px;
    max-width: 600px;
    margin: 0 auto;
}

.empty-box i {
    font-size: 3.5rem;
    color: #1A888D;
    margin-bottom: 25px;
    opacity: 0.5;
}

.empty-box h2 {
    font-size: 2.2rem;
    font-weight: 600;
    margin-bottom: 15px;
}

.empty-box p {
    font-size: 1.15rem;
    opacity: 0.5;
    line-height: 1.6;
    margin-bottom: 35px;
}

.empty-actions {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

:deep(.action-btn) {
    padding: 15px 35px;
    border-radius: 15px;
    background: #1A888D;
    color: white;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

:deep(.action-btn.secondary) {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

:deep(.action-btn:hover) {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

@media (max-width: 768px) {
    .empty-box {
        padding: 40px 20px;
    }
}
</style>
