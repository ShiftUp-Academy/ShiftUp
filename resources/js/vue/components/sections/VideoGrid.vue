<template>
    <section class="video-reveal-section no-global-reveal video-grid" ref="sectionRef">
        <div class="sticky-wrapper">
            <div class="grid-container" ref="gridRef">

                <!-- Rangée du haut -->
                <div class="grid-item">
                    <video class="grid-video" :autoplay="gridItems[0].autoplay" :loop="gridItems[0].loop" muted
                        playsinline :preload="gridItems[0].preload">
                        <source :src="gridItems[0].src" type="video/mp4" />
                    </video>
                </div>

                <div class="grid-item">
                    <video class="grid-video" :autoplay="gridItems[1].autoplay" :loop="gridItems[1].loop" muted
                        playsinline :preload="gridItems[1].preload">
                        <source :src="gridItems[1].src" type="video/mp4" />
                    </video>
                </div>

                <!-- Rangée du milieu -->
                <div class="grid-item">
                    <video class="grid-video" :autoplay="gridItems[2].autoplay" :loop="gridItems[2].loop" muted
                        playsinline :preload="gridItems[2].preload">
                        <source :src="gridItems[2].src" type="video/mp4" />
                    </video>
                </div>

                <div class="grid-item center-item">
                    <video class="grid-video" :autoplay="gridItems[3].autoplay" :loop="gridItems[3].loop" muted
                        playsinline :preload="gridItems[3].preload">
                        <source :src="gridItems[3].src" type="video/mp4" />
                    </video>
                </div>

                <div class="grid-item">
                    <video class="grid-video" :autoplay="gridItems[4].autoplay" :loop="gridItems[4].loop" muted
                        playsinline :preload="gridItems[4].preload">
                        <source :src="gridItems[4].src" type="video/mp4" />
                    </video>
                </div>

                <!-- Rangée du bas -->
                <div class="grid-item">
                    <video class="grid-video" :autoplay="gridItems[5].autoplay" :loop="gridItems[5].loop" muted
                        playsinline :preload="gridItems[5].preload">
                        <source :src="gridItems[5].src" type="video/mp4" />
                    </video>
                </div>

                <div class="grid-item">
                    <video class="grid-video" :autoplay="gridItems[6].autoplay" :loop="gridItems[6].loop" muted
                        playsinline :preload="gridItems[6].preload">
                        <source :src="gridItems[6].src" type="video/mp4" />
                    </video>
                </div>
            </div>

            <!-- Overlay -->
            <div class="soft-dark-overlay"></div>

            <!-- Texte -->
            <div class="overlay-content" ref="overlayRef">
                <h2 class="reveal-text">
                    <span class="text-line">
                        A travers <span class="highlight gt-alpina-font">ShiftUp</span>, on a la vision
                        d'aider les dirigeants de TPE et PME malgache
                    </span>
                    <span class="text-line">
                        à trouver son <span class="highlight gt-alpina-font">feu sacré</span>, réaliser les <span
                            class="highlight gt-alpina-font">premières ventes</span>
                    </span>
                    <span class="text-line">
                        et à devenir un <br>
                        <span class="highlight gt-alpina-font">entrepreneur libre</span>.
                    </span>
                </h2>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from "vue";

const sectionRef = ref(null);
const gridRef = ref(null);
const overlayRef = ref(null);

const VIDEO_SOURCES = [
    "https://res.cloudinary.com/dzgdjei0h/video/upload/v1766752045/S%C3%A9minaire_2023_tbwekc.mp4",
    "https://res.cloudinary.com/dzgdjei0h/video/upload/v1766752042/Shift_Up_1_Workflow_2_disaju.mp4",
    "https://res.cloudinary.com/dzgdjei0h/video/upload/v1766752042/Shift_Up_1_Workflow_3_bklddl.mp4",
    "https://res.cloudinary.com/dzgdjei0h/video/upload/v1766751299/Shift_Up_1_Workflow_myuoax.mp4", // Centre
    "https://res.cloudinary.com/dzgdjei0h/video/upload/v1766489669/%C3%89pingl%C3%A9_sur_Brand_content_dgrajc.mp4",
    "https://res.cloudinary.com/dzgdjei0h/video/upload/v1766752050/Teaser_Mvc_eqc7jj.mp4",
    "https://res.cloudinary.com/dzgdjei0h/video/upload/v1766752053/Teaser_Mvc_1_tg8cgu.mp4"
];

const gridItems = VIDEO_SOURCES.map((src, index) => ({
    src: src,
    autoplay: index === 3, // Autoplay pour le centre uniquement
    loop: true,
    preload: index === 3 ? 'auto' : 'metadata'
}));

let scrollTriggerInstance = null;

onMounted(async () => {
    const { gsap } = await import("gsap");
    const { ScrollTrigger } = await import("gsap/ScrollTrigger");

    gsap.registerPlugin(ScrollTrigger);

    await nextTick();
    setTimeout(() => {
        ScrollTrigger.refresh();
    }, 100);

    if (!sectionRef.value || !gridRef.value || !overlayRef.value) return;

    const textLines = overlayRef.value.querySelectorAll(".text-line");
    const videos = sectionRef.value.querySelectorAll("video");

    videos[3].muted = true;
    videos[3].play().catch(() => { });

    ScrollTrigger.create({
        trigger: sectionRef.value,
        start: "top bottom+=50%", // Lance la lecture bien avant l'arrivée (50vh plus tôt)
        end: "bottom top-=600%",   // Garde la lecture même après la sortie (50vh plus loin)
        onEnter: () => videos.forEach(v => v.play().catch(() => { })),
        onEnterBack: () => videos.forEach(v => v.play().catch(() => { })),
        onLeave: () => videos.forEach(v => v.pause()),
        onLeaveBack: () => videos.forEach(v => v.pause()),
    });

    // 2. Timeline pour l'animation (Zoom + Textes)
    const tl = gsap.timeline({
        scrollTrigger: {
            trigger: sectionRef.value,
            start: "top top",
            end: "+=550%",
            scrub: 1.2,
            pin: true,
            pinSpacing: true,
            invalidateOnRefresh: true,
            anticipatePin: 1
        },
    });

    tl.fromTo(
        gridRef.value,
        { scale: 1.5, transformOrigin: "center center" },
        {
            scale: 1,
            duration: textLines.length * 3,
            ease: "power3.inOut"
        },
        0
    );
    textLines.forEach((line, index) => {
        const startTime = index * 3.0;
        tl.fromTo(line,
            {
                opacity: 0,
                "--progress": "0%",
                y: 10
            },
            {
                opacity: 1,
                "--progress": "120%",
                y: 0,
                duration: 1.2, // Plus rapide
                ease: "power2.out"
            },
            startTime
        );

        // Temps de lecture (pause)
        tl.to(line, { duration: 0.8 }, startTime + 1.2);

        // Sortie complète avant le suivant
        if (index < textLines.length - 1) {
            tl.to(line, {
                opacity: 0,
                "--progress": "0%",
                duration: 0.8,
                ease: "power2.in"
            }, startTime + 2.0);
        }
    });

    scrollTriggerInstance = tl.scrollTrigger;
});

onBeforeUnmount(() => {
    if (scrollTriggerInstance) {
        scrollTriggerInstance.kill();
        scrollTriggerInstance = null;
    }
});
</script>

<style scoped>
.video-reveal-section {
    width: 100%;
    min-height: 100vh;
    background: #000;
    overflow: hidden;
    position: relative;
}

.sticky-wrapper {
    height: 100vh;
    display: grid;
    place-items: center;
    position: relative;
    overflow: hidden;
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-template-rows: 0.8fr 1.5fr 1.5fr;
    gap: 12px;
    width: 100%;
    height: 140vh;
    will-change: transform;
    filter: brightness(1);
}

.grid-item:nth-child(1) {
    grid-column: 1 / 3;
    grid-row: 1;
}

.grid-item:nth-child(2) {
    grid-column: 3 / 5;
    grid-row: 1;
}

.grid-item:nth-child(3) {
    grid-column: 1 / 2;
    grid-row: 2;
}

.grid-item:nth-child(4) {
    grid-column: 2 / 4;
    grid-row: 2;
}

.grid-item:nth-child(5) {
    grid-column: 4 / 5;
    grid-row: 2;
}

.grid-item:nth-child(6) {
    grid-column: 1 / 3;
    grid-row: 3;
}

.grid-item:nth-child(7) {
    grid-column: 3 / 5;
    grid-row: 3;
}

.grid-item {
    background: #111;
    border-radius: 12px;
    overflow: hidden;
    height: 100%;
}

.grid-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.center-item .grid-video {
    object-position: center 0%;
}

.soft-dark-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    z-index: 5;
}

.overlay-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10;
    text-align: center;
    width: 80%;
}

.reveal-text {
    color: #fff;
    font-size: 4em;
    font-weight: 400;
    line-height: 1.2;
    position: relative;
    height: 4em;
    display: flex;
    align-items: center;
    justify-content: center;
}

.text-line {
    position: absolute;
    display: block;
    width: 70%;
    opacity: 0;
    /* Effet de dégradé fluide */
    -webkit-mask-image: linear-gradient(to right,
            rgba(0, 0, 0, 1) 0%,
            rgba(0, 0, 0, 1) calc(var(--progress) - 20%),
            rgba(0, 0, 0, 0) var(--progress));
    mask-image: linear-gradient(to right,
            rgba(0, 0, 0, 1) 0%,
            rgba(0, 0, 0, 1) calc(var(--progress) - 20%),
            rgba(0, 0, 0, 0) var(--progress));
    --progress: 0%;
}

.highlight {
    color: #fff;
}

.gt-alpina-font {
    font-family: 'GT Alpina Fine Trial', serif;
    font-weight: 400;
    font-style: italic;
}

@media (max-width: 1024px) {
    .reveal-text {
        font-size: 2.2em;
    }
}

@media (max-width: 768px) {
    .grid-container {
        height: 80vh;
        gap: 6px;
    }

    .overlay-content {
        width: 80%;
    }

    .reveal-text {
        font-size: 2.4em;
        line-height: 1.1;
    }

    .text-line {
        width: 100%;
    }

    .grid-item {
        border-radius: 6px;
    }
}

@media (max-width: 480px) {
    .reveal-text {
        font-size: 2.6em;
    }
}
</style>