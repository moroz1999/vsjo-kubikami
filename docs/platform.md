# Platform

- The target platform is `ZX Spectrum 128`.
- The project is built with `sjasmplus`: `compile.bat` runs `php tools/map-viewer/generate_asm.php --project` to regenerate `routes/enemies.route*.a80` and `tools/map-viewer/routes-data.js` from split route JSON data under `routes/`, then runs `php pack_rooms.php` before assembling the final images, regenerating packed `rooms/*.zx0` from editable `rooms_unpacked/*.atr`.
- The program entry point is in `main.a80`. `compile.bat` creates six final files under `release/`: Russian, English, Czech, Polish, and Spanish TAP images plus a Russian TRD image.
- TAP images use the `sjasmplus` snapshot loader and include `loading.scr` at `#4000`; the build patches each generated tape BASIC so `BORDER 0` is its first command. The Russian TRD image contains an autostarting `boot.B`, the loading screen in `LOADING.C`, and the game in `KUBIKAMI.C`. The compact tokenized loader starts from line `1` with `BORDER 0`, sets black paper, clears the screen, executes `CLEAR 24575`, loads `LOADING.C` and `KUBIKAMI.C` through the `RANDOMIZE USR 15619:REM:` TR-DOS interceptor, then starts the game with `RANDOMIZE USR 24576`.
- IM 2 uses only the normal 48K address space and does not switch RAM banks. The `JP` dispatch occupies `#FDFD..#FDFF`, and the 257-byte vector table occupies `#FE00..#FF00`. Program data must end at or before `#FDFD`; `main.a80` enforces this boundary with an assembler assertion.
- The PT3 player and `music/compiled.C` module are resident in the same 48K address space. Music is initialized while interrupts are disabled and advanced once per IM 2 interrupt without memory paging.
- AYFX effects use the resident `music/sounds.afb` bank and always replace PT3 channel C while active. PT3 channels A and B continue playing; the shared AY noise period can still affect music that uses noise.
- All changes must remain compatible with the current screen, room, and rendering modules: `screen_utils`, `draw`, and `rooms`.
- Kempston input reads active-high `000FUDLR` from port `#1f`. Cursor input maps `5/8/7/6/0` to `Left/Right/Jump/Dive/Use`.
