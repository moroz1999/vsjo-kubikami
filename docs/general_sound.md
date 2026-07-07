# General Sound

## Scope

General Sound support exists only in the five `*-gs.trd` releases. Music always remains on AY; GS stores and plays sound effects only. No GS module-music upload code is included.

A GS image remains bootable without the device. The loader detects GS before touching `GSDATA.C`; if detection fails, it advances the TR-DOS cursor over that file and loads the game with AY as the default sound mode.

## Ports and Readiness Bits

| Purpose | Decimal | Hex |
|---|---:|---:|
| Data and argument port | `179` | `#B3` |
| Command and status port | `187` | `#BB` |

Two independent readiness checks are used:

- A command completes when status bit `0` becomes clear. The code reads `#BB`, rotates right with `rrca`, and waits while carry is set.
- The data channel accepts the next byte when status bit `7` becomes clear. The code reads `#BB`, rotates left with `rlca`, and waits while carry is set.

## Detection and Handoff

Detection follows this bounded sequence:

1. Write sample number `1` to `#B3`.
2. Write command `#2E` to `#BB`.
3. Read status up to `255` times.
4. Treat a clear status bit `0` as success.

The loader initializes byte `#5B00` to zero and changes it to one only after successful detection. In a `release_gs_trd` game, `general_sound.init` reads this byte. TAP, snapshot, and regular TRD builds force GS availability to zero instead of trusting unrelated memory at `#5B00`.

## Reset and Upload Protocol

After detection, the loader sends cold-reset command `#F4`, then sends `#03` to put every channel DAC at maximum hardware volume. It also sets both independent effect-wide volume controls to their maximum:

| Volume layer | Command | Value |
|---|---:|---:|
| Channel DAC high-volume mode | `#03` | Maximum |
| FX Master Volume | `#2B` | `#40` |
| FX Global Volume | `#3D` | `#40` |

These commands are not aliases. Setting only FX Master Volume does not guarantee maximum output while the DAC or FX Global Volume remains lower. Each sample header also receives maximum volume `#40` through command `#41`, so every GS volume layer is explicitly maximized.

Each sample upload uses:

1. Write the exact destination sample number to `#B3`.
2. Send `#38` to initialize upload into that sample number.
3. Send `#D1` to open the data channel.
4. Read, unpack, delta-decode, and stream every block.
5. Send `#D2` to close the data channel.
6. Apply the sample parameters.

Sample parameter commands are:

| Parameter | Command |
|---|---:|
| Note | `#40` |
| Volume, set to maximum `#40` | `#41` |
| Priority | `#45` |
| Seek first | `#46` |
| Seek last | `#47` |

The argument is written to `#B3` before its command is sent to `#BB`.

## Generated Sample Table

Sample numbers are explicit, not returned by command `#38`. The generated table selects samples `1..8` for the crowbar, enemy hit, hero landing, air-to-water splash, glass break, falling water drop, item pickup, and item drop. Runtime sound calls use backend-neutral `sounds.event_*` IDs; the AY backend maps only events with authored AYFX data, while the GS backend maps events directly to these fixed sample numbers.

`build/gs/samples.a80` uses this format:

```text
GS destination sample number
    packed block sector count
    unpacked block byte count
    ...more blocks...
#FE
note, volume, priority, seek first, seek last
...more samples...
#FF
```

`#FE` terminates one sample's block list. `#FF` terminates the complete table. Sector counts are bytes and unpacked sizes are words. `gs/prepare.php` currently requires sample numbers to be sequential from `1`.

The current upload order and gameplay mapping are:

| Gameplay event | Event ID | AYFX ID | GS sample | GS source | Parameters |
|---|---:|---:|---:|---|---|
| Crowbar | `sounds.event_crowbar` (`0`) | `1` | `1` | `sfx/wood.raw` | note `61`, volume `#40`, priority `#80`, seeks `#0F/#0F` |
| Enemy hit | `sounds.event_enemy_hit` (`1`) | `4` | `2` | `gs/sounds/enemy_hit.raw`, copied from `05_jump_start.raw` | note `71`, volume `#40`, priority `#80`, seeks `#0F/#0F` |
| Hero lands after a jump or fall | `sounds.event_jump_end` (`2`) | none | `3` | `sfx/jumpend.raw` | default note `65`; runtime note `65..68`, volume `#40`, priority `#80`, seeks `#0F/#0F` |
| Hero enters water from air | `sounds.event_splash` (`3`) | none | `4` | `sfx/splash.raw` | default note `65`; runtime note `65..68`, volume `#40`, priority `#80`, seeks `#0F/#0F` |
| Stone breaks glass | `sounds.event_glass_break` (`4`) | none | `5` | `sfx/glass.raw` | note `61`, volume `#40`, priority `#80`, seeks `#0F/#0F` |
| Water drop starts falling | `sounds.event_waterdrop` (`5`) | none | `6` | `sfx/waterdrop.raw` | default note `57`; runtime note `57..60`, volume `#20`, priority `#80`, seeks `#0F/#0F` |
| Item pickup | `sounds.event_take` (`6`) | none | `7` | `sfx/take.raw` | note `65`, volume `#30`, priority `#80`, seeks `#0F/#0F` |
| Item drop | `sounds.event_itemdrop` (`7`) | none | `8` | `sfx/itemdrop.raw` | note `53`, volume `#30`, priority `#80`, seeks `#0F/#0F` |

The landing event is emitted only on the transition from `hero.state_void` to `hero.state_ground`; entering water plays only the splash. Splash is queued only when the previous hero state was not swimming. A glass-break event is queued only after the stone action has passed its room and hero-position checks.

The GS sample note rule is fixed by source rate, not recalculated at build time:

| Source rate | GS note |
|---:|---:|
| `22050 Hz` | `65` |
| `18000 Hz` | `61` |
| `14000 Hz` | `57` |

Known-rate samples follow that table: `wood.raw` and `glass.raw` use note `61`; `jumpend.raw`, `splash.raw`, and `take.raw` use note `65`; `waterdrop.raw` uses note `57`. Runtime randomization plays `65..68` for landing and splash, and `57..60` for water drops. `itemdrop.raw` is `11025 Hz` and uses note `53`, one octave below the `22050 Hz` rule. `enemy_hit.raw` keeps note `71` until its source rate is measured. Crowbar, landing, splash, and glass use full GS sample volume `#40`; water drops use half volume `#20`; item pickup and item drop use 75% volume `#30`.

## Sample Preparation

`compile.bat` runs `php gs/prepare.php` before assembling any release:

1. Sum the uncompressed RAW source sizes and reject totals above `466 KiB` (`477184` bytes) before replacing generated data.
2. Split every raw sample into blocks of at most `10000` bytes.
3. Delta-encode each block independently, starting its previous-byte value at zero.
4. Pack each encoded block with the forward ZX0 format.
5. Pad every packed block to a `256`-byte sector boundary.
6. Concatenate the padded blocks into `build/gs/samples.bin`.
7. Generate the block table and total track/sector skip in `build/gs/samples.a80`.

The loader reads a packed block into `#8000`, decompresses it with `dzx0_turbo` to `#C000`, reverses the delta encoding in place, and streams the exact unpacked byte count to GS. Resetting the delta accumulator for every block must match the encoder.

The current RAW total is `81425` of `477184` bytes. The packed output is `58112` bytes: `227` sectors, or fourteen tracks plus three sectors. Generated files live under ignored `build/gs/`; the raw source samples are tracked under `gs/sounds/` and `sfx/`.

## Runtime Playback

Runtime gameplay code loads a backend-neutral `sounds.event_*` ID into `A` and calls `sounds.play`. `sounds.play` is a patched trampoline: AY modes route to the AY request handler, GS modes route to the direct GS handler, and muted modes return immediately. `sounds.configure` patches the play and frame trampolines when the sound mode changes.

The AY backend never calls `AFXPLAY` directly from gameplay code. It stores one pending AYFX ID, and the IM 2 `sounds.frame` handler consumes it with `AFXPLAY` before advancing `AFXFRAME`. The GS backend sends commands immediately from gameplay code; it does not use the AY pending slot or the IM 2 SFX frame path.

To play a fixed-pitch GS effect, the game writes its sample number to `#B3`, sends command `#39`, and waits for command-ready status. The repeated hero landing, splash, and water-drop effects use direct-play command `#88` with a per-play note argument. `general_sound.play_sample_random_note` adds `R & 3` to the event's base note, producing notes `65..68` for landing and splash and `57..60` for water drops; the sample headers remain unchanged for other playback paths. After command-ready status, the note is written to `#B3`, and the game waits for data-ready status.

Main-menu item `6` cycles these modes:

| Mode | AY music | AY effects | GS effects |
|---|:---:|:---:|:---:|
| `AY` | Yes | Yes | No |
| `AY+GS` | Yes | No | Yes |
| `Music` | Yes | No | No |
| `AY SFX` | No | Yes | No |
| `GS SFX` | No | No | Yes |
| `No sound` | No | No | No |

The initial mode is `AY+GS` after successful GS detection and `AY` otherwise. `AY+GS` means AY music with GS effects; it does not play AY and GS copies of the same effect simultaneously. When GS is unavailable, menu cycling skips `AY+GS` and `GS SFX`.

PT3 music uses its signed master offset only for fade-out, so playback starts at the authored AY amplitude: maximum level `15` remains `15`. AYFX remains at its authored volume because AY effects are disabled in `AY+GS` and therefore cannot mask simultaneous GS playback. The fade uses all fifteen PT3 volume steps.

See [TR-DOS Disk Loading](trdos.md) for disk placement, scratch memory, and the no-GS sector skip.
