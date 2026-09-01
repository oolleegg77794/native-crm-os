<template>
    <div class="relative inline-block text-left" ref="dropdownRef">
        <div @click="isOpen = !isOpen">
            <slot name="trigger" />
        </div>

        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-2 rounded-xl glass-dropdown p-1.5 shadow-2xl focus:outline-none"
                :class="[alignClass, widthClass]"
                @click="closeOnClick ? (isOpen = false) : null"
            >
                <slot />
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    align: { type: String, default: 'right' }, // left, right
    width: { type: String, default: '48' }, // 48, 56, 64, auto
    closeOnClick: { type: Boolean, default: true },
});

const isOpen = ref(false);
const dropdownRef = ref(null);

const alignClass = computed(() => (props.align === 'left' ? 'left-0 origin-top-left' : 'right-0 origin-top-right'));
const widthClass = computed(() => {
    switch (props.width) {
        case '48': return 'w-48';
        case '56': return 'w-56';
        case '64': return 'w-64';
        case '80': return 'w-80';
        default: return 'w-auto min-w-[12rem]';
    }
});

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>
