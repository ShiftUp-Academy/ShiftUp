<template>
    <div class="intro-loader fixed inset-0 z-[99999] flex items-center justify-center bg-black" ref="container">
        <ShaderBackground class="absolute inset-0 w-full h-full" :colors="colors">
            <div class="relative z-20 flex flex-col items-center justify-center h-full text-center px-4">
                <h1 class="text-3xl md:text-5xl font-light text-white mb-8 tracking-wider opacity-0" ref="textRef">
                    Veuillez patienter,<br>
                    <span class="text-xl md:text-2xl mt-4 block text-white/80">vous ne verrez ce chargement qu'une seule
                        fois.</span>
                </h1>
                <div class="loading-wave opacity-0" ref="waveRef">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
            </div>
        </ShaderBackground>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { gsap } from 'gsap';
import ShaderBackground from './ShaderBackground.vue';

const emit = defineEmits(['complete']);

const container = ref(null);
const textRef = ref(null);
const waveRef = ref(null);

const colors = {
    primary: '#F7B455',
    secondary: '#0E7EC3',
    accent: '#981e12',
    dark: '#000000'
};

onMounted(() => {
    const tl = gsap.timeline({
        onComplete: () => {
            gsap.to(container.value, {
                yPercent: -100,
                duration: 0.8,
                ease: 'power4.inOut',
                onComplete: () => emit('complete')
            });
        }
    });

    tl.to([textRef.value, waveRef.value], {
        opacity: 1,
        y: 0,
        duration: 1,
        stagger: 0.2,
        ease: 'power3.out'
    })
        .to({}, { duration: 2.5 });
});
</script>

<style scoped>
.loading-wave {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    height: 50px;
    gap: 6px;
}

.bar {
    width: 6px;
    height: 20px;
    background-color: white;
    animation: wave 1.2s infinite ease-in-out;
    border-radius: 4px;
}

.bar:nth-child(1) {
    animation-delay: 0s;
}

.bar:nth-child(2) {
    animation-delay: 0.1s;
}

.bar:nth-child(3) {
    animation-delay: 0.2s;
}

.bar:nth-child(4) {
    animation-delay: 0.3s;
}

@keyframes wave {

    0%,
    100% {
        height: 20px;
        opacity: 0.5;
    }

    50% {
        height: 50px;
        opacity: 1;
    }
}
</style>
