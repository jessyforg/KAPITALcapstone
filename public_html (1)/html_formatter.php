<?php
/**
 * HTML Formatter for AI Responses
 * 
 * This file contains functions to properly format HTML responses from the AI.
 */

/**
 * Formats a markdown-style text into proper HTML
 * 
 * @param string $text The text to format
 * @return string The formatted HTML
 */
function formatAIResponse($text) {
    // Split the text into lines for processing
    $lines = explode("\n", $text);
    $html = '';
    $inList = false;
    $listItems = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines
        if (empty($line)) {
            // If we were in a list, close it
            if ($inList) {
                $html .= formatListItems($listItems);
                $listItems = [];
                $inList = false;
            }
            continue;
        }
        
        // Check for headings
        if (preg_match('/^#\s+(.+)$/', $line, $matches)) {
            if ($inList) {
                $html .= formatListItems($listItems);
                $listItems = [];
                $inList = false;
            }
            $html .= '<h1>' . $matches[1] . '</h1>';
        } 
        elseif (preg_match('/^##\s+(.+)$/', $line, $matches)) {
            if ($inList) {
                $html .= formatListItems($listItems);
                $listItems = [];
                $inList = false;
            }
            $html .= '<h2>' . $matches[1] . '</h2>';
        } 
        elseif (preg_match('/^###\s+(.+)$/', $line, $matches)) {
            if ($inList) {
                $html .= formatListItems($listItems);
                $listItems = [];
                $inList = false;
            }
            $html .= '<h3>' . $matches[1] . '</h3>';
        } 
        elseif (preg_match('/^####\s+(.+)$/', $line, $matches)) {
            if ($inList) {
                $html .= formatListItems($listItems);
                $listItems = [];
                $inList = false;
            }
            $html .= '<h4>' . $matches[1] . '</h4>';
        } 
        // Check for list items
        elseif (preg_match('/^[\*\-]\s+(.+)$/', $line, $matches)) {
            $inList = true;
            $listItems[] = $matches[1];
        } 
        // Regular paragraph
        else {
            if ($inList) {
                $html .= formatListItems($listItems);
                $listItems = [];
                $inList = false;
            }
            $html .= '<p>' . $line . '</p>';
        }
    }
    
    // Close any open list
    if ($inList) {
        $html .= formatListItems($listItems);
    }
    
    // Convert bold and italic
    $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);
    
    return $html;
}

/**
 * Formats a list of items into HTML
 * 
 * @param array $items The list items
 * @return string The formatted HTML list
 */
function formatListItems($items) {
    if (empty($items)) {
        return '';
    }
    
    $html = '<ul>';
    foreach ($items as $item) {
        $html .= '<li>' . $item . '</li>';
    }
    $html .= '</ul>';
    
    return $html;
} 