@extends('layouts.admin')
@section('heading','Document Manager')

@section('content')
<div x-data="ragDocument()" x-init="init()">

    <a href="{{ route('admin.rag') }}" class="inline-flex items-center gap-1 text-xs font-bold text-purple-500 hover:text-purple-700 mb-4 transition adm-a adm-d0" data-adm>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Documents
    </a>

    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading && doc">
        {{-- Document header --}}
        <div class="adm-card relative p-5 mb-5 adm-a adm-d0" data-adm>
            <div class="flex items-start justify-between">
                <div x-show="!editingDoc" class="min-w-0">
                    <h2 class="text-lg font-bold text-gray-800" x-text="doc?.title"></h2>
                    <p class="text-xs text-gray-400 mt-1" x-text="doc?.description || 'No description'"></p>
                </div>
                <div x-show="editingDoc" class="flex-1 space-y-2 mr-4">
                    <input type="text" x-model="docEdit.title" class="adm-input w-full">
                    <textarea x-model="docEdit.description" rows="2" class="adm-input w-full"></textarea>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button x-show="!editingDoc" @click="editingDoc=true; docEdit={title:doc.title, description:doc.description}"
                            class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 cursor-pointer transition">&#9999;&#65039; Edit</button>
                    <button x-show="editingDoc" @click="saveDoc()" class="adm-btn text-xs">Save</button>
                    <button x-show="editingDoc" @click="editingDoc=false" class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 cursor-pointer transition">Cancel</button>
                </div>
            </div>
        </div>

        {{-- Add root node --}}
        <div class="flex items-center justify-between mb-4 adm-a adm-d1" data-adm>
            <h3 class="text-xs font-bold text-gray-700 flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs">&#127795;</div>
                Knowledge Tree
            </h3>
            <button @click="addNode(null)" class="adm-btn text-xs">+ Root Node</button>
        </div>

        {{-- Empty state --}}
        <div x-show="(doc?.nodes||[]).length === 0" class="adm-card relative p-10 text-center">
            <p class="text-xs text-gray-400">No nodes yet. Add a root node to start building the knowledge tree.</p>
        </div>

        {{-- Node tree --}}
        <div class="space-y-2">
            <template x-for="node in doc?.nodes || []" :key="node.id">
                <div>
                    <div x-data="{ collapsed: true }" class="ml-0">
                        {{-- Node item --}}
                        <div class="adm-card relative p-3 flex items-center gap-2 hover:bg-white/60 transition cursor-pointer" @click="collapsed = !collapsed">
                            <button class="text-gray-400 hover:text-gray-600 w-5 text-center">
                                <span x-text="collapsed ? '&#9654;' : '&#9660;'" class="text-[10px]"></span>
                            </button>
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs shrink-0">&#128302;</div>
                            <div class="flex-1 min-w-0">
                                <span class="font-bold text-gray-700 text-xs" x-text="node.title"></span>
                                <span class="text-[10px] text-gray-400 ml-2" x-text="'[' + (node.keywords||[]).join(', ') + ']'"></span>
                            </div>
                            <span class="adm-badge bg-white/60 text-gray-400" x-text="(node.pages||[]).length + 'p / ' + (node.children||[]).length + 'c'"></span>
                            <button @click.stop="addNode(node.id)" class="adm-badge bg-purple-100/60 text-purple-700 hover:bg-purple-200/60 cursor-pointer transition" title="Add child">+ Child</button>
                            <button @click.stop="addPage(node.id)" class="adm-badge bg-blue-100/60 text-blue-700 hover:bg-blue-200/60 cursor-pointer transition" title="Add page">+ Page</button>
                            <button @click.stop="editNode(node)" class="adm-badge bg-white/60 text-gray-500 hover:bg-white/80 cursor-pointer transition">&#9999;&#65039;</button>
                            <button @click.stop="deleteNode(node)" class="adm-badge bg-red-50/60 text-red-500 hover:bg-red-100/60 cursor-pointer transition">&#128465;</button>
                        </div>

                        {{-- Expanded content --}}
                        <div x-show="!collapsed" x-transition class="ml-6 mt-1 space-y-1">
                            {{-- Pages --}}
                            <template x-for="pg in node.pages || []" :key="pg.id">
                                <div class="bg-white/40 backdrop-blur-sm rounded-xl border border-white/50 p-3 flex items-start gap-2 text-xs">
                                    <span class="text-gray-400 mt-0.5">&#128196;</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-700" x-text="'Page #' + pg.page_number"></p>
                                        <p class="text-[10px] text-gray-400 line-clamp-2 mt-0.5" x-text="pg.content"></p>
                                    </div>
                                    <button @click="editPage(pg)" class="adm-badge bg-white/60 text-gray-500 hover:bg-white/80 cursor-pointer transition shrink-0">&#9999;&#65039;</button>
                                    <button @click="deletePage(pg)" class="adm-badge bg-red-50/60 text-red-500 hover:bg-red-100/60 cursor-pointer transition shrink-0">&#128465;</button>
                                </div>
                            </template>

                            {{-- Children --}}
                            <template x-for="child in node.children || []" :key="child.id">
                                <div>
                                    <div x-data="{ coll: true }">
                                        <div class="adm-card relative p-3 flex items-center gap-2 hover:bg-white/60 transition cursor-pointer" @click="coll = !coll">
                                            <button class="text-gray-400 hover:text-gray-600 w-5 text-center">
                                                <span x-text="coll ? '&#9654;' : '&#9660;'" class="text-[10px]"></span>
                                            </button>
                                            <div class="w-6 h-6 rounded bg-gradient-to-br from-blue-400/20 to-purple-400/20 flex items-center justify-center text-[10px] shrink-0">&#128160;</div>
                                            <div class="flex-1 min-w-0">
                                                <span class="font-bold text-gray-700 text-xs" x-text="child.title"></span>
                                                <span class="text-[10px] text-gray-400 ml-2" x-text="'[' + (child.keywords||[]).join(', ') + ']'"></span>
                                            </div>
                                            <span class="adm-badge bg-white/60 text-gray-400" x-text="(child.pages||[]).length + 'p'"></span>
                                            <button @click.stop="addPage(child.id)" class="adm-badge bg-blue-100/60 text-blue-700 hover:bg-blue-200/60 cursor-pointer transition">+ Page</button>
                                            <button @click.stop="editNode(child)" class="adm-badge bg-white/60 text-gray-500 hover:bg-white/80 cursor-pointer transition">&#9999;&#65039;</button>
                                            <button @click.stop="deleteNode(child)" class="adm-badge bg-red-50/60 text-red-500 hover:bg-red-100/60 cursor-pointer transition">&#128465;</button>
                                        </div>
                                        <div x-show="!coll" x-transition class="ml-6 mt-1 space-y-1">
                                            <template x-for="pg2 in child.pages || []" :key="pg2.id">
                                                <div class="bg-white/40 backdrop-blur-sm rounded-xl border border-white/50 p-3 flex items-start gap-2 text-xs">
                                                    <span class="text-gray-400 mt-0.5">&#128196;</span>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-bold text-gray-700" x-text="'Page #' + pg2.page_number"></p>
                                                        <p class="text-[10px] text-gray-400 line-clamp-2 mt-0.5" x-text="pg2.content"></p>
                                                    </div>
                                                    <button @click="editPage(pg2)" class="adm-badge bg-white/60 text-gray-500 hover:bg-white/80 cursor-pointer transition shrink-0">&#9999;&#65039;</button>
                                                    <button @click="deletePage(pg2)" class="adm-badge bg-red-50/60 text-red-500 hover:bg-red-100/60 cursor-pointer transition shrink-0">&#128465;</button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Modal: Edit/Add Node --}}
    <div x-show="modal==='node'" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" x-transition>
        <div class="adm-card relative p-6 w-full max-w-md mx-4 shadow-2xl" @click.away="modal=null">
            <h3 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs">&#128302;</div>
                <span x-text="modalData.id ? 'Edit Node' : 'Add Node'"></span>
            </h3>
            <form @submit.prevent="saveNode()" class="space-y-3">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Title</label>
                    <input type="text" x-model="modalData.title" required class="adm-input w-full">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Keywords (comma-separated)</label>
                    <input type="text" x-model="modalData.keywordsStr" class="adm-input w-full" placeholder="e.g. insulin, blood sugar, glucose">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="adm-btn">Save</button>
                    <button type="button" @click="modal=null" class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 cursor-pointer transition px-4 py-2">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Edit/Add Page --}}
    <div x-show="modal==='page'" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" x-transition>
        <div class="adm-card relative p-6 w-full max-w-lg mx-4 shadow-2xl" @click.away="modal=null">
            <h3 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-blue-400/20 to-purple-400/20 flex items-center justify-center text-xs">&#128196;</div>
                <span x-text="modalData.id ? 'Edit Page' : 'Add Page'"></span>
            </h3>
            <form @submit.prevent="savePage()" class="space-y-3">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Page Number</label>
                    <input type="number" x-model="modalData.page_number" min="1" required class="adm-input w-full">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Content</label>
                    <textarea x-model="modalData.content" rows="8" required class="adm-input w-full"></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="adm-btn">Save</button>
                    <button type="button" @click="modal=null" class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 cursor-pointer transition px-4 py-2">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ragDocument() {
    const docId = @json($id);
    return {
        loading: true, doc: null, editingDoc: false, docEdit: {},
        modal: null, modalData: {},
        async init() {
            await this.reload();
        },
        async reload() {
            this.loading = true;
            const r = await api.get('/admin/rag/documents/' + docId);
            if (r.success) this.doc = r.data;
            this.loading = false;
            this.$nextTick(() => admAnimate());
        },
        async saveDoc() {
            const r = await api.put('/admin/rag/documents/' + docId, this.docEdit);
            if (r.success) { this.doc.title = this.docEdit.title; this.doc.description = this.docEdit.description; this.editingDoc = false; toast('Document updated'); }
            else toast(r.message || 'Failed', 'error');
        },

        // -- Nodes --
        addNode(parentId) {
            this.modalData = { title: '', keywordsStr: '', parent_id: parentId, document_id: docId };
            this.modal = 'node';
        },
        editNode(n) {
            this.modalData = { id: n.id, title: n.title, keywordsStr: (n.keywords || []).join(', ') };
            this.modal = 'node';
        },
        async saveNode() {
            const kw = this.modalData.keywordsStr ? this.modalData.keywordsStr.split(',').map(s => s.trim()).filter(Boolean) : [];
            if (this.modalData.id) {
                const r = await api.put('/admin/rag/nodes/' + this.modalData.id, { title: this.modalData.title, keywords: kw });
                if (r.success) { toast('Node updated'); await this.reload(); } else toast(r.message || 'Failed', 'error');
            } else {
                const payload = { title: this.modalData.title, keywords: kw, document_id: this.modalData.document_id, parent_id: this.modalData.parent_id };
                const r = await api.post('/admin/rag/nodes', payload);
                if (r.success) { toast('Node created'); await this.reload(); } else toast(r.message || 'Failed', 'error');
            }
            this.modal = null;
        },
        async deleteNode(n) {
            if (!confirm('Delete node "' + n.title + '" and all its children/pages?')) return;
            const r = await api.delete('/admin/rag/nodes/' + n.id);
            if (r.success) { toast('Node deleted'); await this.reload(); } else toast(r.message || 'Failed', 'error');
        },

        // -- Pages --
        addPage(nodeId) {
            this.modalData = { content: '', page_number: '', node_id: nodeId };
            this.modal = 'page';
        },
        editPage(p) {
            this.modalData = { id: p.id, content: p.content, page_number: p.page_number || '' };
            this.modal = 'page';
        },
        async savePage() {
            const payload = { content: this.modalData.content, page_number: parseInt(this.modalData.page_number) };
            if (this.modalData.id) {
                const r = await api.put('/admin/rag/pages/' + this.modalData.id, payload);
                if (r.success) { toast('Page updated'); await this.reload(); } else toast(r.message || 'Failed', 'error');
            } else {
                payload.node_id = this.modalData.node_id;
                const r = await api.post('/admin/rag/pages', payload);
                if (r.success) { toast('Page created'); await this.reload(); } else toast(r.message || 'Failed', 'error');
            }
            this.modal = null;
        },
        async deletePage(p) {
            if (!confirm('Delete page #' + p.page_number + '?')) return;
            const r = await api.delete('/admin/rag/pages/' + p.id);
            if (r.success) { toast('Page deleted'); await this.reload(); } else toast(r.message || 'Failed', 'error');
        }
    };
}
</script>
@endpush
