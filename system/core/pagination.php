<?php

namespace AuraCore;

class Pagination
{
    protected $items;
    protected $total;
    protected $perPage;
    protected $currentPage;
    protected $lastPage;
    protected $baseUrl;

    public function __construct($items, $total, $perPage, $currentPage, $baseUrl = null)
    {
        $this->items = $items;
        $this->total = (int) $total;
        $this->perPage = max(1, (int) $perPage);
        $this->currentPage = max(1, (int) $currentPage);
        $this->lastPage = $this->perPage > 0
            ? (int) max(1, ceil($this->total / $this->perPage))
            : 1;
        if ($this->currentPage > $this->lastPage) {
            $this->currentPage = $this->lastPage;
        }
        $this->baseUrl = $baseUrl;
    }

    public function items()
    {
        return $this->items;
    }

    public function total()
    {
        return $this->total;
    }

    public function perPage()
    {
        return $this->perPage;
    }

    public function currentPage()
    {
        return $this->currentPage;
    }

    public function lastPage()
    {
        return $this->lastPage;
    }

    public function hasPages()
    {
        return $this->lastPage > 1;
    }

    public function hasMorePages()
    {
        return $this->currentPage < $this->lastPage;
    }

    public function onFirstPage()
    {
        return $this->currentPage <= 1;
    }

    public function previousPage()
    {
        return $this->currentPage > 1 ? $this->currentPage - 1 : null;
    }

    public function nextPage()
    {
        return $this->hasMorePages() ? $this->currentPage + 1 : null;
    }

    public function previousPageUrl()
    {
        $page = $this->previousPage();
        return $page === null ? null : $this->url($page);
    }

    public function nextPageUrl()
    {
        $page = $this->nextPage();
        return $page === null ? null : $this->url($page);
    }

    public function url($page)
    {
        if ($page < 1 || $page > $this->lastPage) {
            return null;
        }
        return $this->buildUrl($page);
    }

    public function links($view = null)
    {
        if (!$this->hasPages()) {
            return '';
        }

        $html = '<nav><ul class="pagination">';

        if (!$this->onFirstPage()) {
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars((string) $this->previousPageUrl()) . '">&laquo; Previous</a></li>';
        }

        $window = 2;
        $start = max(1, $this->currentPage - $window);
        $end = min($this->lastPage, $this->currentPage + $window);

        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars((string) $this->url(1)) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $this->currentPage ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . htmlspecialchars((string) $this->url($i)) . '">' . $i . '</a></li>';
        }

        if ($end < $this->lastPage) {
            if ($end < $this->lastPage - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars((string) $this->url($this->lastPage)) . '">' . $this->lastPage . '</a></li>';
        }

        if ($this->hasMorePages()) {
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars((string) $this->nextPageUrl()) . '">Next &raquo;</a></li>';
        }

        $html .= '</ul></nav>';
        return $html;
    }

    public function toArray()
    {
        return [
            'data' => $this->items,
            'items' => $this->items,
            'total' => $this->total,
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'has_pages' => $this->hasPages(),
            'has_more' => $this->hasMorePages(),
            'prev_page_url' => $this->previousPageUrl(),
            'next_page_url' => $this->nextPageUrl(),
        ];
    }

    protected function buildUrl($page)
    {
        if ($this->baseUrl !== null) {
            $separator = strpos($this->baseUrl, '?') === false ? '?' : '&';
            return $this->baseUrl . $separator . 'page=' . $page;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $parts = parse_url($uri);
        $query = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        $query['page'] = $page;
        $path = $parts['path'] ?? '/';
        return $path . '?' . http_build_query($query);
    }
}
