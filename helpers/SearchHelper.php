<?php

/**
 * Search Helper
 * Provides utilities for search result highlighting and snippet extraction
 */
class SearchHelper {

    /**
     * Extract text snippets that contain the search keywords
     * Shows context around where keywords appear in the text
     *
     * @param string $fullText The full text content (e.g., from PDF)
     * @param string $searchQuery The search query with keywords
     * @param int $snippetCount Maximum number of snippets to return
     * @param int $contextLength Characters of context before/after keyword
     * @return array Array of snippets with highlighted keywords and page info
     */
    public static function extractHighlightedSnippets($fullText, $searchQuery, $snippetCount = 3, $contextLength = 100) {
        if (empty($fullText) || empty($searchQuery)) {
            return [];
        }

        // Normalize text
        $fullText = self::normalizeText($fullText);

        // Extract search terms (handle multiple words)
        $searchTerms = self::extractSearchTerms($searchQuery);

        if (empty($searchTerms)) {
            return [];
        }

        $snippets = [];
        $foundPositions = [];

        // Find all positions where keywords appear
        foreach ($searchTerms as $term) {
            $termLower = mb_strtolower($term);
            $textLower = mb_strtolower($fullText);
            $offset = 0;

            while (($pos = mb_strpos($textLower, $termLower, $offset)) !== false) {
                $foundPositions[] = [
                    'pos' => $pos,
                    'term' => $term,
                    'length' => mb_strlen($term)
                ];
                $offset = $pos + 1;
            }
        }

        // Sort by position
        usort($foundPositions, function($a, $b) {
            return $a['pos'] - $b['pos'];
        });

        // Extract unique snippets (avoid overlapping)
        $lastSnippetEnd = -1000;
        $extractedCount = 0;
        $textLength = mb_strlen($fullText);

        foreach ($foundPositions as $found) {
            if ($extractedCount >= $snippetCount) {
                break;
            }

            // Skip if too close to last snippet
            if ($found['pos'] - $lastSnippetEnd < 50) {
                continue;
            }

            $snippet = self::extractSnippet($fullText, $found['pos'], $contextLength, $searchTerms);

            if (!empty($snippet)) {
                // Calculate approximate page number (assuming ~2500 chars per page)
                $approximatePage = max(1, floor($found['pos'] / 2500) + 1);

                // Calculate position percentage in document
                $positionPercent = ($found['pos'] / $textLength) * 100;

                $snippets[] = [
                    'text' => $snippet,
                    'page' => $approximatePage,
                    'position' => $found['pos'],
                    'position_percent' => $positionPercent,
                    'search_term' => $found['term']
                ];
                $lastSnippetEnd = $found['pos'] + $contextLength;
                $extractedCount++;
            }
        }

        return $snippets;
    }

    /**
     * Search through page-level text data and return accurate page numbers
     * This is more accurate than character-based estimation
     *
     * @param array $pageTexts Array with page numbers as keys (e.g., [1 => 'text', 2 => 'text'])
     * @param string $searchQuery The search query with keywords
     * @param int $snippetCount Maximum number of snippets to return
     * @param int $contextLength Characters of context before/after keyword
     * @return array Array of snippets with accurate page numbers
     */
    public static function searchByPage($pageTexts, $searchQuery, $snippetCount = 3, $contextLength = 100) {
        if (empty($pageTexts) || empty($searchQuery)) {
            return [];
        }

        $searchTerms = self::extractSearchTerms($searchQuery);
        if (empty($searchTerms)) {
            return [];
        }

        $snippets = [];
        $foundCount = 0;

        // Search through each page
        foreach ($pageTexts as $pageNum => $pageText) {
            if ($foundCount >= $snippetCount) {
                break;
            }

            $pageText = self::normalizeText($pageText);
            $textLower = mb_strtolower($pageText);

            // Find matches on this page
            $pageMatches = [];
            foreach ($searchTerms as $term) {
                $termLower = mb_strtolower($term);
                $offset = 0;

                while (($pos = mb_strpos($textLower, $termLower, $offset)) !== false) {
                    $pageMatches[] = [
                        'pos' => $pos,
                        'term' => $term
                    ];
                    $offset = $pos + 1;
                }
            }

            // Sort matches by position
            usort($pageMatches, function($a, $b) {
                return $a['pos'] - $b['pos'];
            });

            // Extract snippets from this page
            $lastPos = -1000;
            foreach ($pageMatches as $match) {
                if ($foundCount >= $snippetCount) {
                    break;
                }

                // Skip if too close to last snippet
                if ($match['pos'] - $lastPos < 50) {
                    continue;
                }

                $snippet = self::extractSnippet($pageText, $match['pos'], $contextLength, $searchTerms);

                if (!empty($snippet)) {
                    $snippets[] = [
                        'text' => $snippet,
                        'page' => $pageNum,  // Accurate page number!
                        'position' => $match['pos'],
                        'search_term' => $match['term']
                    ];
                    $lastPos = $match['pos'];
                    $foundCount++;
                }
            }
        }

        return $snippets;
    }

    /**
     * Extract a single snippet around a keyword position
     */
    private static function extractSnippet($text, $position, $contextLength, $searchTerms) {
        $textLength = mb_strlen($text);

        // Calculate start and end positions
        $start = max(0, $position - $contextLength);
        $end = min($textLength, $position + $contextLength);

        // Try to break at word boundaries
        if ($start > 0) {
            $spacePos = mb_strpos($text, ' ', $start);
            if ($spacePos !== false && $spacePos < $start + 20) {
                $start = $spacePos + 1;
            }
        }

        if ($end < $textLength) {
            $spacePos = mb_strrpos(mb_substr($text, 0, $end), ' ');
            if ($spacePos !== false && $spacePos > $end - 20) {
                $end = $spacePos;
            }
        }

        // Extract the snippet
        $snippet = mb_substr($text, $start, $end - $start);

        // Add ellipsis
        $snippet = ($start > 0 ? '...' : '') . trim($snippet) . ($end < $textLength ? '...' : '');

        // Highlight all search terms in the snippet
        $snippet = self::highlightKeywords($snippet, $searchTerms);

        return $snippet;
    }

    /**
     * Highlight keywords in text with HTML markup
     */
    public static function highlightKeywords($text, $keywords) {
        if (empty($text) || empty($keywords)) {
            return htmlspecialchars($text);
        }

        // Escape HTML first
        $text = htmlspecialchars($text);

        // If keywords is a string, convert to array
        if (is_string($keywords)) {
            $keywords = self::extractSearchTerms($keywords);
        }

        // Sort keywords by length (longest first) to avoid partial replacements
        usort($keywords, function($a, $b) {
            return mb_strlen($b) - mb_strlen($a);
        });

        // Highlight each keyword
        foreach ($keywords as $keyword) {
            if (mb_strlen($keyword) < 2) {
                continue; // Skip very short keywords
            }

            // Use case-insensitive regex replacement
            $pattern = '/(' . preg_quote($keyword, '/') . ')/ui';
            $text = preg_replace($pattern, '<mark class="search-highlight">$1</mark>', $text);
        }

        return $text;
    }

    /**
     * Extract search terms from query string
     * Handles quoted phrases and individual words
     */
    private static function extractSearchTerms($query) {
        $terms = [];

        // First, extract quoted phrases
        if (preg_match_all('/"([^"]+)"/', $query, $matches)) {
            $terms = array_merge($terms, $matches[1]);
            // Remove quoted phrases from query
            $query = preg_replace('/"[^"]+"/', '', $query);
        }

        // Then extract individual words (3+ characters)
        $words = preg_split('/\s+/', trim($query));
        foreach ($words as $word) {
            $word = trim($word);
            if (mb_strlen($word) >= 2) {
                $terms[] = $word;
            }
        }

        return array_unique($terms);
    }

    /**
     * Normalize text for better searching
     */
    private static function normalizeText($text) {
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove special characters that might interfere
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);

        return trim($text);
    }

    /**
     * Count keyword occurrences in text
     */
    public static function countKeywordOccurrences($text, $keywords) {
        if (empty($text) || empty($keywords)) {
            return 0;
        }

        if (is_string($keywords)) {
            $keywords = self::extractSearchTerms($keywords);
        }

        $count = 0;
        $textLower = mb_strtolower($text);

        foreach ($keywords as $keyword) {
            $keywordLower = mb_strtolower($keyword);
            $count += mb_substr_count($textLower, $keywordLower);
        }

        return $count;
    }

    /**
     * Get keyword statistics for a document
     * Returns information about where and how often keywords appear
     */
    public static function getKeywordStats($fullText, $searchQuery) {
        if (empty($fullText) || empty($searchQuery)) {
            return [
                'total_occurrences' => 0,
                'unique_terms' => 0,
                'sections' => []
            ];
        }

        $searchTerms = self::extractSearchTerms($searchQuery);
        $textLength = mb_strlen($fullText);

        // Divide text into sections (pages/chunks)
        $sectionSize = 2000; // ~1 page of text
        $sectionCount = ceil($textLength / $sectionSize);

        $sections = [];
        $totalOccurrences = 0;

        for ($i = 0; $i < $sectionCount; $i++) {
            $sectionStart = $i * $sectionSize;
            $sectionText = mb_substr($fullText, $sectionStart, $sectionSize);
            $occurrences = self::countKeywordOccurrences($sectionText, $searchTerms);

            if ($occurrences > 0) {
                $sections[] = [
                    'section' => $i + 1,
                    'occurrences' => $occurrences,
                    'approximate_page' => floor($i / 2) + 1 // Rough page estimate
                ];
                $totalOccurrences += $occurrences;
            }
        }

        return [
            'total_occurrences' => $totalOccurrences,
            'unique_terms' => count($searchTerms),
            'sections' => $sections,
            'terms' => $searchTerms
        ];
    }

    /**
     * Highlight title matches
     */
    public static function highlightTitle($title, $searchQuery) {
        if (empty($searchQuery)) {
            return htmlspecialchars($title);
        }

        return self::highlightKeywords($title, $searchQuery);
    }

    /**
     * Highlight abstract/description matches
     */
    public static function highlightAbstract($abstract, $searchQuery, $maxLength = 300) {
        if (empty($abstract)) {
            return '';
        }

        // Truncate if too long
        if (mb_strlen($abstract) > $maxLength) {
            $abstract = mb_substr($abstract, 0, $maxLength) . '...';
        }

        if (empty($searchQuery)) {
            return htmlspecialchars($abstract);
        }

        return self::highlightKeywords($abstract, $searchQuery);
    }
}
