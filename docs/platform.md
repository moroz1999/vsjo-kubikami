# Platform

- The target platform is `ZX Spectrum 128`.
- The project is built with `sjasmplus`: `compile.bat` runs `php tools/map-viewer/generate_asm.php --project` to regenerate route asm and `tools/map-viewer/routes-data.js` from split route JSON data under `routes/`, then runs `php pack_rooms.php` before `_sjasmplus\\sjasmplus.exe main.a80`, regenerating `rooms/` from `rooms_unpacked/`.
- The program entry point is in `main.a80`, and the build output is currently `qsave1.sna`.
- All changes must remain compatible with the current screen, room, and rendering modules: `screen_utils`, `draw`, and `rooms`.
- Kempston input reads active-high `000FUDLR` from port `#1f`. Cursor input maps `5/8/7/6/0` to `Left/Right/Jump/Dive/Use`.
- This section is reserved for future hardware details such as memory layout, screen organization, attributes, and other platform-specific rules.
