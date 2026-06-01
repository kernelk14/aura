<?php

namespace AuraCore;

class Pagination
{
    protected $items;
    protected $total;
    protected $perPage;
    protected $currentPage;
    protected $lastPage;

    public function __construct($items, $total, $perPage, $currentPage)
    {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->lastPage = (int) ceil($total / $perPage);
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

    public function previousPageUrl()
    {
        if ($this->onFirstPage()) {
            return null;
        }
        return $this->buildUrl($this->currentPage - 1);
    }

    public function nextPageUrl()
    {
        if ($this->hasMorePages()) {
            return $this->buildUrl($this->currentPage + 1);
        }
        return null;
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
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->previousPageUrl() . '">&laquo; Previous</a></li>';
        }

        for ($i = 1; $i <= $this->lastPage; $i++) {
            $active = $i === $this->currentPage ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $this->url($i) . '">' . $i . '</a></li>';
        }

        if ($this->hasMorePages()) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->nextPageUrl() . '">Next &raquo;</a></li>';
        }

        $html .= '</ul></nav>';
        return $html;
    }

    public function toArray()
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'has_pages' => $this->hasPages(),
            'has_more' => $this->hasMorePages(),
        ];
    }

    protected function buildUrl($page)
    {
        $params = $_GET;
        $params['page'] = $page;
        return '?' . http_build_query($params);
    }
}
