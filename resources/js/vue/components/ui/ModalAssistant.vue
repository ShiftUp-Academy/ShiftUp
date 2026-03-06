<template>
    <Transition @before-enter="beforeEnter" @enter="enter" @leave="leave" :css="false">
        <div v-if="isOpen" class="modal-notif-wrapper" @click="$emit('close')" ref="wrapperRef">
            <LiquidGlass border-radius="30px 30px 0 30px" class="glass-container" @click.stop>
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="toggle-container">
                            <button :class="['toggle-bt', activeTab === 'assistant' ? 'active' : '']"
                                @click="activeTab = 'assistant'">
                                Assistant
                            </button>
                            <button :class="['toggle-bt', activeTab === 'notif' ? 'active' : '']"
                                @click="activeTab = 'notif'">
                                Notification
                            </button>
                        </div>
                    </div>

                    <div class="content-viewport" data-lenis-prevent>
                        <Transition name="fade-slide" mode="out-in">
                            <div v-if="activeTab === 'notif'" key="notif" class="notifications-list custom-scrollbar">
                                <div v-if="notifications.length === 0" class="empty-state">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>Aucune nouvelle activité.</p>
                                </div>
                                <div v-else v-for="(notif, index) in notifications" :key="notif.Id"
                                    :class="['notif-item', !notif.DateLecture ? 'unread' : '']"
                                    @click="markAsRead(notif)">
                                    <div class="notif-icon">
                                        <i :class="notif.Donnees.icone || 'fas fa-info-circle'"></i>
                                    </div>
                                    <div class="notif-body">
                                        <p class="notif-text">{{ notif.Donnees.message }}</p>
                                        <span class="notif-time">{{ formatDate(notif.DateCreation) }}</span>
                                    </div>
                                    <div v-if="!notif.DateLecture" class="unread-dot"></div>
                                </div>
                            </div>

                            <div v-else key="assistant" class="assistant-container">
                                <ChatAssistant :embedded="true" />
                            </div>
                        </Transition>
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
import ChatAssistant from './ChatAssistant.vue';

const props = defineProps({
    isOpen: Boolean,
    notifications: {
        type: Array,
        default: () => []
    }
});

const activeTab = ref('notif');
const emit = defineEmits(['close', 'refresh']);

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return "À l'instant";
    if (diffInSeconds < 3600) return `Il y a ${Math.floor(diffInSeconds / 60)} min`;
    if (diffInSeconds < 86400) return `Il y a ${Math.floor(diffInSeconds / 3600)} h`;
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
};

const markAsRead = async (notif) => {
    if (!notif.DateLecture) {
        try {
            await fetch(`/notifications/${notif.Id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });
            emit('refresh');
        } catch (error) {
            console.error("Erreur marquage lu", error);
        }
    }

    if (notif.Donnees.lien) {
        window.location.href = notif.Donnees.lien;
    }
};

const beforeEnter = (el) => {
    gsap.set(el, {
        opacity: 0,
        y: -30,
        scale: 1,
        position: 'relative'
    });
};

const enter = (el, done) => {
    gsap.to(el, {
        opacity: 1,
        y: 0,
        duration: 0.6,
        ease: "power3.out",
        clearProps: "all",
        onComplete: done
    });
};

const leave = (el, done) => {
    gsap.to(el, {
        opacity: 0,
        y: 30,
        scale: 0.85,
        duration: 0.4,
        ease: "power2.in",
        onComplete: done
    });
};
</script>

<style scoped>
.modal-notif-wrapper {
    width: 320px;
    margin-right: 4vw !important;
    position: relative;
    z-index: 200;
    margin-bottom: 10px;
}

.glass-container {
    width: 100%;
    display: flex;
    flex-direction: column;
}

:deep(.liquidGlass-effect) {
    backdrop-filter: blur(5px) !important;
    background: rgba(255, 255, 255, 0.05) !important;
}

:deep(.liquidGlass-tint) {
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.5) 0%, rgba(0, 0, 0, 0.15) 100%) !important;
}

.modal-content {
    padding: 20px;
    width: 100%;
    color: white;
    display: flex;
    flex-direction: column;
    background: transparent !important;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 10px;
    flex-shrink: 0;
}

.toggle-container {
    display: flex;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    padding: 3px;
    backdrop-filter: blur(5px);
}

.toggle-bt {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.922);
    padding: 6px 14px;
    border-radius: 9px;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.toggle-bt.active {
    background: rgb(35, 35, 35);
    color: white;
}

.content-viewport {
    height: 400px;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.content-viewport>.fade-slide-enter-active,
.content-viewport>.fade-slide-leave-active,
.content-viewport>div {
    height: 100%;
    width: 100%;
    display: flex;
    flex-direction: column;
}

.notifications-list {
    height: 100%;
    overflow-y: auto;
}

.assistant-container {
    height: 100%;
    width: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
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
    border-radius: 30px;
    background: rgba(10, 10, 10, 0.6);
    backdrop-filter: blur(2px);
    margin-bottom: 8px;
    transition: background 0.3s;
}

.notif-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(26, 26, 26, 0.2);
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

.notif-item.unread {
    background: linear-gradient(135deg, #202020 0%, #1A888D 50%, #F7B455 100%) !important;
}

.notif-item.unread .notif-icon {
    color: rgba(255, 255, 255, 0.814) !important;
}

.unread-dot {
    width: 10px;
    height: 10px;
    background: #ffffff;
    border-radius: 50%;
    align-self: center;
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

.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateX(10px);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}
</style>
