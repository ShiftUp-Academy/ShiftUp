<template>
    <div class="chat-content-root">
        <div v-if="!embedded" class="chat-header">
            <div class="header-info">
                <div class="header-avatar">
                    <svg viewBox="0 0 24 24" class="icon-avatar">
                        <path d="M12 2L14.7 8.6L21.3 11.3L14.7 14L12 20.6L9.3 14L2.7 11.3L9.3 8.6L12 2Z" />
                    </svg>
                </div>
                <div class="header-text">
                    <h3 class="title">ShiftUp AI</h3>
                    <p class="subtitle">Propulsé par Gemini</p>
                </div>
            </div>
        </div>

        <div class="messages-area custom-scrollbar" ref="scrollContainer">
            <div class="messages-list">
                <div v-for="(msg, index) in messages" :key="index" :class="['message-row', msg.role]">
                    <div v-if="msg.role === 'assistant'" class="bubble-wrapper assistant">
                        <div class="bubble assistant-bubble">
                            <div class="markdown-body" v-html="renderMarkdown(msg.content)"></div>
                        </div>
                    </div>

                    <div v-else class="bubble-wrapper user">
                        <div class="bubble user-bubble">
                            {{ msg.content }}
                        </div>
                    </div>
                </div>

                <div v-if="isLoading" class="message-row assistant">
                    <div class="bubble-wrapper assistant">
                        <div class="avatar-assistant loading-icon">
                            <svg viewBox="0 0 24 24" class="icon-star animate-pulse">
                                <path d="M12 2L14.7 8.6L21.3 11.3L14.7 14L12 20.6L9.3 14L2.7 11.3L9.3 8.6L12 2Z" />
                            </svg>
                        </div>
                        <div class="bubble assistant-bubble loading-bubble">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="input-container">
            <form @submit.prevent="handleSend" class="input-form">
                <input v-model="userInput" type="text" placeholder="Poser une question..." class="chat-input"
                    :disabled="isLoading">
                <button type="submit" class="send-btn" :disabled="isLoading || !userInput.trim()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </button>
            </form>
            <div class="disclaimer">
                L'IA peut faire des erreurs. Vérifiez les infos.
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { marked } from 'marked';

const props = defineProps({
    messages: Array,
    isLoading: Boolean,
    embedded: Boolean
});

const emit = defineEmits(['send']);
const userInput = ref('');
const scrollContainer = ref(null);

const renderMarkdown = (content) => {
    return marked(content);
};

const handleSend = () => {
    if (!userInput.value.trim() || props.isLoading) return;
    emit('send', userInput.value);
    userInput.value = '';
};

const scrollToBottom = () => {
    if (scrollContainer.value) {
        setTimeout(() => {
            scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
        }, 50);
    }
};

defineExpose({ scrollToBottom });

onMounted(() => {
    scrollToBottom();
});
</script>

<style scoped>
.chat-content-root {
    display: grid;
    align-items:end;
    grid-template-rows: auto 1fr auto;
    height: 100%;
    width: 100%;
    overflow: hidden;
    background: transparent;
    position: relative;
}

.chat-header {
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.01);
}

.header-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1a73e8, #6e39f5);
    padding: 6px;
}

.icon-avatar {
    width: 100%;
    height: 100%;
    fill: white;
}

.header-text .title {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
    color: white;
}

.header-text .subtitle {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
}

.messages-area {
    overflow-y: auto;
    overflow-x: hidden;
    padding: 16px;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    display: flex;
    flex-direction: column;
}

.messages-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-top: auto;
}

.message-row {
    display: flex;
    width: 100%;
}

.message-row.user {
    justify-content: flex-end;
}

.bubble-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    max-width: 90%;
}

.bubble {
    padding: 10px 14px;
    border-radius: 18px;
    font-size: 1.1rem !important;
    line-height: 1.1;
    word-wrap: break-word;
}

.avatar-assistant {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
    margin-bottom: 4px;
}

.icon-star {
    fill: #d9f539;
    width: 100%;
    height: 100%;
}

.assistant-bubble {
    background: rgba(30, 30, 30, 0.6);
    color: #eeeeee;
    border-bottom-left-radius: 4px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(5px);
}

.user-bubble {
    background: linear-gradient(115deg, #1a73e8e0, #6e39f5);
    color: white;
    border-bottom-right-radius: 4px;
    box-shadow: 0 4px 10px rgba(0, 102, 255, 0.2);
}

.input-container {
    height: 12vh;
    border-radius: 30px;
    padding: 12px 16px;
    background: rgb(15, 15, 15);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.input-form {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 24px;
    padding: 4px 4px 4px 14px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: focus-within 0.3s;
}

.input-form:focus-within {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.2);
}

.chat-input {
    flex-grow: 1;
    background: transparent;
    border: none;
    color: white;
    font-size: 0.92rem;
    outline: none;
    padding: 8px 0;
}

.chat-input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.send-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: white;
    border: none;
    color: black;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s, background-color 0.2s;
}

.send-btn:hover {
    background: #f0f0f0;
    transform: scale(1.05);
}

.send-btn:disabled {
    opacity: 0.3;
    transform: none;
}

.disclaimer {
    text-align: center;
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.5);
    margin-top: 6px;
}

/* Scrollbar Style */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

/* Markdown and animations */
.markdown-body {
    font-size: 0.92rem;
}

.markdown-body :deep(p) {
    margin: 0 0 8px 0;
}

.markdown-body :deep(p:last-child) {
    margin: 0;
}

.markdown-body :deep(strong) {
    font-weight: 600;
    color: white;
}

.dot {
    width: 6px;
    height: 6px;
    background: rgba(255, 255, 255, 0.6);
    border-radius: 50%;
    animation: bounce 1.4s infinite ease-in-out both;
}

@keyframes bounce {

    0%,
    80%,
    100% {
        transform: scale(0);
    }

    40% {
        transform: scale(1);
    }
}

.animate-pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: .5;
    }
}
</style>
