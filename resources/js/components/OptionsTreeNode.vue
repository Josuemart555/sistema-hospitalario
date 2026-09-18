<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    node: { type: Object, required: true },
    checkedIds: { type: Object, required: true },
});

const expanded = ref(true);
const hasChildren = computed(() => props.node.children.length > 0);
const descendantIds = computed(() => flattenIds(props.node.children));

function flattenIds(nodes) {
    return nodes.flatMap((child) => [child.id, ...flattenIds(child.children)]);
}

const isChecked = computed(() => props.checkedIds.has(props.node.id));
const isIndeterminate = computed(() => !isChecked.value && descendantIds.value.some((id) => props.checkedIds.has(id)));

const toggle = (event) => {
    const ids = [props.node.id, ...descendantIds.value];
    if (event.target.checked) {
        ids.forEach((id) => props.checkedIds.add(id));
    } else {
        ids.forEach((id) => props.checkedIds.delete(id));
    }
};
</script>

<template>
    <div class="options-tree-node">
        <div class="options-tree-row" :class="{ 'is-checked': isChecked }">
            <button v-if="hasChildren" type="button" class="options-tree-toggle" @click="expanded = !expanded">
                <i class="bi" :class="expanded ? 'bi-folder2-open' : 'bi-folder2'"></i>
            </button>
            <span v-else class="options-tree-toggle-spacer"><i class="bi bi-file-earmark"></i></span>
            <input :id="`option-${node.id}`" class="form-check-input" type="checkbox" :checked="isChecked" :indeterminate="isIndeterminate" @change="toggle">
            <label class="form-check-label" :for="`option-${node.id}`">{{ node.name }}</label>
        </div>

        <div v-if="hasChildren && expanded" class="options-tree-children">
            <OptionsTreeNode v-for="child in node.children" :key="child.id" :node="child" :checked-ids="checkedIds" />
        </div>
    </div>
</template>
