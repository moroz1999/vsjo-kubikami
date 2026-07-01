<?php

const ROOM_WIDTH = 32;
const ROOM_HEIGHT = 22;
const ROOM_SIZE = ROOM_WIDTH * ROOM_HEIGHT;
const ROOM_AMOUNT_X = 7;
const ROOM_AMOUNT_Y = 4;

$sourceDir = __DIR__ . DIRECTORY_SEPARATOR . 'rooms_unpacked';
$targetDir = __DIR__ . DIRECTORY_SEPARATOR . 'rooms';
$zx0Path = __DIR__ . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'zx0.exe';

if (!is_dir($sourceDir)) {
    fwrite(STDERR, "Missing source directory: {$sourceDir}\n");
    exit(1);
}

if (!is_file($zx0Path)) {
    fwrite(STDERR, "Missing ZX0 compressor: {$zx0Path}\n");
    exit(1);
}

if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true)) {
    fwrite(STDERR, "Cannot create target directory: {$targetDir}\n");
    exit(1);
}

$totalSource = 0;
$totalPacked = 0;

for ($y = 0; $y < ROOM_AMOUNT_Y; $y++) {
    for ($x = 0; $x < ROOM_AMOUNT_X; $x++) {
        $sourceFileName = "room{$x},{$y}.atr";
        $targetFileName = "room{$x},{$y}.zx0";
        $sourcePath = $sourceDir . DIRECTORY_SEPARATOR . $sourceFileName;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $targetFileName;

        if (!is_file($sourcePath)) {
            fwrite(STDERR, "Missing room file: {$sourcePath}\n");
            exit(1);
        }

        $source = file_get_contents($sourcePath);
        if ($source === false) {
            fwrite(STDERR, "Cannot read room file: {$sourcePath}\n");
            exit(1);
        }

        if (strlen($source) < ROOM_SIZE) {
            fwrite(STDERR, "Room file is shorter than 22 rows: {$sourcePath}\n");
            exit(1);
        }

        $room = substr($source, 0, ROOM_SIZE);
        $packed = pack_room($room, $zx0Path, $targetDir, $targetPath);

        if (unpack_zx0($packed, ROOM_SIZE) !== $room) {
            fwrite(STDERR, "Packed room verification failed: {$sourcePath}\n");
            exit(1);
        }

        $totalSource += ROOM_SIZE;
        $totalPacked += strlen($packed);

        printf("%s -> %s: %d -> %d bytes\n", $sourceFileName, $targetFileName, ROOM_SIZE, strlen($packed));
    }
}

printf("Total: %d -> %d bytes\n", $totalSource, $totalPacked);

function pack_room(string $room, string $zx0Path, string $targetDir, string $targetPath): string
{
    $tempPath = tempnam($targetDir, 'room_zx0_');
    if ($tempPath === false) {
        fwrite(STDERR, "Cannot create temporary room file in: {$targetDir}\n");
        exit(1);
    }

    try {
        if (file_put_contents($tempPath, $room) !== strlen($room)) {
            fwrite(STDERR, "Cannot write temporary room file: {$tempPath}\n");
            exit(1);
        }

        run_zx0($zx0Path, $tempPath, $targetPath);

        $packed = file_get_contents($targetPath);
        if ($packed === false) {
            fwrite(STDERR, "Cannot read packed room: {$targetPath}\n");
            exit(1);
        }

        return $packed;
    } finally {
        if (is_file($tempPath) && !unlink($tempPath)) {
            fwrite(STDERR, "Cannot remove temporary room file: {$tempPath}\n");
            exit(1);
        }
    }
}

function run_zx0(string $zx0Path, string $sourcePath, string $targetPath): void
{
    $command = escapeshellarg($zx0Path)
        . ' -f '
        . escapeshellarg($sourcePath)
        . ' '
        . escapeshellarg($targetPath)
        . ' 2>&1';

    $output = [];
    $exitCode = 0;
    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, "ZX0 compression failed: {$sourcePath}\n");
        fwrite(STDERR, implode("\n", $output) . "\n");
        exit(1);
    }
}

function unpack_zx0(string $packed, int $expectedLength): string
{
    $offset = 0;
    $bitMask = 0;
    $bitValue = 0;
    $backtrack = false;
    $lastByte = 0;
    $lastOffset = 1;
    $output = '';

    while (true) {
        $length = zx0_read_interlaced_elias_gamma($packed, $offset, $bitMask, $bitValue, $backtrack, $lastByte, false);
        zx0_copy_literals($packed, $offset, $lastByte, $output, $length, $expectedLength);

        if (!zx0_read_bit($packed, $offset, $bitMask, $bitValue, $backtrack, $lastByte)) {
            $length = zx0_read_interlaced_elias_gamma($packed, $offset, $bitMask, $bitValue, $backtrack, $lastByte, false);
            zx0_copy_from_last_offset($output, $lastOffset, $length, $expectedLength);

            if (!zx0_read_bit($packed, $offset, $bitMask, $bitValue, $backtrack, $lastByte)) {
                continue;
            }
        }

        while (true) {
            $lastOffset = zx0_read_interlaced_elias_gamma($packed, $offset, $bitMask, $bitValue, $backtrack, $lastByte, true);
            if ($lastOffset === 256) {
                if ($offset !== strlen($packed)) {
                    fwrite(STDERR, "ZX0 verification failed: packed stream has trailing bytes\n");
                    exit(1);
                }

                if (strlen($output) !== $expectedLength) {
                    fwrite(STDERR, "ZX0 verification failed: unpacked size is " . strlen($output) . ", expected {$expectedLength}\n");
                    exit(1);
                }

                return $output;
            }

            $lastOffset = $lastOffset * 128 - (zx0_read_byte($packed, $offset, $lastByte) >> 1);
            $backtrack = true;

            $length = zx0_read_interlaced_elias_gamma($packed, $offset, $bitMask, $bitValue, $backtrack, $lastByte, false) + 1;
            zx0_copy_from_last_offset($output, $lastOffset, $length, $expectedLength);

            if (!zx0_read_bit($packed, $offset, $bitMask, $bitValue, $backtrack, $lastByte)) {
                break;
            }
        }
    }
}

function zx0_read_byte(string $packed, int &$offset, int &$lastByte): int
{
    if ($offset >= strlen($packed)) {
        fwrite(STDERR, "ZX0 verification failed: truncated packed stream\n");
        exit(1);
    }

    $lastByte = ord($packed[$offset]);
    $offset++;

    return $lastByte;
}

function zx0_read_bit(
    string $packed,
    int &$offset,
    int &$bitMask,
    int &$bitValue,
    bool &$backtrack,
    int &$lastByte
): int {
    if ($backtrack) {
        $backtrack = false;
        return $lastByte & 1;
    }

    $bitMask >>= 1;
    if ($bitMask === 0) {
        $bitMask = 128;
        $bitValue = zx0_read_byte($packed, $offset, $lastByte);
    }

    return ($bitValue & $bitMask) ? 1 : 0;
}

function zx0_read_interlaced_elias_gamma(
    string $packed,
    int &$offset,
    int &$bitMask,
    int &$bitValue,
    bool &$backtrack,
    int &$lastByte,
    bool $inverted
): int {
    $value = 1;

    while (!zx0_read_bit($packed, $offset, $bitMask, $bitValue, $backtrack, $lastByte)) {
        $value = ($value << 1)
            | (zx0_read_bit($packed, $offset, $bitMask, $bitValue, $backtrack, $lastByte) ^ ($inverted ? 1 : 0));
    }

    return $value;
}

function zx0_copy_literals(
    string $packed,
    int &$offset,
    int &$lastByte,
    string &$output,
    int $length,
    int $expectedLength
): void {
    for ($i = 0; $i < $length; $i++) {
        $output .= chr(zx0_read_byte($packed, $offset, $lastByte));

        if (strlen($output) > $expectedLength) {
            fwrite(STDERR, "ZX0 verification failed: unpacked data is longer than {$expectedLength} bytes\n");
            exit(1);
        }
    }
}

function zx0_copy_from_last_offset(string &$output, int $lastOffset, int $length, int $expectedLength): void
{
    if ($lastOffset < 1 || $lastOffset > strlen($output)) {
        fwrite(STDERR, "ZX0 verification failed: invalid offset {$lastOffset}\n");
        exit(1);
    }

    for ($i = 0; $i < $length; $i++) {
        $sourceIndex = strlen($output) - $lastOffset;
        $output .= $output[$sourceIndex];

        if (strlen($output) > $expectedLength) {
            fwrite(STDERR, "ZX0 verification failed: unpacked data is longer than {$expectedLength} bytes\n");
            exit(1);
        }
    }
}
