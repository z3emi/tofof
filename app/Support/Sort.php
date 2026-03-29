<?php

namespace App\Support;

use Illuminate\Http\Request;

class Sort
{
    public static function resolve(Request $request, array $allowed, string $defaultColumn, string $defaultDirection = 'desc'): array
    {
        $column = $request->get('sort_by', $defaultColumn);
        if (!in_array($column, $allowed, true)) {
            $column = $defaultColumn;
        }

        $direction = strtolower($request->get('sort_dir', $defaultDirection)) === 'asc' ? 'asc' : 'desc';

        return [$column, $direction];
    }

    public static function link(string $column, string $title, array $options = []): string
    {
        $request = request();
        $defaultColumn    = $options['default_column'] ?? 'id';
        $defaultDirection = strtolower($options['default_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $allowed          = $options['allowed'] ?? [];
        $routeName        = $options['route'] ?? optional($request->route())->getName();

        if ($allowed && !in_array($column, $allowed, true)) {
            return e($title);
        }

        $currentSortBy  = $request->get('sort_by');
        $currentSortDir = strtolower($request->get('sort_dir', '')) === 'asc' ? 'asc' : ($request->has('sort_dir') ? 'desc' : null);

        // If no sort in request, assume default if this is the default column
        $isActive = ($currentSortBy === $column) || (!$currentSortBy && $column === $defaultColumn);
        $activeDir = $isActive ? ($currentSortDir ?? $defaultDirection) : null;

        $newDirection = ($isActive && $activeDir === 'asc') ? 'desc' : 'asc';

        $queryParams = array_merge(
            $request->except(['sort_by', 'sort_dir', 'page']),
            $options['query'] ?? []
        );
        $queryParams['sort_by']  = $column;
        $queryParams['sort_dir'] = $newDirection;

        $url = $routeName ? route($routeName, $queryParams) : $request->url() . '?' . http_build_query($queryParams);

        $icon = '<i class="bi bi-arrow-down-up ms-1 small opacity-25"></i>';
        if ($isActive) {
            $icon = $activeDir === 'asc'
                ? '<i class="bi bi-sort-up ms-1 text-primary"></i>'
                : '<i class="bi bi-sort-down ms-1 text-primary"></i>';
        }

        $class = $isActive ? 'text-dark fw-bold' : 'text-muted';
        
        return '<a href="' . e($url) . '" class="text-decoration-none ' . $class . ' d-inline-flex align-items-center">' . e($title) . $icon . '</a>';
    }
}
