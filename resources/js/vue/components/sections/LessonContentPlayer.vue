<template>
    <div class="lesson-content-player" :style="fullScreenMode ? 'overflow: visible !important;' : ''">
        <div v-if="isVideo" class="video-container">
            <template v-if="videoUrl">
                <iframe v-if="isEmbeddable" :src="videoUrl" :title="$t('LessonContentPlayer.lesson_video')"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen class="video-iframe"></iframe>
                <video v-else controls class="video-iframe" controlsList="nodownload"
                    @ended="$emit('content-finished')">
                    <source :src="videoUrl" type="video/mp4">
                    {{ $t('LessonContentPlayer.your_browser_does') }}
                </video>
            </template>
            <div v-else class="no-content">
                <i class="fas fa-video-slash"></i>
                <p>{{ $t('LessonContentPlayer.video_indisponible') }}</p>
            </div>
        </div>

        <div v-else-if="isPdf" class="pdf-container" :class="{ 'fullscreen-mode': fullScreenMode }">
            <div v-if="isLoadingPdf" class="pdf-loading">
                <i class="fas fa-spinner fa-spin"></i> {{ $t('LessonContentPlayer.chargement_document') }}
            </div>

            <VuePdfEmbed v-if="pdfSource && !pdfError" :source="pdfSource" class="pdf-renderer" @loaded="onPdfLoaded"
                @loading-failed="onPdfError" />

            <div v-if="pdfError || !pdfUrl" class="no-content">
                <i class="fas fa-file-pdf"></i>
                <p>{{ $t('LessonContentPlayer.impossible_afficher_pdf') }}</p>
                <a v-if="pdfUrl" :href="pdfUrl + '?download=1'" class="download-fallback-link" download target="_blank">
                    <i class="fas fa-download mr-1"></i> {{ $t('LessonContentPlayer.telecharger_fichier') }}
                </a>
            </div>
        </div>

        <div v-else class="text-container"
            :class="{ 'fullscreen-mode': fullScreenMode, 'show-scroll-hint': isAnimating }" ref="textContainerRef"
            @scroll="checkScroll">
            <template v-if="fullScreenMode">
                <div class="side-controls right">
                    <div class="arrow-btn up" @click="scrollText('up')" @mousemove="handleFlashMove"
                        @mouseleave="handleFlashLeave">
                        <div class="flashlight-overlay"></div>
                        <div class="image-border-glow"></div>
                    </div>
                    <div class="arrow-btn down" @click="scrollText('down')" @mousemove="handleFlashMove"
                        @mouseleave="handleFlashLeave">
                        <div class="flashlight-overlay"></div>
                        <div class="image-border-glow"></div>
                    </div>
                </div>
            </template>

            <div class="text-content" v-html="formattedText"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import VuePdfEmbed from 'vue-pdf-embed';

const emit = defineEmits(['content-finished']);

const pdfError = ref(false);
const isLoadingPdf = ref(true);

const onPdfLoaded = () => {
    console.log('PDF Loaded successfully');
    isLoadingPdf.value = false;
    pdfError.value = false;
};

const onPdfError = (error) => {
    console.error('PDF Load Error:', error);
    isLoadingPdf.value = false;
    pdfError.value = true;
};

const props = defineProps({
    lesson: {
        type: Object,
        required: true,
        default: () => ({
            TypeLecon: 'Texte',
            Contenu: '',
            Descriptions: ''
        })
    },
    fullScreenMode: {
        type: Boolean,
        default: false
    }
});

const textContainerRef = ref(null);
const isAnimating = ref(false);

const triggerHint = () => {
    if (!textContainerRef.value) return;
    isAnimating.value = true;

    setTimeout(() => {
        textContainerRef.value.scrollTo({ top: 70, behavior: 'smooth' });
        setTimeout(() => {
            textContainerRef.value.scrollTo({ top: 0, behavior: 'smooth' });
            // Désactiver l'animation visuelle après 2 secondes
            setTimeout(() => { isAnimating.value = false; }, 2000);
        }, 600);
    }, 500);
};

const isVideo = computed(() => {
    const type = props.lesson.TypeLecon?.toLowerCase().trim();
    const source = props.lesson.SourceType?.toLowerCase().trim();
    return type === 'video' || type === 'vidéo' || source === 'video' || source === 'vidéo';
});

const isPdf = computed(() => {
    const type = props.lesson.TypeLecon?.toLowerCase().trim();
    const source = props.lesson.SourceType?.toLowerCase().trim();
    const content = props.lesson.Contenu?.toLowerCase().trim() || '';
    return type === 'pdf' || source === 'pdf' || content.endsWith('.pdf');
});

const pdfUrl = computed(() => {
    if (!isPdf.value || !props.lesson.Contenu) return null;

    return props.lesson.Contenu;
});

const pdfSource = ref(null);

watch(pdfUrl, async (newUrl) => {
    if (!newUrl) {
        pdfSource.value = null;
        return;
    }

    isLoadingPdf.value = true;
    pdfError.value = false;

    try {
        console.log('Fetching PDF from:', newUrl);
        const response = await fetch(newUrl);

        if (!response.ok) {
            throw new Error(`Fetch failed with status: ${response.status}`);
        }

        // Handle 204 explicitly (though fetch might not throw, the body is empty)
        if (response.status === 204) {
            throw new Error('Server returned 204 No Content for PDF file.');
        }

        const blob = await response.blob();
        if (blob.size === 0) {
            throw new Error('Downloaded PDF is empty.');
        }

        pdfSource.value = URL.createObjectURL(blob);
    } catch (error) {
        console.warn('Manual fetch failed, falling back to direct URL:', error);
        pdfSource.value = newUrl;
    } finally {
        if (pdfError.value) isLoadingPdf.value = false;
    }
}, { immediate: true });

onUnmounted(() => {
    if (pdfSource.value) {
        URL.revokeObjectURL(pdfSource.value);
    }
});

const checkScroll = () => {
    if (!textContainerRef.value) return;
    const { scrollTop, scrollHeight, clientHeight } = textContainerRef.value;
    if (scrollTop + clientHeight >= scrollHeight - 5) {
        emit('content-finished');
    }
};

const scrollText = (direction) => {
    if (!textContainerRef.value) return;

    const container = textContainerRef.value;
    const scrollAmount = window.innerHeight * 0.3;
    const startScroll = container.scrollTop;
    const targetScroll = direction === 'down'
        ? Math.min(startScroll + scrollAmount, container.scrollHeight - container.clientHeight)
        : Math.max(startScroll - scrollAmount, 0);

    const startTime = performance.now();
    const duration = 1500;

    const easeInOutQuart = (x) => {
        return x < 0.5 ? 8 * x * x * x * x : 1 - Math.pow(-2 * x + 2, 4) / 2;
    };

    const animateScroll = (currentTime) => {
        const elapsed = currentTime - startTime;
        if (elapsed > duration) {
            container.scrollTop = targetScroll;
            return;
        }

        const progress = elapsed / duration;
        const ease = easeInOutQuart(progress);

        container.scrollTop = startScroll + (targetScroll - startScroll) * ease;
        requestAnimationFrame(animateScroll);
    };

    requestAnimationFrame(animateScroll);
};

watch(() => props.lesson, () => {
    setTimeout(() => {
        if (!isVideo.value && !isPdf.value && textContainerRef.value) {
            const { scrollHeight, clientHeight } = textContainerRef.value;
            if (scrollHeight <= clientHeight) {
                emit('content-finished');
            } else {
                triggerHint();
            }
        }
        if (isPdf.value) {
            setTimeout(() => emit('content-finished'), 3000);
        }
        if (isVideo.value && isEmbeddable.value) {
            setTimeout(() => emit('content-finished'), 10000);
        }
    }, 500);
}, { immediate: true });

const videoUrl = computed(() => {
    if (!props.lesson.Contenu) return null;
    let url = props.lesson.Contenu;
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        if (url.includes('embed')) return url;
        const videoId = url.split('v=')[1]?.split('&')[0] || url.split('/').pop();
        return `https://www.youtube.com/embed/${videoId}`;
    }
    return url;
});

const isEmbeddable = computed(() => {
    if (!videoUrl.value) return false;
    return videoUrl.value.includes('youtube.com') ||
        videoUrl.value.includes('youtu.be') ||
        videoUrl.value.includes('vimeo.com') ||
        videoUrl.value.includes('dailymotion.com');
});

const formattedText = computed(() => {
    let content = props.lesson.Contenu || props.lesson.Descriptions || $t('LessonContentPlayer.aucun_contenu_texte');

    content = content.replace(/\n/g, '<br>');


    // 1. Remplacement des RGB sombres
    content = content.replace(/color\s*:\s*rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/gi, (match, r, g, b) => {
        // Seuil : si les trois composantes sont inférieures à 90 (gris foncé), on remplace par blanc
        if (parseInt(r) < 90 && parseInt(g) < 90 && parseInt(b) < 90) {
            return 'color: #ffffff';
        }
        return match;
    });

    // 2. Remplacement des HEX 6 digits sombres (#RRGGBB)
    content = content.replace(/color\s*:\s*#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/gi, (match, r, g, b) => {
        const rVal = parseInt(r, 16);
        const gVal = parseInt(g, 16);
        const bVal = parseInt(b, 16);
        if (rVal < 90 && gVal < 90 && bVal < 90) {
            return 'color: #ffffff';
        }
        return match;
    });

    // 3. Remplacement des autres définitions de noir strict
    const strictBlackPatterns = [
        /color\s*:\s*black/gi,
        /color\s*:\s*#000(?![0-9a-f])/gi, // Hex 3 digits #000
        /color\s*:\s*rgba\(\s*0\s*,\s*0\s*,\s*0\s*,\s*1\s*\)/gi,
        /color\s*:\s*hsl\(\s*0\s*,\s*0%\s*,\s*0%\s*\)/gi,
        /color\s*:\s*windowtext/gi
    ];

    strictBlackPatterns.forEach(pattern => {
        content = content.replace(pattern, 'color: #ffffff');
    });

    return content;
});

// Flashlight Effect
const handleFlashMove = (event) => {
    const el = event.currentTarget;
    const rect = el.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;

    el.style.setProperty('--mouse-x', `${x}px`);
    el.style.setProperty('--mouse-y', `${y}px`);
    el.style.setProperty('--flash-opacity', 1);
};

const handleFlashLeave = (event) => {
    event.currentTarget.style.setProperty('--flash-opacity', 0);
};
</script>

<style scoped>
.lesson-content-player {
    width: 100%;
    margin-bottom: 25px;
    background: #1a1a1a;
    border-radius: 12px;
    overflow: hidden;
}

.video-container {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    background: #000;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 20px rgba(138, 56, 245, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.video-iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Reduce native video controls size */
.video-iframe::-webkit-media-controls-panel {
    transform: scale(0.8);
    transform-origin: bottom center;
    width: 125%;
    /* Compensate for scale to fill width */
    margin-bottom: -5px;
    /* Adjust vertical position */
}

/* --- PDF CONTAINER --- */
.pdf-container {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    background: #1e1e1e;
    border-radius: 12px;
    overflow-y: auto;
    /* Enable scrolling for long PDFs */
    position: relative;
    padding: 20px;
}

.pdf-container.fullscreen-mode {
    height: 100vh;
    border-radius: 0;
    padding: 40px;
    /* Add more padding in fullscreen */
}

.pdf-renderer {
    width: 100%;
    height: auto;
    display: block;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
}

.pdf-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 200px;
    color: #fff;
    font-size: 1.1vw;
    gap: 15px;
}

.pdf-embed {
    width: 100%;
    height: 100%;
    border: none;
}

.download-fallback-link {
    color: #8A38F5;
    text-decoration: underline;
    margin-top: 10px;
    font-size: 0.9vw;
    display: flex;
    align-items: center;
}

.download-fallback-link:hover {
    color: #a561f7;
}

.text-container {
    padding: 25px 60px;
    background: #1a1a1a;
    color: #e0e0e0;
    max-height: 600px;
    overflow-y: auto;
    position: relative;
}

.side-controls {
    position: fixed;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    gap: 20px;
    z-index: 100;
}

.side-controls.left {
    left: 8vw;
}

.side-controls.right {
    right: 8vw;
    opacity: 0.15;
    transition: opacity 0.3s ease;
}

.side-controls.right:hover {
    opacity: 1;
}

.arrow-btn {
    width: 150px;
    height: 150px;
    background: transparent;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3.5rem;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    --mouse-x: 50%;
    --mouse-y: 50%;
    --flash-opacity: 0;
}

.arrow-btn i {
    z-index: 10;
    pointer-events: none;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
}

.arrow-btn:hover {
    transform: scale(1.05);
}

.flashlight-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 5;
    background: radial-gradient(150px circle at var(--mouse-x) var(--mouse-y),
            rgba(138, 56, 245, 0.3),
            transparent 80%);
    opacity: var(--flash-opacity, 0);
    transition: opacity 0.3s ease;
}

.image-border-glow {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 6;
    border-radius: inherit;
    padding: 2px;
    background: radial-gradient(120px circle at var(--mouse-x) var(--mouse-y),
            rgba(255, 255, 255, 0.8),
            transparent 60%);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: var(--flash-opacity, 0);
    transition: opacity 0.3s ease;
}

.show-scroll-hint .arrow-btn {
    animation: pulseArrow 2s infinite;
}

@keyframes pulseArrow {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.1);
    }

    100% {
        transform: scale(1);
    }
}

/* --- FULLSCREEN --- */
.text-container.fullscreen-mode {
    max-height: 80vh !important;
    overflow-y: auto !important;
    background: transparent !important;
    padding: 25px 60px;
    scrollbar-width: none;
    -ms-overflow-style: none;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-y;
}

.text-container.fullscreen-mode::-webkit-scrollbar {
    display: none;
}

.text-content {
    line-height: 1.8;
    font-size: 1vw;
    overflow-wrap: break-word;
    word-break: normal;
    hyphens: none;
    -webkit-hyphens: none;
    -ms-hyphens: none;
    color: #ffffff !important;
}

.text-content :deep(*) {
    background-color: transparent !important;
}

.no-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 200px;
    color: #666;
    gap: 10px;
}
</style>