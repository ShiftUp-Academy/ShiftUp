<template>
    <Transition @before-enter="beforeEnter" @enter="enter" @leave="leave" :css="false">
        <div v-if="isOpen" class="modal-notif-wrapper" ref="wrapperRef">
            <LiquidGlass border-radius="25px 25px 0 25px" class="glass-container">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Notifications</h3>
                        <button class="close-btn" @click.stop="$emit('close')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="notifications-list" data-lenis-prevent>
                        <div v-if="notifications.length === 0" class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <p>Aucune nouvelle activité.</p>
                        </div>
                        <div v-else v-for="(notif, index) in notifications" :key="index" class="notif-item">
                            <div class="notif-icon">
                                <i :class="notif.icon || 'fas fa-info-circle'"></i>
                            </div>
                            <div class="notif-body">
                                <p class="notif-text">{{ notif.message }}</p>
                                <span class="notif-time">{{ notif.time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </LiquidGlass>
            <div class="bubble-tail"></div>
        </div>
    </Transition>
</template>

<script setup>
import { ref } from 'vue';
import { gsap } from 'gsap';
import LiquidGlass from './LiquidGlass.vue';

const props = defineProps({
    isOpen: Boolean,
    notifications: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

// GSAP HOOKS
const beforeEnter = (el) => {
    gsap.set(el, {
        opacity: 0,
        y: -30, // Starts slightly above
        filter: 'blur(10px)',
        scale: 1,
        position: 'relative'
    });
};

const enter = (el, done) => {
    gsap.to(el, {
        opacity: 1,
        y: 0,
        filter: 'blur(0px)',
        duration: 0.6,
        ease: "power3.out",
        onComplete: done
    });
};

const leave = (el, done) => {
    // NO absolute positioning needed anymore thanks to the grid container
    gsap.to(el, {
        opacity: 0,
        y: 30, // Continue DESCENDING towards robot
        scale: 0.85,
        filter: 'blur(10px)',
        duration: 0.4,
        ease: "power2.in",
        onComplete: done
    });
};
</script>

<style scoped>
.modal-notif-wrapper {
    width: 320px;
    margin-right: 8vw;
    position: relative;
    z-index: 200;
    margin-bottom: 10px;
    will-change: transform, opacity, filter;
}

.glass-container {
    width: 100%;
}

.modal-content {
    padding: 20px;
    width: 100%;
    color: white;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 10px;
}

.modal-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #ffffff;
}

.close-btn {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.4);
    font-size: 1rem;
    cursor: pointer;
    transition: color 0.3s;
}

.close-btn:hover {
    color: white;
}

.notifications-list {
    max-height: 350px;
    overflow-y: auto;
}

.notifications-list::-webkit-scrollbar {
    width: 3px;
}

.notifications-list::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

.empty-state {
    text-align: center;
    padding: 30px 10px;
    color: rgba(255, 255, 255, 0.4);
}

.notif-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 15px;
    background: rgb(1, 1, 1);
    margin-bottom: 8px;
    transition: background 0.3s;
}

.notif-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(138, 56, 245, 0.2);
    color: #8A38F5;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
}

.notif-body {
    flex: 1;
}

.notif-text {
    margin: 0 0 4px;
    font-size: 1rem;
    line-height: 1.1;
    color: #efefef;
}

.notif-time {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.729);
}

.bubble-tail {
    position: absolute;
    top: 100%;
    right: 0px;
    width: 0;
    height: 0;
    border-left: 12px solid transparent;
    border-right: 0px solid transparent;
    border-top: 12px solid rgba(15, 15, 15, 0.8);
    z-index: 10;
}
</style>
