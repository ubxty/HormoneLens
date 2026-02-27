@extends('layouts.admin')
@section('heading','Document Manager')

@section('content')
<div x-data="ragDocument()" x-init="init()">

    <a href="{{ route('admin.rag') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-brand-600 mb-4">← Back to Documents</a>

    <div x-show="loading" class="text-center py-16"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

    <div x-show="!loading && doc">
        {{-- Document header --}}
        <div class="bg-white rounded-xl shadow-sm border p-5 mb-6">
            <div class="flex items-start justify-between">
                <div x-show="!editingDoc">
                    <h2 class="text-xl font-bold text-gray-800" x-text="doc?.title"></h2>
                    <p class="text-sm text-gray-500 mt-1" x-text="doc?.description || 'No description'"></p>
                </div>
                <div x-show="editingDoc" class="flex-1 space-y-2 mr-4">
                    <input type="text" x-model="docEdit.title" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    <textarea x-model="docEdit.description" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
                </div>
                <div class="flex gap-2">
                    <button x-show="!editingDoc" @click="editingDoc=true; docEdit={title:doc.title, description:doc.description}"
                            class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg">✏️ Edit</button>
                    <button x-show="editingDoc" @click="saveDoc()" class="px-3 py-1.5 text-xs bg-brand-600 text-white rounded-lg hover:bg-brand-700">Save</button>
                    <button x-show="editingDoc" @click="editingDoc=false" class="px-3 py-1.5 text-xs border text-gray-600 rounded-lg hover:bg-gray-50">Cancel</button>
                </div>
            </div>
        </div>

        {{-- Add root node --}}
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Knowledge Tree</h3>
            <button @click="addNode(null)" class="px-3 py-1.5 text-xs bg-brand-600 text-white rounded-lg hover:bg-brand-700">+ Root Node</button>
        </div>

        {{-- Node tree --}}
        <div x-show="(doc?.nodes||[]).length === 0" class="bg-white rounded-xl border p-8 text-center text-gray-400 text-sm">
            No nodes yet. Add a root node to start building the knowledge tree.
        </div>

        <div class="space-y-2">
            <template x-for="node in doc?.nodes || []" :key="node.id">
                <div>
                    <div x-data="{ collapsed: true }" class="ml-0">
                        {{-- Node item --}}
                        <div class="bg-white rounded-lg shadow-sm border p-3 flex items-center gap-2 hover:bg-gray-50 transition">
                            <button @click="collapsed = !collapsed" class="text-gray-400 hover:text-gray-600 w-5 text-center">
                                <span x-text="collapsed ? '▶' : '▼'" class="text-xs"></span>
                            </button>
                            <div class="flex-1 min-w-0">
                                <span class="font-medium text-gray-700 text-sm" x-text="node.title"></span>
                                <span class="text-xs text-gray-400 ml-2" x-text="'[' + (node.keywords||[]).join(', ') + ']'"></span>
                            </div>
                            <span class="text-xs text-gray-400" x-text="(node.pages||[]).length + 'p / ' + (node.children||[]).length + 'c'"></span>
                            <button @click="addNode(node.id)" class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded" title="Add child node">+ Child</button>
                            <button @click="addPage(node.id)" class="text-xs px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded" title="Add page">+ Page</button>
                            <button @click="editNode(node)" class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded">✏️</button>
                            <button @click="deleteNode(node)" class="text-xs px-2 py-1 text-red-500 hover:bg-red-50 rounded">🗑️</button>
                        </div>

                        {{-- Expanded content --}}
                        <div x-show="!collapsed" class="ml-6 mt-1 space-y-1">
                            {{-- Pages --}}
                            <template x-for="pg in node.pages || []" :key="pg.id">
                                <div class="bg-gray-50 rounded-lg p-3 flex items-start gap-2 text-sm border">
                                    <span class="text-gray-400 mt-0.5">📄</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-700" x-text="'Page #' + pg.page_number"></p>
                                        <p class="text-xs text-gray-500 line-clamp-2 mt-0.5" x-text="pg.content"></p>
                                    </div>
                                    <button @click="editPage(pg)" class="text-xs px-2 py-1 bg-white border hover:bg-gray-100 rounded shrink-0">✏️</button>
                                    <button @click="deletePage(pg)" class="text-xs px-2 py-1 text-red-500 hover:bg-red-50 rounded shrink-0">🗑️</button>
                                </div>
                            </template>

                            {{-- Children (recursive display via nested alpine) --}}
                            <template x-for="child in node.children || []" :key="child.id">
                                <div>
                                    <div x-data="{ coll: true }">
                                        <div class="bg-white rounded-lg shadow-sm border p-3 flex items-center gap-2 hover:bg-gray-50 transition">
                                            <button @click="coll = !coll" class="text-gray-400 hover:text-gray-600 w-5 text-center">
                                                <span x-text="coll ? '▶' : '▼'" class="text-xs"></span>
                                            </button>
                                            <div class="flex-1 min-w-0">
                                                <span class="font-medium text-gray-700 text-sm" x-text="child.title"></span>
                                                <span class="text-xs text-gray-400 ml-2" x-text="'[' + (child.keywords||[]).join(', ') + ']'"></span>
                                            </div>
                                            <span class="text-xs text-gray-400" x-text="(child.pages||[]).length + 'p'"></span>
                                            <button @click="addPage(child.id)" class="text-xs px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded">+ Page</button>
                                            <button @click="editNode(child)" class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded">✏️</button>
                                            <button @click="deleteNode(child)" class="text-xs px-2 py-1 text-red-500 hover:bg-red-50 rounded">🗑️</button>
                                        </div>
                                        <div x-show="!coll" class="ml-6 mt-1 space-y-1">
                                            <template x-for="pg2 in child.pages || []" :key="pg2.id">
                                                <div class="bg-gray-50 rounded-lg p-3 flex items-start gap-2 text-sm border">
                                                    <span class="text-gray-400 mt-0.5">📄</span>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-medium text-gray-700" x-text="'Page #' + pg2.page_number"></p>
                                                        <p class="text-xs text-gray-500 line-clamp-2 mt-0.5" x-text="pg2.content"></p>
                                                    </div>
                                                    <button @click="editPage(pg2)" class="text-xs px-2 py-1 bg-white border hover:bg-gray-100 rounded shrink-0">✏️</button>
                                                    <button @click="deletePage(pg2)" class="text-xs px-2 py-1 text-red-500 hover:bg-red-50 rounded shrink-0">🗑️</button>
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
    <div x-show="modal==='node'" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-transition>
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4" @click.away="modal=null">
            <h3 class="font-semibold text-gray-800 mb-4" x-text="modalData.id ? 'Edit Node' : 'Add Node'"></h3>
            <form @submit.prevent="saveNode()" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" x-model="modalData.title" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keywords (comma-separated)</label>
                    <input type="text" x-model="modalData.keywordsStr" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="e.g. insulin, blood sugar, glucose">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm rounded-lg hover:bg-brand-700">Save</button>
                    <button type="button" @click="modal=null" class="px-4 py-2 border text-gray-600 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Edit/Add Page --}}
    <div x-show="modal==='page'" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-transition>
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4" @click.away="modal=null">
            <h3 class="font-semibold text-gray-800 mb-4" x-text="modalData.id ? 'Edit Page' : 'Add Page'"></h3>
            <form @submit.prevent="savePage()" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Page Number</label>
                    <input type="number" x-model="modalData.page_number" min="1" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea x-model="modalData.content" rows="8" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm rounded-lg hover:bg-brand-700">Save</button>
                    <button type="button" @click="modal=null" class="px-4 py-2 border text-gray-600 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
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
            if(r.success) this.doc = r.data;
            this.loading = false;
        },
        async saveDoc() {
            const r = await api.put('/admin/rag/documents/' + docId, this.docEdit);
            if(r.success){ this.doc.title = this.docEdit.title; this.doc.description = this.docEdit.description; this.editingDoc = false; toast('Document updated'); }
            else toast(r.message||'Failed','error');
        },

        // ── Nodes ──
        addNode(parentId) {
            this.modalData = { title:'', keywordsStr:'', parent_id: parentId, document_id: docId };
            this.modal = 'node';
        },
        editNode(n) {
            this.modalData = { id: n.id, title: n.title, keywordsStr: (n.keywords||[]).join(', ') };
            this.modal = 'node';
        },
        async saveNode() {
            const kw = this.modalData.keywordsStr ? this.modalData.keywordsStr.split(',').map(s=>s.trim()).filter(Boolean) : [];
            if(this.modalData.id) {
                const r = await api.put('/admin/rag/nodes/' + this.modalData.id, { title: this.modalData.title, keywords: kw });
                if(r.success){ toast('Node updated'); await this.reload(); } else toast(r.message||'Failed','error');
            } else {
                const payload = { title: this.modalData.title, keywords: kw, document_id: this.modalData.document_id, parent_id: this.modalData.parent_id };
                const r = await api.post('/admin/rag/nodes', payload);
                if(r.success){ toast('Node created'); await this.reload(); } else toast(r.message||'Failed','error');
            }
            this.modal = null;
        },
        async deleteNode(n) {
            if(!confirm('Delete node "'+n.title+'" and all its children/pages?')) return;
            const r = await api.delete('/admin/rag/nodes/' + n.id);
            if(r.success){ toast('Node deleted'); await this.reload(); } else toast(r.message||'Failed','error');
        },

        // ── Pages ──
        addPage(nodeId) {
            this.modalData = { content:'', page_number:'', node_id: nodeId };
            this.modal = 'page';
        },
        editPage(p) {
            this.modalData = { id: p.id, content: p.content, page_number: p.page_number || '' };
            this.modal = 'page';
        },
        async savePage() {
            const payload = { content: this.modalData.content, page_number: parseInt(this.modalData.page_number) };
            if(this.modalData.id) {
                const r = await api.put('/admin/rag/pages/' + this.modalData.id, payload);
                if(r.success){ toast('Page updated'); await this.reload(); } else toast(r.message||'Failed','error');
            } else {
                payload.node_id = this.modalData.node_id;
                const r = await api.post('/admin/rag/pages', payload);
                if(r.success){ toast('Page created'); await this.reload(); } else toast(r.message||'Failed','error');
            }
            this.modal = null;
        },
        async deletePage(p) {
            if(!confirm('Delete page #'+p.page_number+'?')) return;
            const r = await api.delete('/admin/rag/pages/' + p.id);
            if(r.success){ toast('Page deleted'); await this.reload(); } else toast(r.message||'Failed','error');
        }
    };
}
</script>
@endpush
