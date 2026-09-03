<template>
    <div class="space-y-8 max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Module Builder & Schema</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Design custom CRM entities, add dynamic fields, and configure layouts
                </p>
            </div>

            <button
                type="button"
                class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition flex items-center gap-2"
                @click="isCreateModuleModalOpen = true"
            >
                + Create New Module
            </button>
        </div>

        <!-- Modules Master-Detail Layout -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Left 1 Col: Modules List -->
            <div class="space-y-2">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider px-2">
                    Available Modules
                </div>

                <div class="space-y-1.5">
                    <button
                        v-for="mod in metaStore.modules"
                        :key="mod.id"
                        type="button"
                        class="w-full text-left p-3 rounded-xl border transition flex items-center justify-between group select-none shadow-sm"
                        :class="selectedModule?.id === mod.id ? 'bg-indigo-50 dark:bg-indigo-600/10 border-indigo-500 text-slate-900 dark:text-white' : 'bg-white dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                        @click="selectModule(mod)"
                    >
                        <div class="flex items-center gap-3 truncate">
                            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: mod.color || '#6366F1' }"></span>
                            <span class="font-semibold text-sm truncate">{{ mod.name }}</span>
                        </div>
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">{{ mod.api_name }}</span>
                    </button>
                </div>
            </div>

            <!-- Right 3 Cols: Selected Module Fields & Config -->
            <div class="md:col-span-3 space-y-6">
                <div v-if="selectedModule" class="p-6 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 glass-panel shadow-sm space-y-6">
                    <!-- Module Details Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span>{{ selectedModule.name }}</span>
                                <Badge variant="primary">{{ selectedModule.fields?.length || 0 }} fields</Badge>
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                API Slug: <code class="text-indigo-600 dark:text-indigo-400 font-mono">{{ selectedModule.api_name }}</code> &bull;
                                Title Field: <code class="text-emerald-600 dark:text-emerald-400 font-mono">{{ selectedModule.title_field }}</code>
                            </p>
                        </div>

                        <button
                            type="button"
                            class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition"
                            @click="isAddFieldModalOpen = true"
                        >
                            + Add Custom Field
                        </button>
                    </div>

                    <!-- Fields List Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="text-slate-500 dark:text-slate-400 uppercase font-semibold border-b border-slate-200 dark:border-slate-800/80 pb-2">
                                    <th class="py-2.5 px-3">Field Label</th>
                                    <th class="py-2.5 px-3">API Name</th>
                                    <th class="py-2.5 px-3">Data Type</th>
                                    <th class="py-2.5 px-3 text-center">Required</th>
                                    <th class="py-2.5 px-3 text-center">Unique</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                                <tr
                                    v-for="field in selectedModule.fields"
                                    :key="field.id"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition text-slate-800 dark:text-slate-200"
                                >
                                    <td class="py-3 px-3 font-medium text-slate-900 dark:text-white flex items-center gap-2">
                                        <span>{{ field.label }}</span>
                                        <span v-if="field.api_name === selectedModule.title_field" class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-500/20">
                                            PRIMARY
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 font-mono text-slate-500 dark:text-slate-400">{{ field.api_name }}</td>
                                    <td class="py-3 px-3 font-mono text-indigo-600 dark:text-indigo-300">{{ field.type }}</td>
                                    <td class="py-3 px-3 text-center">
                                        <span :class="field.is_required ? 'text-rose-500 font-bold' : 'text-slate-400 dark:text-slate-600'">
                                            {{ field.is_required ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span :class="field.is_unique ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400 dark:text-slate-600'">
                                            {{ field.is_unique ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else class="p-12 text-center text-slate-400 dark:text-slate-500 border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl bg-white/40 dark:bg-transparent">
                    Select a module from the left to view and configure its fields.
                </div>
            </div>
        </div>

        <!-- Add Field Modal -->
        <Modal
            v-model="isAddFieldModalOpen"
            title="Add Custom Field"
            subtitle="Define a new attribute for this dynamic module"
            size="md"
        >
            <form @submit.prevent="handleAddField" id="add-field-form" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Field Label</label>
                    <input
                        type="text"
                        v-model="fieldForm.label"
                        required
                        placeholder="e.g. Expected Revenue"
                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 shadow-sm transition"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">API Name (Optional)</label>
                    <input
                        type="text"
                        v-model="fieldForm.api_name"
                        placeholder="e.g. expected_revenue (auto-generated if blank)"
                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 shadow-sm transition font-mono"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Field Type</label>
                    <select
                        v-model="fieldForm.type"
                        required
                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 shadow-sm transition"
                    >
                        <option value="string" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Single Line Text (string)</option>
                        <option value="text" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Multi-Line Text (textarea)</option>
                        <option value="integer" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Integer Number</option>
                        <option value="decimal" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Decimal Number</option>
                        <option value="currency" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Currency ($)</option>
                        <option value="percent" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Percentage (%)</option>
                        <option value="boolean" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Boolean Switch (Checkbox)</option>
                        <option value="date" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Date</option>
                        <option value="datetime" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Date & Time</option>
                        <option value="email" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Email</option>
                        <option value="phone" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Phone Number</option>
                        <option value="url" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">URL Link</option>
                        <option value="picklist" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Picklist (Dropdown Single Select)</option>
                        <option value="lookup" class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Lookup (Foreign Relationship)</option>
                    </select>
                </div>

                <!-- Lookup Target Module Selector -->
                <div v-if="fieldForm.type === 'lookup'">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Target Module</label>
                    <select
                        v-model="fieldForm.lookupTarget"
                        required
                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 shadow-sm transition"
                    >
                        <option value="" disabled selected class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Select target module...</option>
                        <option
                            v-for="m in metaStore.modules"
                            :key="m.api_name"
                            :value="m.api_name"
                            class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
                        >
                            {{ m.name }} ({{ m.api_name }})
                        </option>
                    </select>
                </div>

                <div class="flex items-center gap-6 pt-2">
                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" v-model="fieldForm.is_required" class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-indigo-600 focus:ring-indigo-500" />
                        Required Field
                    </label>
                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" v-model="fieldForm.is_unique" class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-indigo-600 focus:ring-indigo-500" />
                        Unique Value
                    </label>
                </div>
            </form>

            <template #footer>
                <button
                    type="button"
                    class="px-4 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition shadow-sm"
                    @click="isAddFieldModalOpen = false"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    form="add-field-form"
                    class="px-5 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition"
                >
                    Add Field
                </button>
            </template>
        </Modal>

        <!-- Create Module Modal -->
        <Modal
            v-model="isCreateModuleModalOpen"
            title="Create Custom CRM Module"
            subtitle="Add a new entity type with its own table, schema, and layout"
            size="md"
        >
            <form @submit.prevent="handleCreateModule" id="create-mod-form" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Module Name (Plural)</label>
                    <input
                        type="text"
                        v-model="moduleForm.name"
                        required
                        placeholder="e.g. Products"
                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 shadow-sm transition"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Singular Name</label>
                    <input
                        type="text"
                        v-model="moduleForm.singular_name"
                        placeholder="e.g. Product"
                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 shadow-sm transition"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Primary Title Field Name</label>
                    <input
                        type="text"
                        v-model="moduleForm.title_field"
                        placeholder="e.g. product_name"
                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 shadow-sm transition font-mono"
                    />
                </div>
            </form>

            <template #footer>
                <button
                    type="button"
                    class="px-4 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition shadow-sm"
                    @click="isCreateModuleModalOpen = false"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    form="create-mod-form"
                    class="px-5 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition"
                >
                    Create Module
                </button>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useMetaStore } from '@/stores/meta';
import Modal from '@/components/common/Modal.vue';
import Badge from '@/components/common/Badge.vue';

const metaStore = useMetaStore();
const selectedModule = ref(null);
const isAddFieldModalOpen = ref(false);
const isCreateModuleModalOpen = ref(false);

const fieldForm = ref({
    label: '',
    api_name: '',
    type: 'string',
    lookupTarget: '',
    is_required: false,
    is_unique: false,
});

const moduleForm = ref({
    name: '',
    singular_name: '',
    title_field: 'name',
});

onMounted(async () => {
    const modules = await metaStore.fetchModules(true);
    if (modules.length > 0) {
        selectModule(modules[0]);
    }
});

const selectModule = async (mod) => {
    await metaStore.loadModule(mod.api_name);
    selectedModule.value = metaStore.currentModule;
};

const handleAddField = async () => {
    const payload = {
        label: fieldForm.value.label,
        api_name: fieldForm.value.api_name || undefined,
        type: fieldForm.value.type,
        is_required: fieldForm.value.is_required,
        is_unique: fieldForm.value.is_unique,
    };

    if (fieldForm.value.type === 'lookup') {
        payload.options = { target_module: fieldForm.value.lookupTarget };
    }

    await metaStore.addField(selectedModule.value.api_name, payload);
    await selectModule(selectedModule.value);
    isAddFieldModalOpen.value = false;
};

const handleCreateModule = async () => {
    const newMod = await metaStore.createModule({
        name: moduleForm.value.name,
        singular_name: moduleForm.value.singular_name || undefined,
        title_field: moduleForm.value.title_field || 'name',
    });

    isCreateModuleModalOpen.value = false;
    await selectModule(newMod);
};
</script>
