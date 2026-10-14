<?php

namespace NovaFlow\Core\Pagination;

use JsonSerializable;
use Countable;
use IteratorAggregate;
use ArrayIterator;
use ArrayAccess;
use NovaFlow\Core\Request;

/**
 * LengthAwarePaginator
 * High-performance paginator for web interfaces and REST APIs
 */
class LengthAwarePaginator implements JsonSerializable, Countable, IteratorAggregate, ArrayAccess
{
    protected array $items;
    protected int $total;
    protected int $perPage;
    protected int $currentPage;
    protected int $lastPage;
    protected string $path;
    protected array $query = [];
    protected string $pageName = 'page';

    public function __construct(array $items, int $total, int $perPage, ?int $currentPage = null, array $options = [])
    {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = max(1, $perPage);
        $this->lastPage = max(1, (int) ceil($total / $this->perPage));
        
        $this->currentPage = $currentPage ?: $this->resolveCurrentPage();
        if ($this->currentPage > $this->lastPage && $this->lastPage > 0) {
            $this->currentPage = $this->lastPage;
        }

        $this->path = $options['path'] ?? (isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '/');
        $this->pageName = $options['pageName'] ?? 'page';

        // Retain existing GET parameters except current page
        $params = $_GET ?? [];
        unset($params[$this->pageName]);
        $this->query = $params;
    }

    /**
     * Resolve current page from request parameters
     */
    protected function resolveCurrentPage(): int
    {
        $page = $_GET[$this->pageName] ?? 1;
        $page = (int) $page;
        return $page >= 1 ? $page : 1;
    }

    /**
     * Get paginated items
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Get total items count
     */
    public function total(): int
    {
        return $this->total;
    }

    /**
     * Get items per page
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get current page number
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get total pages count
     */
    public function lastPage(): int
    {
        return $this->lastPage;
    }

    /**
     * Number of the first item in current slice
     */
    public function from(): ?int
    {
        return $this->total > 0 ? ($this->currentPage - 1) * $this->perPage + 1 : null;
    }

    public function firstItem(): ?int
    {
        return $this->from();
    }

    /**
     * Number of the last item in current slice
     */
    public function to(): ?int
    {
        return $this->total > 0 ? min($this->from() + count($this->items) - 1, $this->total) : null;
    }

    public function lastItem(): ?int
    {
        return $this->to();
    }

    /**
     * Determine if there are enough items to split into multiple pages
     */
    public function hasPages(): bool
    {
        return $this->currentPage != 1 || $this->hasMorePages();
    }

    /**
     * Determine if there are more items in the data source
     */
    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    /**
     * Generate URL for given page number
     */
    public function url(int $page): string
    {
        if ($page <= 0) {
            $page = 1;
        }

        $parameters = array_merge($this->query, [$this->pageName => $page]);
        return $this->path . '?' . http_build_query($parameters);
    }

    /**
     * URL for previous page
     */
    public function previousPageUrl(): ?string
    {
        return $this->currentPage > 1 ? $this->url($this->currentPage - 1) : null;
    }

    /**
     * URL for next page
     */
    public function nextPageUrl(): ?string
    {
        return $this->hasMorePages() ? $this->url($this->currentPage + 1) : null;
    }

    /**
     * Append query string parameters
     */
    public function appends(array $query): self
    {
        $this->query = array_merge($this->query, $query);
        return $this;
    }

    /**
     * Render Bootstrap 5 pagination HTML navigation links
     */
    public function links(): string
    {
        if (!$this->hasPages()) {
            return '';
        }

        $html = '<nav aria-label="Page navigation"><ul class="pagination pagination-sm justify-content-center mb-0">';

        // Previous Link
        if ($this->currentPage <= 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($this->previousPageUrl()) . '">&laquo;</a></li>';
        }

        // Window slider calculation
        $start = max(1, $this->currentPage - 2);
        $end = min($this->lastPage, $this->currentPage + 2);

        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($this->url(1)) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        for ($p = $start; $p <= $end; $p++) {
            if ($p === $this->currentPage) {
                $html .= '<li class="page-item active" aria-current="page"><span class="page-link">' . $p . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($this->url($p)) . '">' . $p . '</a></li>';
            }
        }

        if ($end < $this->lastPage) {
            if ($end < $this->lastPage - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($this->url($this->lastPage)) . '">' . $this->lastPage . '</a></li>';
        }

        // Next Link
        if ($this->hasMorePages()) {
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($this->nextPageUrl()) . '">&raquo;</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
        }

        $html .= '</ul></nav>';
        return $html;
    }

    /**
     * Convert paginator to structured array for API serialization
     */
    public function toArray(): array
    {
        return [
            'data'         => $this->items,
            'current_page' => $this->currentPage,
            'from'         => $this->from(),
            'to'           => $this->to(),
            'last_page'    => $this->lastPage,
            'per_page'     => $this->perPage,
            'total'        => $this->total,
            'first_page_url' => $this->url(1),
            'last_page_url'  => $this->url($this->lastPage),
            'next_page_url'  => $this->nextPageUrl(),
            'prev_page_url'  => $this->previousPageUrl(),
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool { return isset($this->items[$offset]); }
    public function offsetGet(mixed $offset): mixed { return $this->items[$offset] ?? null; }
    public function offsetSet(mixed $offset, mixed $value): void { $this->items[$offset] = $value; }
    public function offsetUnset(mixed $offset): void { unset($this->items[$offset]); }
}
