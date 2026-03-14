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

        $currentSortBy  = $request->get('sort_by', $defaultColumn);
        $currentSortDir = strtolower($request->get('sort_dir', $defaultDirection)) === 'asc' ? 'asc' : 'desc';
        $newDirection   = ($currentSortBy === $column && $currentSortDir === 'asc') ? 'desc' : 'asc';

        $queryParams = array_merge(
            $request->except(['sort_by', 'sort_dir', 'page']),
            $options['query'] ?? []
        );
        $queryParams['sort_by']  = $column;
        $queryParams['sort_dir'] = $newDirection;

        $url = $routeName ? route($routeName, $queryParams) : $request->url() . '?' . http_build_query($queryParams);

        $icon = '';
        if ($currentSortBy === $column) {
            $icon = $currentSortDir === 'asc'
                ? '<i class="bi bi-sort-up ms-1"></i>'
                : '<i class="bi bi-sort-down ms-1"></i>';
        }

        return '<a href="' . e($url) . '" class="text-decoration-none text-dark">' . e($title) . $icon . '</a>';
    }
}
