<?php

const ROOM_WIDTH = 32;
const ROOM_HEIGHT = 22;
const ROOM_SIZE = ROOM_WIDTH * ROOM_HEIGHT;
const ROOM_AMOUNT_X = 7;
const ROOM_AMOUNT_Y = 4;

$sourceDir = __DIR__ . DIRECTORY_SEPARATOR . 'rooms_unpacked';
$targetDir = __DIR__ . DIRECTORY_SEPARATOR . 'rooms';

if (!is_dir($sourceDir)) {
    fwrite(STDERR, "Missing source directory: {$sourceDir}\n");
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
        $fileName = "room{$x},{$y}.atr";
        $sourcePath = $sourceDir . DIRECTORY_SEPARATOR . $fileName;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

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
        $packed = pack_room($room);

        if (unpack_room($packed) !== $room) {
            fwrite(STDERR, "Packed room verification failed: {$sourcePath}\n");
            exit(1);
        }

        if (file_put_contents($targetPath, $packed) === false) {
            fwrite(STDERR, "Cannot write packed room: {$targetPath}\n");
            exit(1);
        }

        $totalSource += ROOM_SIZE;
        $totalPacked += strlen($packed);

        printf("%s: %d -> %d bytes\n", $fileName, ROOM_SIZE, strlen($packed));
    }
}

printf("Total: %d -> %d bytes\n", $totalSource, $totalPacked);

function pack_room(string $room): string
{
    $packed = '';
    $length = strlen($room);
    $offset = 0;

    while ($offset < $length) {
        $color = $room[$offset];
        $runLength = 1;

        while (
            $runLength < 255
            && $offset + $runLength < $length
            && $room[$offset + $runLength] === $color
        ) {
            $runLength++;
        }

        $packed .= chr($runLength) . $color;
        $offset += $runLength;
    }

    return $packed . "\x00\x00";
}

function unpack_room(string $packed): string
{
    $room = '';
    $length = strlen($packed);
    $offset = 0;

    while ($offset + 1 < $length) {
        $runLength = ord($packed[$offset]);
        $color = $packed[$offset + 1];
        $offset += 2;

        if ($runLength === 0) {
            return $room;
        }

        $room .= str_repeat($color, $runLength);
    }

    return $room;
}
