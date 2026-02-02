<template>
    <div class="page-transition-overlay fixed inset-0 z-[9999] pointer-events-none flex" style="visibility: hidden;"
        ref="container">
        <div v-for="n in columns" :key="n"
            class="column flex-1 h-full flex flex-col justify-end relative overflow-hidden">
            <div v-for="m in rows" :key="`${n}-${m}`" class="transition-square w-full h-full absolute top-full left-0"
                :style="{ backgroundColor: getRandomColor() }"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { gsap } from 'gsap';

const container = ref(null);
const columns = 5;
const rows = 3;   

const colors = ['#F7B455', '#0E7EC3', '#981e12', '#1a1a1a'];

const getRandomColor = () => {
    return colors[Math.floor(Math.random() * colors.length)];
};

const animateStart = (done) => {
    if (!container.value) {
        if (done) done();
        return;
    }

    gsap.set(container.value, { visibility: 'visible' });

    const squares = container.value.querySelectorAll('.transition-square');
    gsap.set(squares, { y: '0%' });
    const cols = container.value.querySelectorAll('.column');
    const tl = gsap.timeline({
        onComplete: () => {
            if (done) done();
        }
    });

    const allSquares = [];
    cols.forEach((col, i) => {
        const sqs = col.querySelectorAll('.transition-square');
    });
    const stagger = 0.05;
};

const startTransition = () => {
    return new Promise((resolve) => {
        if (!container.value) { resolve(); return; }
        gsap.set(container.value, { visibility: 'visible' });
        const cols = container.value.querySelectorAll('.column');
        gsap.fromTo(cols,
            { yPercent: 100 },
            {
                yPercent: 0,
                duration: 0.6,
                stagger: {
                    amount: 0.3,
                    from: "random"
                },
                ease: "power3.inOut",
                onComplete: resolve
            }
        );
    });
};

const endTransition = () => {
    if (!container.value) return;
    const cols = container.value.querySelectorAll('.column');
    gsap.to(cols, {
        yPercent: -100,
        duration: 0.6,
        stagger: {
            amount: 0.2,
            from: "random"
        },
        ease: "power3.inOut",
        onComplete: () => {
            gsap.set(container.value, { visibility: 'hidden' });
            gsap.set(cols, { yPercent: 100 }); // Reset for next time
        }
    });
};

defineExpose({
    startTransition,
    endTransition
});
</script>

<style scoped>
.transition-square {
    height: 100%;
    /* Make it full height for coverage */
}

/* Add some gradient or pattern to the columns to simulate "squares" or "trail" */


.column:nth-child(1) .transition-square {
    background-color: #F7B455;
}

.column:nth-child(2) .transition-square {
    background-color: #0E7EC3;
}

.column:nth-child(3) .transition-square {
    background-color: #981e12;
}

.column:nth-child(4) .transition-square {
    background-color: #0E7EC3;
}

.column:nth-child(5) .transition-square {
    background-color: #F7B455;
}
</style>
