<?php

declare(strict_types=1);

function read_word(string $data, int $offset): int
{
    return ord($data[$offset]) | (ord($data[$offset + 1]) << 8);
}

function replace_word(string $data, int $offset, int $value): string
{
    return substr_replace($data, pack('v', $value), $offset, 2);
}

function block_checksum(string $data): int
{
    $checksum = 0;
    $length = strlen($data);

    for ($index = 0; $index < $length; $index++) {
        $checksum ^= ord($data[$index]);
    }

    return $checksum;
}

function require_tap(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function patch_tap_loader(string $filename): void
{
    $tap = file_get_contents($filename);
    require_tap($tap !== false, "Cannot read {$filename}");
    require_tap(strlen($tap) >= 57, "{$filename}: file is too short");

    $header_block_length = read_word($tap, 0);
    require_tap($header_block_length === 19, "{$filename}: invalid BASIC header block");
    require_tap(block_checksum(substr($tap, 2, $header_block_length)) === 0, "{$filename}: invalid BASIC header checksum");

    $header = substr($tap, 2, $header_block_length - 1);
    require_tap(ord($header[0]) === 0, "{$filename}: first block is not a header");
    require_tap(ord($header[1]) === 0, "{$filename}: first file is not BASIC");
    require_tap(trim(substr($header, 2, 10)) === 'LOADER', "{$filename}: unexpected BASIC filename");

    $program_length = read_word($header, 12);
    $variables_offset = read_word($header, 16);
    $data_block_offset = 2 + $header_block_length;
    $data_block_length = read_word($tap, $data_block_offset);
    require_tap($data_block_length === $program_length + 2, "{$filename}: BASIC data length mismatch");
    require_tap(
        block_checksum(substr($tap, $data_block_offset + 2, $data_block_length)) === 0,
        "{$filename}: invalid BASIC data checksum"
    );

    $data_block = substr($tap, $data_block_offset + 2, $data_block_length);
    require_tap(ord($data_block[0]) === 255, "{$filename}: invalid BASIC data flag");

    $program = substr($data_block, 1, -1);
    $border_command = chr(0xe7) . '0' . chr(0x0e) . str_repeat(chr(0), 5) . ':';

    require_tap(strlen($program) >= 5, "{$filename}: BASIC program is too short");
    require_tap(ord($program[0]) === 0 && ord($program[1]) === 10, "{$filename}: BASIC does not start at line 10");

    if (substr($program, 4, strlen($border_command)) === $border_command) {
        return;
    }

    require_tap(ord($program[4]) === 0xfd, "{$filename}: CLEAR is not the first BASIC command");

    $program = substr($program, 0, 4) . $border_command . substr($program, 4);
    $program = replace_word($program, 2, read_word($program, 2) + strlen($border_command));
    $new_program_length = strlen($program);

    $header = replace_word($header, 12, $new_program_length);
    $header = replace_word($header, 16, $variables_offset + strlen($border_command));
    $header_block = pack('v', $header_block_length) . $header . chr(block_checksum($header));

    $data = chr(255) . $program;
    $new_data_block_length = strlen($data) + 1;
    $new_data_block = pack('v', $new_data_block_length) . $data . chr(block_checksum($data));
    $remaining_blocks = substr($tap, $data_block_offset + 2 + $data_block_length);
    $patched_tap = $header_block . $new_data_block . $remaining_blocks;

    require_tap(file_put_contents($filename, $patched_tap, LOCK_EX) === strlen($patched_tap), "Cannot write {$filename}");
}

if ($argc < 2) {
    fwrite(STDERR, "Usage: php patch_tap_loader.php <file.tap> [file.tap ...]\n");
    exit(1);
}

try {
    foreach (array_slice($argv, 1) as $filename) {
        patch_tap_loader($filename);
    }
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
