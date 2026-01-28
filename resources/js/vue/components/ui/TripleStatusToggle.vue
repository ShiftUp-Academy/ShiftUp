<template>
    <div class="triple-toggle-container">
        <div class="toggle-track">
            <div class="toggle-indicator" :style="indicatorStyle"></div>
            <button v-for="option in options" :key="option.value" class="toggle-btn"
                :class="{ active: modelValue === option.value }" @click="$emit('update:modelValue', option.value)">
                {{ option.label }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        required: true
    },
    options: {
        type: Array,
        required: true,
        validator: (value) => value.length === 3
    }
});

defineEmits(['update:modelValue']);

const indicatorStyle = computed(() => {
    const index = props.options.findIndex(opt => opt.value === props.modelValue);
    const translatePercent = index * 100;
    return {
        transform: `translateX(${translatePercent}%)`,
        width: '33.333333%'
    };
});
</script>

<style scoped>
.triple-toggle-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 30px 0;
    width: 100%;
}

.toggle-track {
    display: flex;
    background: #f8f9fa;
    border-radius: 20px;
    position: relative;
    padding: 6px;
    width: 100%;
    border: 1px solid #e9ecef;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
}

.toggle-btn {
    flex: 1;
    background: none;
    border: none;
    padding: 16px 0;
    font-size: 1rem;
    font-weight: 700;
    z-index: 2;
    cursor: pointer;
    color: #adb5bd;
    transition: all 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    letter-spacing: 0.5px;
}

.toggle-btn.active {
    color: #ffffff;
}

.toggle-indicator {
    position: absolute;
    top: 6px;
    left: 6px;
    bottom: 6px;
    width: calc(33.333333% - 12px);
    background: linear-gradient(90deg, #0E7EC3, #8A38F5);
    border-radius: 16px;
    box-shadow: 0 8px 15px rgba(138, 56, 245, 0.25);
    z-index: 1;
    transition: transform 0.6s cubic-bezier(0.19, 1, 0.22, 1);
}
</style>
