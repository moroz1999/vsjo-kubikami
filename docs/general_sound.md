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

After detection, the loader sends cold-reset command `#F4`, then sets both independent effect-wide volume controls to their maximum:

| Volume layer | Command | Value |
|---|---:|---:|
| FX Master Volume | `#2B` | `#40` |
| FX Global Volume | `#3D` | `#40` |

These commands are not aliases. Setting only FX Master Volume does not guarantee maximum output while FX Global Volume remains lower. Each sample header also receives maximum volume `#40` through command `#41`, so every GS volume layer is explicitly maximized.

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

Sample numbers are explicit, not returned by command `#38`. The generated table selects samples `1..8` for the crowbar, enemy hit, hero landing, air-to-water splash, glass break, water-drop landing, item pickup, and item drop. Runtime sound calls use backend-neutral `sounds.event_*` IDs; the AY backend maps only events with authored AYFX data, while the GS backend maps events directly to these fixed sample numbers.

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
| Crowbar | `sounds.event_crowbar` (`2`) | `2` | `1` | `sfx/gs/wood.raw` | note `61`, volume `#40`, high priority `#C0`, seeks `#05/#0A` |
| Enemy attack | `sounds.event_enemy_hit` (`0`) | `0` | `2` | `sfx/gs/attack.raw` | note `65`, volume `#40`, priority `#80`, seeks `#05/#0A` |
| Hero lands after a jump or fall | `sounds.event_jump_end` (`13`) | `13` | `3` | `sfx/gs/jumpend.raw` | default note `65`; runtime note `65..68`, volume `#40`, priority `#80`, seeks `#05/#0A` |
| Hero enters water from air | `sounds.event_splash` (`21`) | `21` | `4` | `sfx/gs/splash.raw` | default note `65`; runtime note `65..68`, volume `#40`, priority `#80`, seeks `#05/#0A` |
| Stone breaks glass | `sounds.event_glass_break` (`12`) | `12` | `5` | `sfx/gs/glass.raw` | note `61`, volume `#40`, high priority `#C0`, seeks `#05/#0A` |
| Water drop lands | `sounds.event_waterdrop` (`23`) | `23` | `6` | `sfx/gs/waterdrop.raw` | default note `57`; runtime note `57..60`, volume `#20`, low priority `#40`, seeks `#05/#0A` |
| Item pickup | `sounds.event_take` (`22`) | `22` | `7` | `sfx/gs/take.raw` | note `65`, volume `#30`, priority `#80`, seeks `#05/#0A` |
| Item drop | `sounds.event_itemdrop` (`5`) | `5` | `8` | `sfx/gs/itemdrop.raw` | note `61`, volume `#30`, priority `#80`, seeks `#05/#0A` |

The landing event is emitted only on the transition from `hero.state_void` to `hero.state_ground`; entering water plays only the splash. Splash is queued only when the previous hero state was not swimming. A glass-break event is queued only after the stone action has passed its room and hero-position checks.

GS priorities use the direct `#01..#FE` scale: larger values have higher playback priority. Effects use three spaced bands: low `#40` for ambient sounds, medium `#80` for routine active gameplay, and high `#C0` for quest actions. Water drops are low; enemy hits, landing, splashes, item pickup, and item drop are medium; crowbar and glass-break effects are high.

The GS sample note rule is fixed by source rate, not recalculated at build time:

| Source rate | GS note |
|---:|---:|
| `22050 Hz` | `65` |
| `18000 Hz` | `61` |
| `14000 Hz` | `57` |

Known-rate samples follow that table: `wood.raw`, `glass.raw`, and `itemdrop.raw` use note `61`; `attack.raw`, `jumpend.raw`, `splash.raw`, and `take.raw` use note `65`; `waterdrop.raw` uses note `57`. Runtime randomization plays `65..68` for landing and splash, and `57..60` for water drops. Crowbar, enemy attack, landing, splash, and glass use full GS sample volume `#40`; water drops use half volume `#20`; item pickup and item drop use 75% volume `#30`.

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

The current RAW total is `93454` of `477184` bytes. The packed output is `63744` bytes: `249` sectors, or fifteen tracks plus nine sectors. Generated files live under ignored `build/gs/`; GS source samples are tracked under `sfx/gs/`, and the AYFX bank under `sfx/ay/`.

## Runtime Playback

Runtime gameplay code loads a backend-neutral `sounds.event_*` ID into `A` and calls `sounds.play`. `sounds.play` is a patched trampoline: AY modes route to the AY request handler, GS modes route to the direct GS handler, and muted modes return immediately. `sounds.configure` patches the play and frame trampolines when the sound mode changes. Event IDs follow the alphabetical order of the `.afx` files in the AYFX bank and are therefore also the zero-based indices expected by `AFXPLAY`; the editor displays the same effects as `1..25`.

GS keeps dedicated handlers for the original eight events. Every newer event temporarily falls through to `play_gs_itemdrop` and plays GS sample `8` from `itemdrop.raw` until a dedicated GS sample is assigned.

The AY backend never calls `AFXPLAY` directly from gameplay code. It stores one pending AYFX ID, and the IM 2 `sounds.frame` handler consumes it with `AFXPLAY` before advancing `AFXFRAME`. The GS backend sends commands immediately from gameplay code; it does not use the AY pending slot or the IM 2 SFX frame path.

To play a fixed-pitch GS effect, the game writes its sample number to `#B3` and sends command `#39` twice, waiting for command-ready status after each send. The repeated hero landing, splash, and water-drop effects first select the sample with `#2E`, set its randomized note with `#40`, and then use the same double `#39` path. Both copies therefore keep automatic channel allocation and the sample's priority and `SeekFirst/SeekLast` rules active; direct commands `#88..#8B` are not used because each targets one specific channel.

GS channels `0` and `1` feed the left side, while channels `2` and `3` feed the right. Every sample uses `SeekFirst = #05`, selecting channels `0` and `2`, and `SeekLast = #0A`, selecting the remaining channels `1` and `3`. The two consecutive starts therefore prefer one channel on each side. `general_sound.play_sample_random_note` adds `R & 3` to the event's base note, producing notes `65..68` for landing and splash and `57..60` for water drops.

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

PT3 music applies `music.volume_reduction` to software-controlled AY amplitudes during normal playback but keeps hardware envelopes enabled. Hardware envelope channels therefore retain their authored envelope level instead of being disabled merely because the steady reduction is non-zero. Starting `music.fade_out` makes `fade_step_count` non-zero; the player then disables hardware envelopes for the duration of the fade and continues lowering the software amplitudes. AYFX remains at its authored volume because AY effects are disabled in `AY+GS` and therefore cannot mask simultaneous GS playback.

See [TR-DOS Disk Loading](trdos.md) for disk placement, scratch memory, and the no-GS sector skip.
