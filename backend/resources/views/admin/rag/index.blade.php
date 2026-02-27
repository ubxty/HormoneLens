@extends('layouts.admin')
@section('heading','RAG Knowledge Base')

@section('content')
<div x-data="ragAdmin()" x-init="init()">

    {{-- Create Document --}}
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500" x-text="docs.length + ' documents'"></p>
        <button @click="showCreate = !showCreate"
                class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">
            + New Document
        </button>
    </div>

    {{-- Create form --}}
    <div x-show="showCreate" x-transition class="bg-white rounded-xl shadow-sm border p-5 mb-6">
        <h3 class="font-semibold text-gray-800 mb-3">Create Document</h3>
        <form @submit.prevent="createDoc()" class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" x-model="newDoc.title" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea x-model="newDoc.description" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm"></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" :disabled="creating" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm rounded-lg disabled:opacity-50">Create</button>
                <button type="button" @click="showCreate = false" class="px-4 py-2 border text-gray-600 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
            </div>
        </form>
    </div>

    <div x-show="loading" class="text-center py-12"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

    {{-- Document cards --}}
    <div x-show="!loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="d in docs" :key="d.id">
            <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition group">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-800 group-hover:text-brand-600" x-text="d.title"></h3>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="d.description || 'No description'"></p>
                    </div>
                    <button @click="deleteDoc(d)" class="text-gray-400 hover:text-red-500 text-xs p-1" title="Delete">🗑️</button>
                </div>
                <div class="flex gap-4 text-xs text-gray-500 mb-3">
                    <span x-text="d.nodes_count + ' nodes'"></span>
                    <span x-text="d.pages_count + ' pages'"></span>
                </div>
                <a :href="'/admin/rag/documents/' + d.id"
                   class="inline-block px-3 py-1.5 bg-brand-50 text-brand-700 text-xs font-medium rounded-lg hover:bg-brand-100 transition">
                    Manage →
                </a>
            </div>
        </template>
    </div>

    <div x-show="!loading && docs.length === 0" class="bg-white rounded-xl border p-8 text-center text-gray-400 text-sm">
        <div class="text-4xl mb-2">📚</div>No documents yet. Create your first one!
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
            if(r.success) this.docs = r.data || [];
            this.loading = false;
        },
        async createDoc() {
            this.creating = true;
            const r = await api.post('/admin/rag/documents', this.newDoc);
            if(r.success){ this.docs.push({...r.data, nodes_count:0, pages_count:0}); this.newDoc = {title:'',description:''}; this.showCreate = false; toast('Document created!'); }
            else toast(r.message || 'Failed', 'error');
            this.creating = false;
        },
        async deleteDoc(d) {
            if(!confirm('Delete "' + d.title + '" and all its nodes/pages?')) return;
            const r = await api.delete('/admin/rag/documents/' + d.id);
            if(r.success){ this.docs = this.docs.filter(x => x.id !== d.id); toast('Document deleted'); }
            else toast(r.message || 'Failed', 'error');
        }
    };
}
</script>
@endpush
