<template>
    <div :class="['chat-assistant-container', embedded ? 'embedded' : 'floating']">
        <button v-if="!embedded" @click="toggleChat" class="chat-toggle-btn" :class="{ 'is-open': isOpen }">
            <div v-if="!isOpen" class="toggle-avatar-wrapper">
                <UnicornAvatar :size="45" />
            </div>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="icon-close" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <Transition v-if="!embedded" name="fade-slide">
            <div v-if="isOpen" class="chat-window window-floating">
                <ChatContent :messages="messages" :isLoading="isLoading" @send="sendMessage" :embedded="false"
                    ref="chatContentRef" />
            </div>
        </Transition>

        <div v-else class="chat-window window-embedded">
            <ChatContent :messages="messages" :isLoading="isLoading" @send="sendMessage" :embedded="true"
                ref="chatContentRef" />
        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import axios from 'axios';
import ChatContent from './ChatContent.vue';
import UnicornAvatar from './UnicornAvatar.vue';

const props = defineProps({
    embedded: {
        type: Boolean,
        default: false
    }
});

const isOpen = ref(false);
const isLoading = ref(false);
const chatContentRef = ref(null);

const defaultMessages = [
    { role: 'assistant', content: 'Bonjour ! Je suis l\'assistant ShiftUp. Comment puis-je vous aider ?' }
];

const loadMessages = () => {
    try {
        const saved = sessionStorage.getItem('shiftup_ai_chat_current');
        if (saved) return JSON.parse(saved);
    } catch (e) {
        console.error("Erreur historique chat", e);
    }
    return [...defaultMessages];
};

const messages = ref(loadMessages());

watch(messages, (newVal) => {
    try {
        sessionStorage.setItem('shiftup_ai_chat_current', JSON.stringify(newVal));
    } catch (e) {}
}, { deep: true });

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) scrollToBottom();
};

const scrollToBottom = async () => {
    await nextTick();
    if (chatContentRef.value && chatContentRef.value.scrollToBottom) {
        chatContentRef.value.scrollToBottom();
    }
};

const sendMessage = async (userMsg) => {
    if (!userMsg.trim() || isLoading.value) return;

    messages.value.push({ role: 'user', content: userMsg });
    isLoading.value = true;

    scrollToBottom();

    try {
        const response = await axios.post('/ai/chat', { message: userMsg });
        if (response.data.status === 'success') {
            messages.value.push({ role: 'assistant', content: response.data.response });
        } else {
            messages.value.push({ role: 'assistant', content: 'Désolé, problème technique.' });
        }
    } catch (error) {
        messages.value.push({ role: 'assistant', content: 'Erreur de connexion.' });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};
</script>

<style scoped>
.chat-assistant-container {
    display: flex;
    flex-direction: column;
}

.chat-assistant-container.floating {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 9999;
}

.chat-assistant-container.embedded {
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: transparent;
}

.chat-window {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
    background: transparent;
}

.window-floating {
    background: #090909;
    width: 380px;
    height: 600px;
    max-height: 80vh;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    position: absolute;
    bottom: 5rem;
    right: 0;
}

.window-embedded {
    width: 100%;
    height: 100%;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1), transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(30px) scale(0.98);
}

.chat-toggle-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #090909;
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    padding: 0;
    overflow: hidden;
    position: relative;
    z-index: 1;
}

.chat-toggle-btn:hover {
    transform: scale(1.05) translateY(-5px);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
}

.chat-toggle-btn.is-open {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
}

.toggle-avatar-wrapper {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-close {
    width: 25px;
    height: 25px;
    color: white;
}
</style>
