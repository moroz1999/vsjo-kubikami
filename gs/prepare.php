<?php

declare(strict_types=1);

const BLOCK_SIZE = 10000;
const DATA_BLOCKS_END = 0xfe;
const TRDOS_FILE_SECTOR_LIMIT = 255;
const GS_SAMPLE_MEMORY_LIMIT = 466 * 1024;
const SFX_PRIORITY_LOW = 0x40;
const SFX_PRIORITY_MEDIUM = 0x80;
const SFX_PRIORITY_HIGH = 0xc0;
const SFX_SEEK_FIRST = 0x05;
const SFX_SEEK_LAST = 0x0a;

$projectRoot = dirname(__DIR__);
$outputDirectory = $projectRoot . '/build/gs';
$zx0Path = $projectRoot . '/bin/zx0.exe';
$samples = [
    [
        'handle' => 1,
        'name' => 'crowbar',
        'source' => $projectRoot . '/sfx/gs/crowbar.raw',
        'note' => 61,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 2,
        'name' => 'enemy_hit',
        'source' => $projectRoot . '/sfx/gs/attack.raw',
        'note' => 65,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_MEDIUM,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 3,
        'name' => 'jump_end',
        'source' => $projectRoot . '/sfx/gs/jumpend.raw',
        'note' => 65,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_MEDIUM,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 4,
        'name' => 'splash',
        'source' => $projectRoot . '/sfx/gs/splash.raw',
        'note' => 65,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_MEDIUM,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 5,
        'name' => 'glass_break',
        'source' => $projectRoot . '/sfx/gs/glass.raw',
        'note' => 61,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 6,
        'name' => 'waterdrop',
        'source' => $projectRoot . '/sfx/gs/waterdrop.raw',
        'note' => 57,
        'volume' => 0x20,
        'priority' => SFX_PRIORITY_LOW,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 7,
        'name' => 'take',
        'source' => $projectRoot . '/sfx/gs/itemtake.raw',
        'note' => 65,
        'volume' => 0x30,
        'priority' => SFX_PRIORITY_MEDIUM,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 8,
        'name' => 'itemdrop',
        'source' => $projectRoot . '/sfx/gs/itemdrop.raw',
        'note' => 65,
        'volume' => 0x30,
        'priority' => SFX_PRIORITY_MEDIUM,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 9,
        'name' => 'bugs',
        'source' => $projectRoot . '/sfx/gs/bugs.raw',
        'note' => 61,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 10,
        'name' => 'death',
        'source' => $projectRoot . '/sfx/gs/death.raw',
        'note' => 53,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 11,
        'name' => 'door',
        'source' => $projectRoot . '/sfx/gs/door.raw',
        'note' => 61,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 12,
        'name' => 'dynamite',
        'source' => $projectRoot . '/sfx/gs/dynamite.raw',
        'note' => 53,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 13,
        'name' => 'elevator',
        'source' => $projectRoot . '/sfx/gs/elevator.raw',
        'note' => 61,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 14,
        'name' => 'elevator_stop',
        'source' => $projectRoot . '/sfx/gs/elevatorstop.raw',
        'note' => 65,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_MEDIUM,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 15,
        'name' => 'energy',
        'source' => $projectRoot . '/sfx/gs/energy.raw',
        'note' => 53,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 16,
        'name' => 'fill_bottle',
        'source' => $projectRoot . '/sfx/gs/fillbottle.raw',
        'note' => 53,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 17,
        'name' => 'generator',
        'source' => $projectRoot . '/sfx/gs/generator.raw',
        'note' => 53,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 18,
        'name' => 'key',
        'source' => $projectRoot . '/sfx/gs/key.raw',
        'note' => 61,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 19,
        'name' => 'ladder',
        'source' => $projectRoot . '/sfx/gs/ladder.raw',
        'note' => 61,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 20,
        'name' => 'medkit',
        'source' => $projectRoot . '/sfx/gs/medkit.raw',
        'note' => 65,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_MEDIUM,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 21,
        'name' => 'menu',
        'source' => $projectRoot . '/sfx/gs/menu.raw',
        'note' => 61,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_MEDIUM,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 22,
        'name' => 'poison',
        'source' => $projectRoot . '/sfx/gs/poison.raw',
        'note' => 53,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 23,
        'name' => 'redcard',
        'source' => $projectRoot . '/sfx/gs/redcard.raw',
        'note' => 53,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 24,
        'name' => 'seeds',
        'source' => $projectRoot . '/sfx/gs/seeds.raw',
        'note' => 53,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
    [
        'handle' => 25,
        'name' => 'water_valve',
        'source' => $projectRoot . '/sfx/gs/watervalve.raw',
        'note' => 53,
        'volume' => 0x40,
        'priority' => SFX_PRIORITY_HIGH,
        'seek_first' => SFX_SEEK_FIRST,
        'seek_last' => SFX_SEEK_LAST,
    ],
];

$totalSampleBytes = 0;

// GS assigns handles in upload order; validate all sources before replacing generated data.
foreach ($samples as $sampleIndex => $sample) {
    if ($sample['handle'] !== $sampleIndex + 1) {
        fail("GS sample handles must be sequential from 1");
    }

    if (!is_file($sample['source'])) {
        fail("Sample source not found: {$sample['source']}");
    }

    $sampleSize = filesize($sample['source']);
    if ($sampleSize === false || $sampleSize === 0) {
        fail("Cannot read sample size: {$sample['source']}");
    }

    $totalSampleBytes += $sampleSize;
}

if ($totalSampleBytes > GS_SAMPLE_MEMORY_LIMIT) {
    fail(sprintf(
        "GS sample memory limit exceeded: %d bytes used, %d bytes available (466 KiB)",
        $totalSampleBytes,
        GS_SAMPLE_MEMORY_LIMIT
    ));
}

if (!is_file($zx0Path)) {
    fail("ZX0 compressor not found: {$zx0Path}");
}

if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0777, true)) {
    fail("Cannot create output directory: {$outputDirectory}");
}

// Release builds regenerate both binary data and its matching ASM table.
foreach (glob($outputDirectory . '/*') ?: [] as $oldFile) {
    if (is_file($oldFile) && !unlink($oldFile)) {
        fail("Cannot remove generated file: {$oldFile}");
    }
}

$diskData = '';
$sampleBlocks = [];

foreach ($samples as $sample) {
    $sourceData = file_get_contents($sample['source']);
    if ($sourceData === false || $sourceData === '') {
        fail("Cannot read sample source: {$sample['source']}");
    }

    $blocks = [];
    foreach (str_split($sourceData, BLOCK_SIZE) as $blockIndex => $sourceBlock) {
        // Delta history resets per block because the loader decodes blocks independently.
        $encodedBlock = deltaEncode($sourceBlock);
        $blockName = sprintf('s%02d_%02d', $sample['handle'], $blockIndex);
        $encodedPath = $outputDirectory . "/{$blockName}.delta";
        $packedPath = $outputDirectory . "/{$blockName}.zx0";

        if (file_put_contents($encodedPath, $encodedBlock) === false) {
            fail("Cannot write delta block: {$encodedPath}");
        }

        $command = escapeshellarg($zx0Path)
            . ' -f '
            . escapeshellarg($encodedPath)
            . ' '
            . escapeshellarg($packedPath);
        $commandOutput = [];
        exec($command . ' 2>&1', $commandOutput, $exitCode);
        if ($exitCode !== 0 || !is_file($packedPath)) {
            fail("ZX0 failed for {$sample['name']}: " . implode(PHP_EOL, $commandOutput));
        }

        if (!unlink($encodedPath)) {
            fail("Cannot remove temporary block: {$encodedPath}");
        }

        $packedData = file_get_contents($packedPath);
        if ($packedData === false || $packedData === '') {
            fail("Cannot read packed block: {$packedPath}");
        }

        $sectorCount = intdiv(strlen($packedData) + 255, 256);
        if ($sectorCount >= DATA_BLOCKS_END) {
            fail("Packed block is too large for the loader table: {$packedPath}");
        }

        // TR-DOS reads whole sectors, so the next ZX0 stream must start aligned.
        $paddedSize = $sectorCount * 256;
        $diskData .= $packedData . str_repeat("\0", $paddedSize - strlen($packedData));
        $blocks[] = [
            'sectors' => $sectorCount,
            'unpacked_size' => strlen($sourceBlock),
        ];
    }

    $sampleBlocks[] = [
        'sample' => $sample,
        'blocks' => $blocks,
    ];
}

// GSDATA.C is one sequential sector stream consumed by the generated table.
$diskDataPath = $outputDirectory . '/samples.bin';
if (file_put_contents($diskDataPath, $diskData) === false) {
    fail("Cannot write GS disk data: {$diskDataPath}");
}

$sectorCount = intdiv(strlen($diskData), 256);

$table = buildTable($sampleBlocks, $sectorCount);
$tablePath = $outputDirectory . '/samples.a80';
if (file_put_contents($tablePath, $table) === false) {
    fail("Cannot write GS loader table: {$tablePath}");
}

echo sprintf(
    "Prepared %d GS samples: %d/%d raw bytes, %d packed bytes, %d sectors (%d tracks + %d sectors)%s",
    count($samples),
    $totalSampleBytes,
    GS_SAMPLE_MEMORY_LIMIT,
    strlen($diskData),
    $sectorCount,
    intdiv($sectorCount, 16),
    $sectorCount % 16,
    PHP_EOL
);

function deltaEncode(string $source): string
{
    $encoded = '';
    $previousByte = 0;
    $length = strlen($source);

    for ($index = 0; $index < $length; $index++) {
        $currentByte = ord($source[$index]);
        $encoded .= chr(($currentByte - $previousByte) & 0xff);
        $previousByte = $currentByte;
    }

    return $encoded;
}

/**
 * @param array<int, array{sample: array<string, int|string>, blocks: array<int, array<string, int>>}> $sampleBlocks
 */
function buildTable(array $sampleBlocks, int $sectorCount): string
{
    $lines = [
        '; Generated by gs/prepare.php.',
        sprintf('gs_data_sector_count        equ%s%d', "\t", $sectorCount),
        sprintf('gs_data_file_count          equ%s%d', "\t", intdiv($sectorCount + TRDOS_FILE_SECTOR_LIMIT - 1, TRDOS_FILE_SECTOR_LIMIT)),
        sprintf('gs_data_skip_tracks         equ%s%d', "\t", intdiv($sectorCount, 16)),
        sprintf('gs_data_skip_sectors        equ%s%d', "\t", $sectorCount % 16),
        '',
        'gs_sample_table',
    ];

    foreach ($sampleBlocks as $entry) {
        $sample = $entry['sample'];
        $lines[] = sprintf('                ; %s', $sample['name']);
        $lines[] = sprintf('%sdb%s%d', str_repeat(' ', 28), "\t", $sample['handle']);

        foreach ($entry['blocks'] as $block) {
            $lines[] = sprintf(
                '%sdb%s%d',
                str_repeat(' ', 28),
                "\t",
                $block['sectors']
            );
            $lines[] = sprintf(
                '%sdw%s%d',
                str_repeat(' ', 28),
                "\t",
                $block['unpacked_size']
            );
        }

        $lines[] = sprintf('%sdb%strd_loader_data_end', str_repeat(' ', 28), "\t");
        $lines[] = sprintf(
            '%sdb%s%d,#%02x,#%02x,#%02x,#%02x',
            str_repeat(' ', 28),
            "\t",
            $sample['note'],
            $sample['volume'],
            $sample['priority'],
            $sample['seek_first'],
            $sample['seek_last']
        );
    }

    $lines[] = sprintf('%sdb%strd_loader_table_end', str_repeat(' ', 28), "\t");
    $lines[] = '';

    return implode(PHP_EOL, $lines);
}

function fail(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}
