<?php

namespace NovaFlow\Core;

/**
 * Pagination Helper
 */
class Pagination
{
    protected int $total;
    protected int $perPage;
    protected int $currentPage;
    protected string $baseUrl;
    protected int $limit = 5;

    public function __construct(int $total, int $perPage = 20, int $currentPage = 1, string $baseUrl = '')
    {
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->baseUrl = $baseUrl;
    }

    public static function make(int $total, int $perPage = 20, int $currentPage = 1, string $baseUrl = ''): self
    {
        return new self($total, $perPage, $currentPage, $baseUrl);
    }

    public function total(): int
    {
        return $this->total;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function totalPages(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    public function hasMore(): bool
    {
        return $this->currentPage < $this->totalPages();
    }

    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    public function offset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function limit(): int
    {
        return $this->perPage;
    }

    public function render(string $template = 'default'): string
    {
        if ($this->totalPages() <= 1) {
            return '';
        }

        $html = '<nav><ul class="pagination justify-content-center">';

        // Previous button
        $prevDisabled = !$this->hasPrevious() ? 'disabled' : '';
        $prevUrl = $this->hasPrevious() ? $this->getUrl($this->currentPage - 1) : '#';
        $html .= '<li class="page-item ' . $prevDisabled . '">';
        $html .= '<a class="page-link" href="' . $prevUrl . '">&laquo; আগে</a>';
        $html .= '</li>';

        // Page numbers
        $start = max(1, $this->currentPage - $this->limit);
        $end = min($this->totalPages(), $this->currentPage + $this->limit);

        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->getUrl(1) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $this->currentPage ? 'active' : '';
            $html .= '<li class="page-item ' . $active . '">';
            $html .= '<a class="page-link" href="' . $this->getUrl($i) . '">' . $i . '</a>';
            $html .= '</li>';
        }

        if ($end < $this->totalPages()) {
            if ($end < $this->totalPages() - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->getUrl($this->totalPages()) . '">' . $this->totalPages() . '</a></li>';
        }

        // Next button
        $nextDisabled = !$this->hasMore() ? 'disabled' : '';
        $nextUrl = $this->hasMore() ? $this->getUrl($this->currentPage + 1) : '#';
        $html .= '<li class="page-item ' . $nextDisabled . '">';
        $html .= '<a class="page-link" href="' . $nextUrl . '">পরে &raquo;</a>';
        $html .= '</li>';

        $html .= '</ul></nav>';

        return $html;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages(),
            'has_more' => $this->hasMore(),
            'has_previous' => $this->hasPrevious()
        ];
    }

    protected function getUrl(int $page): string
    {
        $separator = str_contains($this->baseUrl, '?') ? '&' : '?';
        return $this->baseUrl . $separator . 'page=' . $page;
    }
}