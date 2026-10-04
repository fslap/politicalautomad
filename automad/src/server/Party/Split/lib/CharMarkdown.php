<?php

namespace Lib;

/**
 * CharMarkdown - A character-by-character Markdown parser in PHP.
 * No regex patterns used - pure string manipulation and character analysis.
 * 
 * Supports:
 * - Headers (# ## ### etc.)
 * - Links [text](url)
 * - Images ![alt](src)
 * - Bold **text** and __text__
 * - Emphasis *text* and _text_
 * - Strikethrough ~~text~~
 * - Inline code `code`
 * - Code blocks ```code```
 * - Lists (ordered and unordered)
 * - Blockquotes > text
 * - Horizontal rules ---
 * - Tables (GitHub-style)
 * - Paragraphs
 * - Checkboxes [ ] and [x]
 *
 * Author: Based on Slimdown but rewritten without regex
 * License: MIT
 */
class CharMarkdown {
    
    private $text;
    private $pos;
    private $len;
    private $output;
    private $codeBlocks;
    
    public function __construct() {
        $this->reset();
    }
    
    private function reset() {
        $this->text = '';
        $this->pos = 0;
        $this->len = 0;
        $this->output = '';
        $this->codeBlocks = [];
    }
    
    public function render($text) {
        $this->reset();
        $this->text = "\n" . $text . "\n";
        $this->len = strlen($this->text);
        $this->pos = 0;
        
        $lines = $this->splitIntoLines();
        $this->processLines($lines);
        
        return trim($this->output);
    }
    
    private function splitIntoLines() {
        $lines = [];
        $currentLine = '';
        
        for ($i = 0; $i < $this->len; $i++) {
            $char = $this->text[$i];
            if ($char === "\n") {
                $lines[] = $currentLine;
                $currentLine = '';
            } else {
                $currentLine .= $char;
            }
        }
        
        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }
        
        return $lines;
    }
    
    private function processLines($lines) {
        $inCodeBlock = false;
        $codeBlockContent = '';
        $inList = false;
        $listType = '';
        $inBlockquote = false;
        $inTable = false;
        $tableRows = [];
        $tableHeaders = [];
        
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);
            
            // Handle code blocks first
            if ($this->startsWithString($trimmed, '```')) {
                if (!$inCodeBlock) {
                    $inCodeBlock = true;
                    $codeBlockContent = '';
                } else {
                    $inCodeBlock = false;
                    $this->addCodeBlock($codeBlockContent);
                }
                continue;
            }
            
            if ($inCodeBlock) {
                $codeBlockContent .= $line . "\n";
                continue;
            }
            
            // Check for table
            if ($this->isTableRow($trimmed)) {
                if (!$inTable) {
                    $inTable = true;
                    $tableRows = [];
                    $tableHeaders = $this->parseTableRow($trimmed);
                } else {
                    // Check if it's a separator row
                    if ($this->isTableSeparator($trimmed)) {
                        continue;
                    }
                    $tableRows[] = $this->parseTableRow($trimmed);
                }
                continue;
            } else if ($inTable) {
                // End of table
                $this->renderTable($tableHeaders, $tableRows);
                $inTable = false;
                $tableRows = [];
                $tableHeaders = [];
            }
            
            // Empty line handling
            if (empty($trimmed)) {
                if ($inList) {
                    $this->closeList($listType);
                    $inList = false;
                }
                if ($inBlockquote) {
                    $this->output .= "</blockquote>\n";
                    $inBlockquote = false;
                }
                continue;
            }
            
            // Headers
            if ($this->charAt($trimmed, 0) === '#') {
                $headerLevel = $this->countLeadingChars($trimmed, '#');
                if ($headerLevel > 0 && $headerLevel <= 6) {
                    $headerText = trim(substr($trimmed, $headerLevel));
                    $this->output .= "<h{$headerLevel}>" . $this->processInlineElements($headerText) . "</h{$headerLevel}>\n";
                    continue;
                }
            }
            
            // Horizontal rule
            if ($this->isHorizontalRule($trimmed)) {
                $this->output .= "<hr>\n";
                continue;
            }
            
            // Lists
            if ($this->isListItem($trimmed)) {
                $isOrdered = $this->isOrderedListItem($trimmed);
                $currentListType = $isOrdered ? 'ol' : 'ul';
                
                if (!$inList || $listType !== $currentListType) {
                    if ($inList) {
                        $this->closeList($listType);
                    }
                    $this->openList($currentListType);
                    $inList = true;
                    $listType = $currentListType;
                }
                
                $itemText = $this->extractListItemText($trimmed);
                $this->output .= "\t<li>" . $this->processInlineElements($itemText) . "</li>\n";
                continue;
            } else if ($inList) {
                $this->closeList($listType);
                $inList = false;
            }
            
            // Blockquotes
            if ($this->charAt($trimmed, 0) === '>') {
                if (!$inBlockquote) {
                    $this->output .= "<blockquote>";
                    $inBlockquote = true;
                }
                $quoteText = trim(substr($trimmed, 1));
                $this->output .= $this->processInlineElements($quoteText) . "\n";
                continue;
            } else if ($inBlockquote) {
                $this->output .= "</blockquote>\n";
                $inBlockquote = false;
            }
            
            // Regular paragraphs
            if (!$this->startsWithHtmlTag($trimmed)) {
                $this->output .= "<p>" . $this->processInlineElements($trimmed) . "</p>\n";
            } else {
                $this->output .= $line . "\n";
            }
        }
        
        // Close any open elements
        if ($inList) {
            $this->closeList($listType);
        }
        if ($inBlockquote) {
            $this->output .= "</blockquote>\n";
        }
        if ($inTable) {
            $this->renderTable($tableHeaders, $tableRows);
        }
        
        // Reinsert code blocks
        $this->reinsertCodeBlocks();
    }
    
    private function processInlineElements($text) {
        $result = '';
        $len = strlen($text);
        $i = 0;
        
        while ($i < $len) {
            $char = $text[$i];
            
            // Code spans (highest priority)
            if ($char === '`') {
                $codeSpan = $this->extractCodeSpan($text, $i);
                if ($codeSpan !== null) {
                    $result .= '<code>' . htmlentities($codeSpan['content']) . '</code>';
                    $i = $codeSpan['endPos'];
                    continue;
                }
            }
            
            // Images
            if ($char === '!' && $i + 1 < $len && $text[$i + 1] === '[') {
                $img = $this->extractImage($text, $i);
                if ($img !== null) {
                    $result .= "<img src='" . $img['url'] . "' alt='" . $img['alt'] . "'>";
                    $i = $img['endPos'];
                    continue;
                }
            }
            
            // Links
            if ($char === '[') {
                $link = $this->extractLink($text, $i);
                if ($link !== null) {
                    $result .= "<a href='" . $link['url'] . "'>" . $link['text'] . "</a>";
                    $i = $link['endPos'];
                    continue;
                }
            }
            
            // Bold and emphasis
            if ($char === '*' || $char === '_') {
                $emphasis = $this->extractEmphasis($text, $i, $char);
                if ($emphasis !== null) {
                    if ($emphasis['type'] === 'bold') {
                        $result .= '<strong>' . $this->processInlineElements($emphasis['content']) . '</strong>';
                    } else {
                        $result .= '<em>' . $this->processInlineElements($emphasis['content']) . '</em>';
                    }
                    $i = $emphasis['endPos'];
                    continue;
                }
            }
            
            // Strikethrough
            if ($char === '~' && $i + 1 < $len && $text[$i + 1] === '~') {
                $strike = $this->extractStrikethrough($text, $i);
                if ($strike !== null) {
                    $result .= '<del>' . $this->processInlineElements($strike['content']) . '</del>';
                    $i = $strike['endPos'];
                    continue;
                }
            }
            
            $result .= $char;
            $i++;
        }
        
        return $result;
    }
    
    private function extractCodeSpan($text, $start) {
        $len = strlen($text);
        $i = $start + 1;
        $content = '';
        
        while ($i < $len && $text[$i] !== '`') {
            $content .= $text[$i];
            $i++;
        }
        
        if ($i < $len && $text[$i] === '`') {
            return ['content' => $content, 'endPos' => $i + 1];
        }
        
        return null;
    }
    
    private function extractImage($text, $start) {
        if ($start + 1 >= strlen($text) || $text[$start + 1] !== '[') {
            return null;
        }
        
        $i = $start + 2;
        $alt = '';
        
        while ($i < strlen($text) && $text[$i] !== ']') {
            $alt .= $text[$i];
            $i++;
        }
        
        if ($i >= strlen($text) || $text[$i] !== ']') {
            return null;
        }
        
        $i++; // Skip ]
        
        if ($i >= strlen($text) || $text[$i] !== '(') {
            return null;
        }
        
        $i++; // Skip (
        $url = '';
        
        while ($i < strlen($text) && $text[$i] !== ')') {
            $url .= $text[$i];
            $i++;
        }
        
        if ($i < strlen($text) && $text[$i] === ')') {
            return ['alt' => $alt, 'url' => $url, 'endPos' => $i + 1];
        }
        
        return null;
    }
    
    private function extractLink($text, $start) {
        $i = $start + 1;
        $linkText = '';
        
        while ($i < strlen($text) && $text[$i] !== ']') {
            $linkText .= $text[$i];
            $i++;
        }
        
        if ($i >= strlen($text) || $text[$i] !== ']') {
            return null;
        }
        
        $i++; // Skip ]
        
        if ($i >= strlen($text) || $text[$i] !== '(') {
            return null;
        }
        
        $i++; // Skip (
        $url = '';
        
        while ($i < strlen($text) && $text[$i] !== ')') {
            $url .= $text[$i];
            $i++;
        }
        
        if ($i < strlen($text) && $text[$i] === ')') {
            return ['text' => $linkText, 'url' => $url, 'endPos' => $i + 1];
        }
        
        return null;
    }
    
    private function extractEmphasis($text, $start, $marker) {
        $len = strlen($text);
        
        // Check for double marker (bold)
        if ($start + 1 < $len && $text[$start + 1] === $marker) {
            // Double marker - bold
            $i = $start + 2;
            $content = '';
            
            while ($i + 1 < $len) {
                if ($text[$i] === $marker && $text[$i + 1] === $marker) {
                    return ['type' => 'bold', 'content' => $content, 'endPos' => $i + 2];
                }
                $content .= $text[$i];
                $i++;
            }
        } else {
            // Single marker - emphasis
            $i = $start + 1;
            $content = '';
            
            while ($i < $len && $text[$i] !== $marker) {
                $content .= $text[$i];
                $i++;
            }
            
            if ($i < $len && $text[$i] === $marker && !empty($content)) {
                return ['type' => 'emphasis', 'content' => $content, 'endPos' => $i + 1];
            }
        }
        
        return null;
    }
    
    private function extractStrikethrough($text, $start) {
        $i = $start + 2;
        $content = '';
        
        while ($i + 1 < strlen($text)) {
            if ($text[$i] === '~' && $text[$i + 1] === '~') {
                return ['content' => $content, 'endPos' => $i + 2];
            }
            $content .= $text[$i];
            $i++;
        }
        
        return null;
    }
    
    private function isTableRow($line) {
        if (empty($line)) return false;
        return strpos($line, '|') !== false;
    }
    
    private function isTableSeparator($line) {
        $cleaned = str_replace([' ', '|', '-', ':'], '', $line);
        return empty($cleaned);
    }
    
    private function parseTableRow($line) {
        $cells = [];
        $currentCell = '';
        $inEscape = false;
        
        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];
            
            if ($inEscape) {
                $currentCell .= $char;
                $inEscape = false;
                continue;
            }
            
            if ($char === '\\') {
                $inEscape = true;
                continue;
            }
            
            if ($char === '|') {
                $cells[] = trim($currentCell);
                $currentCell = '';
            } else {
                $currentCell .= $char;
            }
        }
        
        if (!empty($currentCell)) {
            $cells[] = trim($currentCell);
        }
        
        // Remove empty first/last cells if they exist (from leading/trailing |)
        if (!empty($cells) && empty($cells[0])) {
            array_shift($cells);
        }
        if (!empty($cells) && empty($cells[count($cells) - 1])) {
            array_pop($cells);
        }
        
        return $cells;
    }
    
    private function renderTable($headers, $rows) {
        $this->output .= "<div class=\"markdown-table-container\">\n";
        $this->output .= "<table>\n";
        
        if (!empty($headers)) {
            $this->output .= "<thead>\n<tr>\n";
            foreach ($headers as $header) {
                $this->output .= "\t<th>" . $this->processInlineElements($header) . "</th>\n";
            }
            $this->output .= "</tr>\n</thead>\n";
        }
        
        if (!empty($rows)) {
            $this->output .= "<tbody>\n";
            foreach ($rows as $row) {
                $this->output .= "<tr>\n";
                foreach ($row as $cell) {
                    $this->output .= "\t<td>" . $this->processInlineElements($cell) . "</td>\n";
                }
                $this->output .= "</tr>\n";
            }
            $this->output .= "</tbody>\n";
        }
        
        $this->output .= "</table>\n";
        $this->output .= "</div>\n";
    }
    
    private function addCodeBlock($content) {
        $content = htmlentities(trim($content), ENT_COMPAT);
        $placeholder = '{{{' . count($this->codeBlocks) . '}}}';
        $this->codeBlocks[] = "<pre><code>" . $content . "</code></pre>";
        $this->output .= "<p>" . $placeholder . "</p>\n";
    }
    
    private function reinsertCodeBlocks() {
        for ($i = 0; $i < count($this->codeBlocks); $i++) {
            $placeholder = '<p>{{{' . $i . '}}}</p>';
            $this->output = str_replace($placeholder, $this->codeBlocks[$i], $this->output);
        }
    }
    
    private function isListItem($line) {
        $trimmed = trim($line);
        if (empty($trimmed)) return false;
        
        // Unordered list
        $firstChar = $this->charAt($trimmed, 0);
        if ($firstChar === '*' || $firstChar === '-' || $firstChar === '+') {
            return $this->charAt($trimmed, 1) === ' ';
        }
        
        // Ordered list
        return $this->isOrderedListItem($trimmed);
    }
    
    private function isOrderedListItem($line) {
        $trimmed = trim($line);
        $dotPos = strpos($trimmed, '.');
        if ($dotPos === false) return false;
        
        $beforeDot = substr($trimmed, 0, $dotPos);
        if (empty($beforeDot) || !ctype_digit($beforeDot)) return false;
        
        return isset($trimmed[$dotPos + 1]) && $trimmed[$dotPos + 1] === ' ';
    }
    
    private function extractListItemText($line) {
        $trimmed = trim($line);
        
        if ($this->isOrderedListItem($trimmed)) {
            $dotPos = strpos($trimmed, '.');
            return trim(substr($trimmed, $dotPos + 1));
        } else {
            return trim(substr($trimmed, 2));
        }
    }
    
    private function isHorizontalRule($line) {
        $clean = str_replace(' ', '', $line);
        if (strlen($clean) < 3) return false;
        
        $char = $clean[0];
        if ($char !== '-' && $char !== '*' && $char !== '_') return false;
        
        for ($i = 0; $i < strlen($clean); $i++) {
            if ($clean[$i] !== $char) return false;
        }
        
        return true;
    }
    
    private function openList($type) {
        $this->output .= "<{$type}>\n";
    }
    
    private function closeList($type) {
        $this->output .= "</{$type}>\n";
    }
    
    private function startsWithString($haystack, $needle) {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
    
    private function startsWithHtmlTag($line) {
        $trimmed = trim($line);
        return !empty($trimmed) && $trimmed[0] === '<' && 
               (strpos($trimmed, '</') !== false || substr($trimmed, -1) === '>');
    }
    
    private function charAt($string, $pos) {
        return isset($string[$pos]) ? $string[$pos] : '';
    }
    
    private function countLeadingChars($string, $char) {
        $count = 0;
        for ($i = 0; $i < strlen($string) && $string[$i] === $char; $i++) {
            $count++;
        }
        return $count;
    }
}