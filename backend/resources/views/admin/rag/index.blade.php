@extends('layouts.admin')
@section('heading','RAG Knowledge Base')

@section('content')
<div x-data="ragAdmin()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5 adm-a adm-d0" data-adm>
        <p class="text-xs font-bold text-gray-400" x-text="docs.length + ' documents'"></p>
        <button @click="showCreate = !showCreate" class="adm-btn">+ New Document</button>
    </div>

    {{-- Create form --}}
    <div x-show="showCreate" x-transition class="adm-card relative p-5 mb-5 adm-a" data-adm>
        <h3 class="text-xs font-bold text-gray-700 mb-3 flex items-center gap-2">
            <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs">&#128221;</div>
            Create Document
        </h3>
        <form @submit.prevent="createDoc()" class="space-y-3">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Title</label>
                <input type="text" x-model="newDoc.title" required class="adm-input w-full">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Description</label>
                <textarea x-model="newDoc.description" rows="2" class="adm-input w-full"></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" :disabled="creating" class="adm-btn disabled:opacity-50">Create</button>
                <button type="button" @click="showCreate = false" class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 cursor-pointer transition px-4 py-2">Cancel</button>
            </div>
        </form>
    </div>

    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    {{-- Document cards --}}
    <div x-show="!loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="(d, i) in docs" :key="d.id">
            <div class="adm-card relative p-5 group adm-a" :class="'adm-d' + Math.min(i, 5)" data-adm>
                <div class="flex items-start justify-between mb-3">
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-700 group-hover:adm-grad-text truncate" x-text="d.title"></h3>
                        <p class="text-[10px] text-gray-400 mt-0.5 line-clamp-2" x-text="d.description || 'No description'"></p>
                    </div>
                    <button @click="deleteDoc(d)" class="text-gray-300 hover:text-red-500 text-xs p-1 transition shrink-0" title="Delete">&#128465;</button>
                </div>
                <div class="flex gap-4 text-[10px] font-bold text-gray-400 mb-3">
                    <span x-text="d.nodes_count + ' nodes'"></span>
                    <span x-text="d.pages_count + ' pages'"></span>
                </div>
                <a :href="'/admin/rag/documents/' + d.id"
                   class="adm-badge bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 hover:from-purple-200 hover:to-pink-200 cursor-pointer transition">
                    Manage &#8594;
                </a>
            </div>
        </template>
    </div>

    <div x-show="!loading && docs.length === 0" class="adm-card relative p-10 text-center adm-a adm-d0" data-adm>
        <div class="text-4xl mb-2">&#128218;</div>
        <p class="text-xs text-gray-400">No documents yet. Create your first one!</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ragAdmin() {
    return {
        loading: true, docs: [], showCreate: false, creating: false,
        newDoc: { title: '', description: '' },
        async init() {
            const r = await api.get('/admin/rag/documents');
            if (r.success) this.docs = r.data || [];
            this.loading = false;
            this.$nextTick(() => admAnimate());
        },
        async createDoc() {
            this.creating = true;
            const r = await api.post('/admin/rag/documents', this.newDoc);
            if (r.success) { this.docs.push({...r.data, nodes_count: 0, pages_count: 0}); this.newDoc = { title: '', description: '' }; this.showCreate = false; toast('Document created!'); }
            else toast(r.message || 'Failed', 'error');
            this.creating = false;
        },
        async deleteDoc(d) {
            if (!confirm('Delete "' + d.title + '" and all its nodes/pages?')) return;
            const r = await api.delete('/admin/rag/documents/' + d.id);
            if (r.success) { this.docs = this.docs.filter(x => x.id !== d.id); toast('Document deleted'); }
            else toast(r.message || 'Failed', 'error');
        }
    };
}
</script>
@endpush
