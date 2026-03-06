<template>
    <div class="chat-content-root">
        <!-- SVG Gradient Definition -->
        <svg width="0" height="0" style="position: absolute; pointer-events: none;">
            <defs>
                <linearGradient id="starGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#202020;stop-opacity:1" />
                    <stop offset="50%" style="stop-color:#1A888D;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#F7B455;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>
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

        <div class="messages-area custom-scrollbar" ref="scrollContainer" @click="handleChatClick">
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
                        <div class="avatar-assistant loading-icon-container" ref="loadingIconRef">
                            <svg viewBox="0 0 24 24" class="icon-star">
                                <path d="M12 2L14.7 8.6L21.3 11.3L14.7 14L12 20.6L9.3 14L2.7 11.3L9.3 8.6L12 2Z" />
                            </svg>
                        </div>
                        <div class="bubble assistant-bubble loading-bubble" ref="loadingBubbleRef">
                            <div class="dots-container">
                                <span class="dot"></span>
                                <span class="dot"></span>
                                <span class="dot"></span>
                            </div>
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
import { ref, onMounted, watch, onBeforeUnmount } from 'vue';
import { marked } from 'marked';
import { gsap } from 'gsap';
import { router } from '@inertiajs/vue3';

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

const handleChatClick = (event) => {
    const link = event.target.closest('a');
    if (link) {
        const href = link.getAttribute('href');
        if (href && (href.startsWith('/') || href.startsWith(window.location.origin))) {
            event.preventDefault();
            const path = href.startsWith('/') ? href : href.replace(window.location.origin, '');
            router.visit(path);
        }
    }
};

defineExpose({ scrollToBottom });

const loadingIconRef = ref(null);
const loadingBubbleRef = ref(null);
let loadingTimeline = null;

const startLoadingAnimation = () => {
    if (loadingTimeline) loadingTimeline.kill();
    loadingTimeline = gsap.timeline({ repeat: -1 });

    if (loadingIconRef.value) {
        // Star Icon: Floating + Rotation + Glow
        loadingTimeline.to(loadingIconRef.value, {
            y: -8,
            rotation: 15,
            duration: 1.5,
            ease: "sine.inOut",
            yoyo: true,
            repeat: -1
        }, 0);

        loadingTimeline.to(loadingIconRef.value.querySelector('svg'), {
            filter: "drop-shadow(0 0 8px #1A888D)",
            scale: 1.1,
            duration: 0.8,
            yoyo: true,
            repeat: -1,
            ease: "power1.inOut"
        }, 0);
    }

    if (loadingBubbleRef.value) {
        // Bubble: Subtle liquid-like scaling
        loadingTimeline.to(loadingBubbleRef.value, {
            scaleX: 1.05,
            scaleY: 0.95,
            duration: 1.2,
            ease: "sine.inOut",
            yoyo: true,
            repeat: -1
        }, 0.2);

        // Individual dots animation
        const dots = loadingBubbleRef.value.querySelectorAll('.dot');
        dots.forEach((dot, i) => {
            loadingTimeline.to(dot, {
                y: -10,
                opacity: 1,
                scale: 1.2,
                duration: 0.5,
                ease: "power2.out",
                yoyo: true,
                repeat: -1,
                delay: i * 0.15
            }, 0);
        });
    }
};

watch(() => props.isLoading, (loading) => {
    if (loading) {
        setTimeout(startLoadingAnimation, 50); // Small delay to ensure DOM is ready
        scrollToBottom();
    } else {
        if (loadingTimeline) loadingTimeline.kill();
    }
});

onBeforeUnmount(() => {
    if (loadingTimeline) loadingTimeline.kill();
});

onMounted(() => {
    scrollToBottom();
});
</script>

<style scoped>
.chat-content-root {
    display: grid;
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
    min-height: 0;
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

.icon-star path,
.icon-avatar path {
    fill: url(#starGradient) !important;
}

.icon-star,
.icon-avatar {
    width: 100%;
    height: 100%;
}

.assistant-bubble {
    background: rgba(30, 30, 30, 0.6);
    color: #eeeeee;
    border-bottom-left-radius: 4px;
    backdrop-filter: blur(5px);
}

.user-bubble {
    background: linear-gradient(135deg, #202020 0%, #1A888D 50%, #F7B455 100%) !important;
    color: white;
    border-bottom-right-radius: 4px;
    box-shadow: 0 4px 15px rgba(26, 136, 141, 0.2);
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

.markdown-body {
    font-size: 1.1rem;
    line-height: 1.1;
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

.markdown-body :deep(a) {
    display: inline-block;
    background: linear-gradient(135deg, #202020, #1A888D, #F7B455, #202020);
    background-size: 300% 300%;
    color: white !important;
    padding: 3px 12px;
    border-radius: 12px;
    text-decoration: none !important;
    font-weight: 600;
    font-size: 0.9em;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    margin: 4px 2px;
    animation: gradientShift 4s ease infinite;
}

@keyframes gradientShift {
    0% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}

.markdown-body :deep(a:hover) {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 15px rgba(26, 136, 141, 0.4);
}

.dot {
    width: 8px;
    height: 8px;
    background: linear-gradient(135deg, #202020 0%, #1A888D 50%, #F7B455 100%) !important;
    border-radius: 50%;
    opacity: 1 !important;
    transform: translateY(0);
}

.dots-container {
    display: flex;
    gap: 6px;
    padding: 2px 4px;
}

.loading-icon-container {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
    margin-top: 2vh;
    margin-bottom: 4px;
}
</style>
