<?php

declare(strict_types=1);

const ROOM_BYTES_PER_CELL = 1;

$toolDir = __DIR__;
$projectRoot = dirname($toolDir, 2);

$roomConfig = read_room_config($projectRoot . DIRECTORY_SEPARATOR . 'rooms.a80');
$rooms = read_rooms($projectRoot, $roomConfig);
$routeTable = read_route_table($projectRoot . DIRECTORY_SEPARATOR . 'enemies.route.a80');
$includeFiles = read_route_includes($projectRoot . DIRECTORY_SEPARATOR . 'enemies.route.a80');
$points = read_route_points($projectRoot, $includeFiles);
$rewires = read_route_rewires($projectRoot);

$data = [
    'version' => 1,
    'generatedAt' => gmdate('c'),
    'source' => [
        'rooms' => 'rooms.a80',
        'roomsDir' => 'rooms_unpacked',
        'routeTable' => 'enemies.route.a80',
        'logicDir' => 'logic',
    ],
    'room' => $roomConfig,
    'rooms' => $rooms,
    'tableOrder' => $routeTable,
    'includeFiles' => $includeFiles,
    'points' => order_points($points, $routeTable),
    'rewires' => $rewires,
];

write_json($toolDir . DIRECTORY_SEPARATOR . 'routes.json', $data);
write_viewer_data($toolDir . DIRECTORY_SEPARATOR . 'routes-data.js', $data);

printf(
    "Imported %d rooms, %d route points, %d logic rewires\n",
    count($rooms),
    count($data['points']),
    count($rewires)
);

function read_room_config(string $path): array
{
    $source = read_text_file($path);

    return [
        'amountX' => read_equ($source, 'room_amount_x'),
        'amountY' => read_equ($source, 'room_amount_y'),
        'width' => read_equ($source, 'room_w'),
        'height' => read_equ($source, 'room_h'),
    ];
}

function read_equ(string $source, string $name): int
{
    if (!preg_match('/^' . preg_quote($name, '/') . '\s+equ\s+(\d+)/m', $source, $matches)) {
        fail("Cannot find {$name} in rooms.a80");
    }

    return (int) $matches[1];
}

function read_rooms(string $projectRoot, array $roomConfig): array
{
    $rooms = [];
    $roomSize = $roomConfig['width'] * $roomConfig['height'] * ROOM_BYTES_PER_CELL;

    for ($y = 0; $y < $roomConfig['amountY']; $y++) {
        for ($x = 0; $x < $roomConfig['amountX']; $x++) {
            $relativePath = "rooms_unpacked/room{$x},{$y}.atr";
            $path = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $size = is_file($path) ? filesize($path) : 0;

            $rooms[] = [
                'id' => "{$x},{$y}",
                'x' => $x,
                'y' => $y,
                'file' => $relativePath,
                'exists' => is_file($path),
                'bytes' => $size,
                'hasFullRoomData' => $size >= $roomSize,
            ];
        }
    }

    return $rooms;
}

function read_route_table(string $path): array
{
    $source = read_text_file($path);

    if (!preg_match('/route_points_table\s*(.*?)route_points_table_end/s', $source, $matches)) {
        fail('Cannot find route_points_table in enemies.route.a80');
    }

    preg_match_all('/\bdw\s+([a-zA-Z_][a-zA-Z0-9_]*)/', $matches[1], $entries);

    return $entries[1];
}

function read_route_includes(string $path): array
{
    $source = read_text_file($path);
    preg_match_all('/include\s+"(enemies\.route_[^"]+\.a80)"/', $source, $matches);

    return array_values(array_unique($matches[1]));
}

function read_route_points(string $projectRoot, array $includeFiles): array
{
    $points = [];

    foreach ($includeFiles as $includeFile) {
        $path = $projectRoot . DIRECTORY_SEPARATOR . $includeFile;
        if (!is_file($path)) {
            fail("Missing route include: {$includeFile}");
        }

        $source = read_text_file($path);
        preg_match_all(
            '/^(route_[a-zA-Z0-9_]+)\s*\R\s*route_point\s*\{\s*(.*?)^\s*\}/ms',
            $source,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );

        foreach ($matches as $match) {
            $id = $match[1][0];
            $values = parse_route_point_values($match[2][0], $includeFile, $id);
            $line = line_number_at($source, $match[0][1]);

            $points[$id] = [
                'id' => $id,
                'roomX' => parse_int_value($values[0], $includeFile, $id),
                'roomY' => parse_int_value($values[1], $includeFile, $id),
                'x' => parse_int_value($values[2], $includeFile, $id),
                'y' => parse_int_value($values[3], $includeFile, $id),
                'type' => normalize_route_point_type($values[4]),
                'topLeft' => normalize_link($values[5]),
                'bottomRight' => normalize_link($values[6]),
                'alternative' => normalize_link($values[7]),
                'sourceFile' => $includeFile,
                'sourceLine' => $line,
            ];
        }
    }

    return $points;
}

function parse_route_point_values(string $block, string $file, string $id): array
{
    $values = [];

    foreach (preg_split('/\R/', $block) as $line) {
        $line = preg_replace('/;.*$/', '', $line);
        $line = trim((string) $line);
        if ($line === '') {
            continue;
        }

        $line = rtrim($line, ',');
        if ($line !== '') {
            $values[] = trim($line);
        }
    }

    if (count($values) !== 8) {
        fail("Expected 8 route point fields in {$file}:{$id}, got " . count($values));
    }

    return $values;
}

function parse_int_value(string $value, string $file, string $id): int
{
    if (!preg_match('/^\d+$/', $value)) {
        fail("Expected integer in {$file}:{$id}, got {$value}");
    }

    return (int) $value;
}

function normalize_route_point_type(string $value): string
{
    return preg_replace('/^route_point_/', '', $value);
}

function normalize_link(string $value): ?string
{
    if ($value === '0') {
        return null;
    }

    return $value;
}

function order_points(array $points, array $routeTable): array
{
    $ordered = [];

    foreach ($routeTable as $id) {
        if (!isset($points[$id])) {
            fail("Route table references missing point: {$id}");
        }

        $ordered[] = $points[$id];
        unset($points[$id]);
    }

    foreach ($points as $point) {
        $ordered[] = $point;
    }

    return $ordered;
}

function read_route_rewires(string $projectRoot): array
{
    $rewires = [];
    $logicDir = $projectRoot . DIRECTORY_SEPARATOR . 'logic';

    foreach (glob($logicDir . DIRECTORY_SEPARATOR . '*.a80') ?: [] as $path) {
        $source = read_text_file($path);
        $lines = preg_split('/\R/', $source);
        $routine = null;

        foreach ($lines as $index => $line) {
            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*(?:;.*)?$/', $line, $labelMatch)) {
                $routine = $labelMatch[1];
            }

            if (!preg_match(
                '/\bld\s+hl,enemies\.(route_[a-zA-Z0-9_]+)\+enemies\.route_point\.(top_left_point_ptr|bottom_right_point_ptr|alternative_point_ptr)/',
                $line,
                $fromMatch
            )) {
                continue;
            }

            $to = null;
            for ($offset = 1; $offset <= 6 && isset($lines[$index + $offset]); $offset++) {
                if (preg_match('/\bld\s+de,enemies\.(route_[a-zA-Z0-9_]+)/', $lines[$index + $offset], $toMatch)) {
                    $to = $toMatch[1];
                    break;
                }
            }

            if ($to === null) {
                continue;
            }

            $rewires[] = [
                'from' => $fromMatch[1],
                'field' => normalize_field_name($fromMatch[2]),
                'to' => $to,
                'sourceFile' => relative_path($projectRoot, $path),
                'sourceLine' => $index + 1,
                'routine' => $routine,
            ];
        }
    }

    return $rewires;
}

function normalize_field_name(string $field): string
{
    return match ($field) {
        'top_left_point_ptr' => 'topLeft',
        'bottom_right_point_ptr' => 'bottomRight',
        'alternative_point_ptr' => 'alternative',
        default => $field,
    };
}

function line_number_at(string $source, int $offset): int
{
    return substr_count(substr($source, 0, $offset), "\n") + 1;
}

function write_json(string $path, array $data): void
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        fail('Cannot encode JSON: ' . json_last_error_msg());
    }

    if (file_put_contents($path, $json . "\n") === false) {
        fail("Cannot write {$path}");
    }
}

function write_viewer_data(string $path, array $data): void
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        fail('Cannot encode viewer data: ' . json_last_error_msg());
    }

    $script = "window.ROUTE_MAP_DATA = {$json};\n";
    if (file_put_contents($path, $script) === false) {
        fail("Cannot write {$path}");
    }
}

function read_text_file(string $path): string
{
    $source = file_get_contents($path);
    if ($source === false) {
        fail("Cannot read {$path}");
    }

    return $source;
}

function relative_path(string $root, string $path): string
{
    $root = rtrim(str_replace('\\', '/', realpath($root) ?: $root), '/') . '/';
    $path = str_replace('\\', '/', realpath($path) ?: $path);

    if (str_starts_with($path, $root)) {
        return substr($path, strlen($root));
    }

    return $path;
}

function fail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}
