<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RagDocument;
use App\Models\RagNode;
use App\Models\RagPage;
use Illuminate\Http\Request;

class RagManagementController extends Controller
{
    // ── Documents ──────────────────────────────────

    public function documents()
    {
        $docs = RagDocument::withCount('nodes')->get()->map(function ($doc) {
            $doc->pages_count = RagPage::whereIn(
                'node_id',
                $doc->nodes()->pluck('id')
            )->count();
            return $doc;
        });

        return response()->json(['success' => true, 'data' => $docs]);
    }

    public function showDocument($id)
    {
        $doc = RagDocument::with(['nodes' => function ($q) {
            $q->with(['children.children.pages', 'pages'])->whereNull('parent_id');
        }])->findOrFail($id);

        // Transform keywords string to array for frontend
        $doc->nodes->each(function ($node) {
            $this->transformNodeKeywords($node);
        });

        return response()->json(['success' => true, 'data' => $doc]);
    }

    private function transformNodeKeywords($node): void
    {
        $node->keywords = $node->keywords
            ? array_map('trim', explode(',', $node->keywords))
            : [];
        foreach ($node->children as $child) {
            $this->transformNodeKeywords($child);
        }
    }

    public function storeDocument(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $doc = RagDocument::create($validated);

        return response()->json(['success' => true, 'data' => $doc], 201);
    }

    public function updateDocument(Request $request, $id)
    {
        $doc = RagDocument::findOrFail($id);
        $validated = $request->validate([
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $doc->update($validated);

        return response()->json(['success' => true, 'data' => $doc]);
    }

    public function destroyDocument($id)
    {
        $doc = RagDocument::findOrFail($id);
        $nodeIds = $doc->nodes()->pluck('id');
        RagPage::whereIn('node_id', $nodeIds)->delete();
        $doc->nodes()->delete();
        $doc->delete();

        return response()->json(['success' => true, 'message' => 'Document deleted.']);
    }

    // ── Nodes ──────────────────────────────────────

    public function storeNode(Request $request)
    {
        $validated = $request->validate([
            'document_id' => ['required', 'exists:rag_documents,id'],
            'parent_id'   => ['nullable', 'exists:rag_nodes,id'],
            'title'       => ['required', 'string', 'max:255'],
            'keywords'    => ['nullable', 'array'],
            'keywords.*'  => ['string', 'max:100'],
            'depth'       => ['nullable', 'integer', 'min:0', 'max:10'],
        ]);

        // Auto-compute depth from parent
        if (!empty($validated['parent_id'])) {
            $parent = RagNode::find($validated['parent_id']);
            $validated['depth'] = ($parent?->depth ?? 0) + 1;
        } else {
            $validated['depth'] = $validated['depth'] ?? 0;
        }

        // Convert keywords array to comma-separated string
        if (isset($validated['keywords'])) {
            $validated['keywords'] = implode(', ', $validated['keywords']);
        }

        $node = RagNode::create($validated);

        return response()->json(['success' => true, 'data' => $node->load('pages')], 201);
    }

    public function updateNode(Request $request, $id)
    {
        $node = RagNode::findOrFail($id);
        $validated = $request->validate([
            'title'      => ['sometimes', 'string', 'max:255'],
            'keywords'   => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:100'],
        ]);

        if (isset($validated['keywords'])) {
            $validated['keywords'] = implode(', ', $validated['keywords']);
        }

        $node->update($validated);

        return response()->json(['success' => true, 'data' => $node]);
    }

    public function destroyNode($id)
    {
        $node = RagNode::findOrFail($id);
        $this->deleteNodeTree($node);

        return response()->json(['success' => true, 'message' => 'Node deleted.']);
    }

    private function deleteNodeTree(RagNode $node): void
    {
        foreach ($node->children as $child) {
            $this->deleteNodeTree($child);
        }
        $node->pages()->delete();
        $node->delete();
    }

    // ── Pages ──────────────────────────────────────

    public function storePage(Request $request)
    {
        $validated = $request->validate([
            'node_id'     => ['required', 'exists:rag_nodes,id'],
            'content'     => ['required', 'string'],
            'page_number' => ['required', 'integer', 'min:1'],
        ]);

        $page = RagPage::create($validated);

        return response()->json(['success' => true, 'data' => $page], 201);
    }

    public function updatePage(Request $request, $id)
    {
        $page = RagPage::findOrFail($id);
        $validated = $request->validate([
            'content'     => ['sometimes', 'string'],
            'page_number' => ['nullable', 'integer', 'min:1'],
        ]);

        $page->update($validated);

        return response()->json(['success' => true, 'data' => $page]);
    }

    public function destroyPage($id)
    {
        RagPage::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Page deleted.']);
    }
}
