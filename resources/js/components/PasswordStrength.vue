<script setup>
import { computed, onMounted, ref } from 'vue';
const props = defineProps({ inputId: { type: String, required: true } });
const password = ref('');
onMounted(() => document.getElementById(props.inputId)?.addEventListener('input', (event) => password.value = event.target.value));
const score = computed(() => [password.value.length >= 12, /[A-Z]/.test(password.value), /[a-z]/.test(password.value), /\d/.test(password.value), /[^A-Za-z0-9]/.test(password.value)].filter(Boolean).length);
const label = computed(() => ['Muy débil', 'Muy débil', 'Débil', 'Aceptable', 'Fuerte', 'Muy fuerte'][score.value]);
</script>
<template><div class="mt-2" aria-live="polite"><div class="progress" style="height:8px"><div class="progress-bar" :class="score >= 4 ? 'bg-success' : score >= 3 ? 'bg-warning' : 'bg-danger'" :style="{width: `${score * 20}%`}"></div></div><small class="text-muted">Seguridad: {{ label }}. Use 12 caracteres, mayúsculas, minúsculas, número y símbolo.</small></div></template>
