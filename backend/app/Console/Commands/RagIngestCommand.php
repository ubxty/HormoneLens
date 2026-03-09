<?php

namespace App\Console\Commands;

use App\Models\RagDocument;
use App\Models\RagNode;
use App\Models\RagPage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RagIngestCommand extends Command
{
    protected $signature = 'rag:ingest
        {file : Path to the text/markdown file to ingest}
        {--title= : Document title (defaults to filename)}
        {--description= : Document description}
        {--delimiter=## : Heading delimiter for splitting into nodes (## for h2, ### for h3)}
        {--keywords-per-node=10 : Max keywords extracted per node}';

    protected $description = 'Ingest a text/markdown file into the RAG knowledge base as a hierarchical document → node → page structure.';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $content = file_get_contents($filePath);
        if (empty(trim($content))) {
            $this->error('File is empty.');
            return self::FAILURE;
        }

        $title = $this->option('title') ?? pathinfo($filePath, PATHINFO_FILENAME);
        $description = $this->option('description') ?? "Ingested from {$filePath}";
        $delimiter = $this->option('delimiter');
        $maxKeywords = (int) $this->option('keywords-per-node');

        $this->info("Ingesting: {$title}");

        DB::beginTransaction();
        try {
            // Create document
            $document = RagDocument::create([
                'title' => $title,
                'description' => $description,
            ]);

            // Split content into sections by heading delimiter
            $sections = $this->splitByHeadings($content, $delimiter);

            if (empty($sections)) {
                // No headings found — treat entire content as one node
                $sections = [['title' => $title, 'content' => $content, 'depth' => 0, 'children' => []]];
            }

            $nodeCount = 0;
            $pageCount = 0;

            foreach ($sections as $section) {
                $result = $this->createNodeTree($document->id, null, $section, $maxKeywords);
                $nodeCount += $result['nodes'];
                $pageCount += $result['pages'];
            }

            DB::commit();

            $this->info("Created document #{$document->id} with {$nodeCount} nodes and {$pageCount} pages.");
            return self::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Ingestion failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * Split markdown content by heading levels into a hierarchical structure.
     */
    private function splitByHeadings(string $content, string $delimiter): array
    {
        $lines = explode("\n", $content);
        $sections = [];
        $currentSection = null;
        $delimLevel = substr_count($delimiter, '#');

        foreach ($lines as $line) {
            // Match heading lines (## Title, ### Title, etc.)
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $match)) {
                $headingLevel = strlen($match[1]);

                if ($headingLevel <= $delimLevel) {
                    // Save previous section
                    if ($currentSection !== null) {
                        $sections[] = $currentSection;
                    }
                    $currentSection = [
                        'title' => trim($match[2]),
                        'content' => '',
                        'depth' => $headingLevel - $delimLevel,
                        'children' => [],
                    ];
                    continue;
                }
            }

            // Append line to current section
            if ($currentSection !== null) {
                $currentSection['content'] .= $line . "\n";
            } else {
                // Content before first heading — create intro section
                if (trim($line) !== '') {
                    $currentSection = [
                        'title' => 'Overview',
                        'content' => $line . "\n",
                        'depth' => 0,
                        'children' => [],
                    ];
                }
            }
        }

        // Save last section
        if ($currentSection !== null) {
            $sections[] = $currentSection;
        }

        return $sections;
    }

    /**
     * Create a node (and its pages) recursively.
     */
    private function createNodeTree(int $documentId, ?int $parentId, array $section, int $maxKeywords): array
    {
        $content = trim($section['content']);
        $keywords = $this->extractKeywords($section['title'] . ' ' . $content, $maxKeywords);

        $node = RagNode::create([
            'document_id' => $documentId,
            'parent_id' => $parentId,
            'title' => $section['title'],
            'summary' => mb_substr($content, 0, 300),
            'keywords' => implode(',', $keywords),
            'depth' => max(0, $section['depth']),
        ]);

        $nodeCount = 1;
        $pageCount = 0;

        // Split content into pages (~1000 chars each)
        if (!empty($content)) {
            $pages = $this->splitIntoPages($content);
            foreach ($pages as $index => $pageContent) {
                RagPage::create([
                    'node_id' => $node->id,
                    'page_number' => $index + 1,
                    'content' => $pageContent,
                ]);
                $pageCount++;
            }
        }

        // Process children
        foreach ($section['children'] as $child) {
            $result = $this->createNodeTree($documentId, $node->id, $child, $maxKeywords);
            $nodeCount += $result['nodes'];
            $pageCount += $result['pages'];
        }

        return ['nodes' => $nodeCount, 'pages' => $pageCount];
    }

    /**
     * Extract keywords from text by frequency analysis.
     */
    private function extractKeywords(string $text, int $max): array
    {
        $text = strtolower(strip_tags($text));
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Remove stopwords
        $stopwords = ['the', 'a', 'an', 'is', 'are', 'was', 'were', 'be', 'been', 'being',
            'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should',
            'may', 'might', 'shall', 'can', 'to', 'of', 'in', 'for', 'on', 'with', 'at',
            'by', 'from', 'as', 'into', 'through', 'during', 'before', 'after', 'above',
            'below', 'between', 'out', 'off', 'over', 'under', 'again', 'further', 'then',
            'once', 'and', 'but', 'or', 'nor', 'not', 'so', 'no', 'yet', 'this', 'that',
            'these', 'those', 'it', 'its', 'he', 'she', 'they', 'them', 'we', 'you', 'who',
            'which', 'what', 'each', 'every', 'any', 'all', 'both', 'few', 'more', 'most',
            'other', 'some', 'such', 'than', 'too', 'very', 'just', 'about', 'also', 'how',
        ];

        $filtered = array_filter($words, fn ($w) => strlen($w) > 2 && !in_array($w, $stopwords));

        $freq = array_count_values($filtered);
        arsort($freq);

        return array_slice(array_keys($freq), 0, $max);
    }

    /**
     * Split content into page-sized chunks (~1000 chars), breaking at paragraph boundaries.
     */
    private function splitIntoPages(string $content, int $targetSize = 1000): array
    {
        $paragraphs = preg_split('/\n\s*\n/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $pages = [];
        $currentPage = '';

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if (empty($para)) continue;

            if (mb_strlen($currentPage) + mb_strlen($para) > $targetSize && !empty($currentPage)) {
                $pages[] = trim($currentPage);
                $currentPage = $para;
            } else {
                $currentPage .= ($currentPage ? "\n\n" : '') . $para;
            }
        }

        if (!empty(trim($currentPage))) {
            $pages[] = trim($currentPage);
        }

        return $pages ?: [$content];
    }
}
