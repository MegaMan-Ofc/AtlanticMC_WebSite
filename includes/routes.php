<?php

declare(strict_types=1);

function public_routes(): array
{
    return [
        'home' => ['path' => '', 'script' => 'index.php'],
        'ranks' => ['path' => 'ranks', 'script' => 'ranks.php'],
        'rubis' => ['path' => 'rubis', 'script' => 'rubis.php'],
        'keys' => ['path' => 'keys', 'script' => 'keys.php'],
        'boosters' => ['path' => 'boosters', 'script' => 'boosters.php'],
        'search' => ['path' => 'search', 'script' => 'search.php'],
        'cart' => ['path' => 'cart', 'script' => 'cart.php'],
        'checkout' => ['path' => 'checkout', 'script' => 'checkout.php'],
        'login' => ['path' => 'login', 'script' => 'login.php'],
        'success' => ['path' => 'success', 'script' => 'success.php'],
        'privacy' => ['path' => 'privacy', 'script' => 'privacy.php'],
        'terms' => ['path' => 'terms', 'script' => 'terms.php'],
        'purchase-policy' => ['path' => 'purchase-policy', 'script' => 'purchase-policy.php'],
        'rules' => ['path' => 'rules', 'script' => 'rules.php'],
        'faq' => ['path' => 'faq', 'script' => 'faq.php'],
        'admin' => ['path' => 'admin', 'script' => 'admin.php'],
    ];
}

function legacy_category_slugs(): array
{
    return ['ranks', 'rubis', 'keys', 'boosters'];
}

function reserved_category_slugs(): array
{
    $reserved = [
        'home',
        'index',
        'category',
        'actions',
        'ajax',
        'api',
        'assets',
        'css',
        'js',
        'uploads',
        'maintenance',
    ];

    foreach (public_routes() as $route) {
        $path = (string) ($route['path'] ?? '');

        if ($path !== '' && !in_array($path, legacy_category_slugs(), true)) {
            $reserved[] = $path;
        }
    }

    return array_values(array_unique($reserved));
}

function category_slug_is_reserved(string $slug): bool
{
    return in_array(strtolower(trim($slug)), reserved_category_slugs(), true);
}

function public_category_slug_from_path(string $path): ?string
{
    $path = trim(rawurldecode($path), '/');

    if (
        $path === ''
        || str_contains($path, '/')
        || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $path) !== 1
        || category_slug_is_reserved($path)
    ) {
        return null;
    }

    return $path;
}

function public_category_slug_from_request_uri(string $requestUri): ?string
{
    $requestPath = parse_url($requestUri, PHP_URL_PATH);

    return is_string($requestPath) ? public_category_slug_from_path($requestPath) : null;
}

function category_path(string $slug): string
{
    $slug = strtolower(trim($slug));

    if (
        preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1
        || category_slug_is_reserved($slug)
    ) {
        throw new InvalidArgumentException('Invalid public category slug.');
    }

    return $slug;
}

function category_url(string $slug): string
{
    return url(category_path($slug));
}

function public_route_name_from_request_uri(string $requestUri): ?string
{
    $requestPath = parse_url($requestUri, PHP_URL_PATH);

    if (!is_string($requestPath)) {
        return null;
    }

    $requestPath = trim(rawurldecode($requestPath), '/');

    if ($requestPath === '' || $requestPath === 'index' || $requestPath === 'index.php') {
        return 'home';
    }

    foreach (public_routes() as $name => $route) {
        if ($requestPath === $route['path']) {
            return $name;
        }
    }

    return null;
}

function route_path(string $name, array $query = []): string
{
    $route = public_routes()[$name] ?? null;

    if (!is_array($route)) {
        throw new InvalidArgumentException('Unknown public route: ' . $name);
    }

    $path = $route['path'];

    if ($query !== []) {
        $path .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    return $path;
}

function route_url(string $name, array $query = []): string
{
    return url(route_path($name, $query));
}

function redirect_route(string $name, array $query = [], int $status = 303): never
{
    header('Location: ' . route_url($name, $query), true, $status);
    exit;
}

function current_route_name(): ?string
{
    $script = basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? $_SERVER['SCRIPT_NAME'] ?? ''));

    if ($script === 'category.php') {
        return 'category';
    }

    foreach (public_routes() as $name => $route) {
        if ($script === $route['script']) {
            return $name;
        }
    }

    return null;
}

function current_canonical_url(): ?string
{
    if ((string) config('app.url', '') === '') {
        return null;
    }

    $routeName = current_route_name();

    if ($routeName === 'category') {
        $slug = public_category_slug_from_request_uri((string) ($_SERVER['REQUEST_URI'] ?? '/'));

        if ($slug === null) {
            $querySlug = $_GET['slug'] ?? null;
            $slug = is_string($querySlug) ? public_category_slug_from_path($querySlug) : null;
        }

        return $slug === null ? null : category_url($slug);
    }

    return $routeName === null ? null : route_url($routeName);
}

function request_base_path(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    foreach (['/actions/', '/api/', '/ajax/'] as $marker) {
        $position = strpos($scriptName, $marker);

        if ($position !== false) {
            return rtrim(substr($scriptName, 0, $position), '/');
        }
    }

    $directory = str_replace('\\', '/', dirname($scriptName));

    return $directory === '/' ? '' : rtrim($directory, '/');
}

function normalize_public_path(string $path): string
{
    $parts = parse_url($path);

    if ($parts === false) {
        return ltrim($path, '/');
    }

    $pathPart = ltrim((string) ($parts['path'] ?? ''), '/');

    foreach (public_routes() as $route) {
        if ($pathPart === $route['script'] || $pathPart === $route['path']) {
            $pathPart = $route['path'];
            break;
        }
    }

    if ($pathPart === 'index') {
        $pathPart = '';
    }

    $normalized = $pathPart;

    if (isset($parts['query']) && $parts['query'] !== '') {
        $normalized .= '?' . $parts['query'];
    }

    if (isset($parts['fragment']) && $parts['fragment'] !== '') {
        $normalized .= '#' . $parts['fragment'];
    }

    return $normalized;
}

function url(string $path = ''): string
{
    $path = normalize_public_path(ltrim($path, '/'));
    $configuredUrl = (string) config('app.url', '');

    if ($configuredUrl !== '') {
        return $path === '' ? $configuredUrl : $configuredUrl . '/' . $path;
    }

    $basePath = request_base_path();

    if ($path === '') {
        return $basePath === '' ? '/' : $basePath . '/';
    }

    return ($basePath === '' ? '' : $basePath) . '/' . $path;
}

function redirect(string $path, int $status = 303): never
{
    header('Location: ' . url($path), true, $status);
    exit;
}

function redirect_external(string $url, int $status = 303): never
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException('Invalid redirect URL.');
    }

    header('Location: ' . $url, true, $status);
    exit;
}

function current_request_path(): string
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $path = parse_url($uri, PHP_URL_PATH);

    return is_string($path) ? $path : '/';
}

function safe_return_path(?string $path, string $fallback = ''): string
{
    $fallback = normalize_public_path($fallback);

    if ($path === null || trim($path) === '') {
        return $fallback;
    }

    $decoded = rawurldecode(trim($path));

    if (
        strlen($decoded) > 2048
        || str_contains($decoded, '://')
        || str_starts_with($decoded, '//')
        || str_contains($decoded, "\r")
        || str_contains($decoded, "\n")
    ) {
        return $fallback;
    }

    $parts = parse_url($decoded);

    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
        return $fallback;
    }

    $requestedPath = trim((string) ($parts['path'] ?? ''), '/');
    $base = basename($requestedPath);
    $matchedPath = null;

    if ($requestedPath === '' || $base === 'index' || $base === 'index.php') {
        $matchedPath = '';
    } else {
        foreach (public_routes() as $route) {
            if ($base === $route['path'] || $base === $route['script']) {
                $matchedPath = $route['path'];
                break;
            }
        }

        if ($matchedPath === null) {
            $matchedPath = public_category_slug_from_path($requestedPath);
        }
    }

    if ($matchedPath === null) {
        return $fallback;
    }

    if (isset($parts['query']) && $parts['query'] !== '') {
        $matchedPath .= '?' . $parts['query'];
    }

    return $matchedPath;
}

function current_public_return_path(): string
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');

    return safe_return_path($uri, route_path('home'));
}
