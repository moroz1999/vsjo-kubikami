<?php

declare(strict_types=1);

$toolDir = __DIR__;
$projectRoot = dirname($toolDir, 2);
$dataPath = $toolDir . DIRECTORY_SEPARATOR . 'routes.json';
$outputDir = $toolDir . DIRECTORY_SEPARATOR . 'generated';

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--project') {
        $outputDir = $projectRoot;
        continue;
    }

    if (str_starts_with($arg, '--out=')) {
        $outputDir = make_absolute_path(substr($arg, 6), $toolDir);
        continue;
    }

    fail("Unknown argument: {$arg}");
}

$data = read_route_data($dataPath, $toolDir);
$pointsById = index_points($data['points'] ?? []);
$orderedIds = order_ids($data['tableOrder'] ?? [], $pointsById);
$includeFiles = collect_include_files($data['includeFiles'] ?? [], $pointsById);
$routeLogicTail = read_route_logic_tail($projectRoot . DIRECTORY_SEPARATOR . 'enemies.route.a80');

if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true)) {
    fail("Cannot create output directory: {$outputDir}");
}

write_route_table($outputDir . DIRECTORY_SEPARATOR . 'enemies.route.a80', $orderedIds, $includeFiles, $routeLogicTail);
write_route_files($outputDir, $includeFiles, $pointsById, $orderedIds);

printf(
    "Generated %s and %d route include files in %s\n",
    'enemies.route.a80',
    count($includeFiles),
    $outputDir
);

function read_route_data(string $path, string $baseDir): array
{
    $data = read_json($path);

    if (isset($data['points']) && is_array($data['points'])) {
        return $data;
    }

    if (!isset($data['routeFiles']) || !is_array($data['routeFiles'])) {
        fail("Route data has neither points nor routeFiles: {$path}");
    }

    $data['points'] = read_route_file_points($data['routeFiles'], $baseDir);

    return $data;
}

function read_route_file_points(array $routeFiles, string $baseDir): array
{
    $points = [];

    foreach ($routeFiles as $routeFile) {
        $file = is_array($routeFile) ? ($routeFile['file'] ?? null) : $routeFile;
        if (!is_string($file) || $file === '') {
            fail('routeFiles contains an entry without file');
        }

        $roomData = read_json(make_absolute_path($file, $baseDir));
        if (!isset($roomData['points']) || !is_array($roomData['points'])) {
            fail("Route file misses points: {$file}");
        }

        foreach ($roomData['points'] as $point) {
            $points[] = $point;
        }
    }

    return $points;
}

function read_json(string $path): array
{
    $source = file_get_contents($path);
    if ($source === false) {
        fail("Cannot read {$path}. Run import_from_asm.php first.");
    }

    $data = json_decode($source, true);
    if (!is_array($data)) {
        fail("Cannot decode {$path}: " . json_last_error_msg());
    }

    return $data;
}

function read_route_logic_tail(string $path): string
{
    $source = file_get_contents($path);
    if ($source === false) {
        fail("Cannot read {$path}");
    }

    preg_match_all(
        '/^[^\S\r\n]*include\s+"enemies\.route_[^"]+\.a80".*\R?/m',
        $source,
        $matches,
        PREG_OFFSET_CAPTURE
    );

    if ($matches[0] === []) {
        return '';
    }

    $last = end($matches[0]);
    $tail = substr($source, $last[1] + strlen($last[0]));

    return ltrim($tail, "\r\n");
}

function index_points(array $points): array
{
    $indexed = [];

    foreach ($points as $point) {
        if (!isset($point['id'])) {
            fail('Point without id in routes.json');
        }

        $indexed[$point['id']] = $point;
    }

    return $indexed;
}

function order_ids(array $tableOrder, array $pointsById): array
{
    $ordered = [];
    $seen = [];

    foreach ($tableOrder as $id) {
        if (!isset($pointsById[$id])) {
            fail("tableOrder references missing point: {$id}");
        }

        $ordered[] = $id;
        $seen[$id] = true;
    }

    foreach ($pointsById as $id => $_point) {
        if (!isset($seen[$id])) {
            $ordered[] = $id;
        }
    }

    return $ordered;
}

function collect_include_files(array $includeFiles, array $pointsById): array
{
    $seen = array_fill_keys($includeFiles, true);
    $newFiles = [];

    foreach ($pointsById as $point) {
        $includeFile = route_file_from_room($point);
        if (!isset($seen[$includeFile])) {
            $newFiles[] = $includeFile;
            $seen[$includeFile] = true;
        }
    }

    usort($newFiles, static function (string $left, string $right): int {
        return route_file_sort_key($left) <=> route_file_sort_key($right);
    });

    return array_merge($includeFiles, $newFiles);
}

function route_file_sort_key(string $file): int
{
    if (preg_match('/enemies\.route_(\d+)_(\d+)\.a80/', $file, $matches)) {
        return ((int) $matches[2]) * 100 + (int) $matches[1];
    }

    return PHP_INT_MAX;
}

function write_route_table(string $path, array $orderedIds, array $includeFiles, string $logicTail): void
{
    $indent = str_repeat(' ', 28);
    $lines = [];
    $lines[] = 'route_point_count           equ	(route_points_table_end-route_points_table)/2';
    $lines[] = '';
    $lines[] = 'route_points_table';

    foreach ($orderedIds as $id) {
        $lines[] = $indent . 'dw	' . $id;
    }

    $lines[] = 'route_points_table_end';
    $lines[] = '';

    foreach ($includeFiles as $includeFile) {
        $lines[] = $indent . 'include	"' . $includeFile . '"';
    }

    if ($logicTail !== '') {
        $lines[] = '';
        $lines[] = rtrim($logicTail, "\r\n");
    }

    write_text($path, implode(PHP_EOL, $lines) . PHP_EOL);
}

function write_route_files(string $outputDir, array $includeFiles, array $pointsById, array $orderedIds): void
{
    $pointsByFile = [];

    foreach ($orderedIds as $id) {
        $point = $pointsById[$id];
        $file = route_file_from_room($point);
        $pointsByFile[$file][] = $point;
    }

    foreach ($includeFiles as $includeFile) {
        $path = $outputDir . DIRECTORY_SEPARATOR . $includeFile;
        $points = $pointsByFile[$includeFile] ?? [];

        if ($points === []) {
            write_text($path, empty_route_file_text($includeFile));
            continue;
        }

        $blocks = array_map('route_point_block', $points);
        write_text($path, implode(PHP_EOL . PHP_EOL, $blocks) . PHP_EOL);
    }
}

function route_file_from_room(array $point): string
{
    return sprintf('enemies.route_%d_%d.a80', $point['roomX'], $point['roomY']);
}

function route_point_block(array $point): string
{
    $indent = str_repeat(' ', 28);
    $fields = [
        [string_value($point, 'roomX'), 'room_x', true],
        [string_value($point, 'roomY'), 'room_y', true],
        [string_value($point, 'x'), 'x', true],
        [string_value($point, 'y'), 'y', true],
        ['route_point_' . string_value($point, 'type'), 'type', true],
        [link_value($point, 'topLeft'), 'top-left point', true],
        [link_value($point, 'bottomRight'), 'bottom-right point', true],
        [link_value($point, 'alternative'), 'alternative point', false],
    ];

    $lines = [$point['id'], $indent . "route_point\t{"];
    foreach ($fields as [$value, $comment, $comma]) {
        $fieldText = $comma ? $value . ',' : $value;
        $lines[] = $indent . str_pad($fieldText, 28) . ' ; ' . $comment;
    }
    $lines[] = $indent . '}';

    return implode(PHP_EOL, $lines);
}

function string_value(array $point, string $key): string
{
    if (!array_key_exists($key, $point)) {
        fail("Point {$point['id']} misses {$key}");
    }

    return (string) $point[$key];
}

function link_value(array $point, string $key): string
{
    if (!array_key_exists($key, $point) || $point[$key] === null || $point[$key] === '') {
        return '0';
    }

    return (string) $point[$key];
}

function empty_route_file_text(string $includeFile): string
{
    if (preg_match('/enemies\.route_(\d+)_(\d+)\.a80/', $includeFile, $matches)) {
        return sprintf('; Room %d,%d has no enemy route points.%s', $matches[1], $matches[2], PHP_EOL);
    }

    return '; No enemy route points.' . PHP_EOL;
}

function write_text(string $path, string $text): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
        fail("Cannot create directory: {$dir}");
    }

    if (file_put_contents($path, $text) === false) {
        fail("Cannot write {$path}");
    }
}

function make_absolute_path(string $path, string $base): string
{
    if (preg_match('/^[a-zA-Z]:[\/\\\\]/', $path) || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
        return $path;
    }

    return $base . DIRECTORY_SEPARATOR . $path;
}

function fail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}
