# Platform

- The target platform is `ZX Spectrum 128`.
- The project is built with `sjasmplus`: `compile.bat` runs `_sjasmplus\\sjasmplus.exe main.a80`.
- The program entry point is in `main.a80`, and the build output is currently `qsave1.sna`.
- All changes must remain compatible with the current screen, room, and rendering modules: `screen_utils`, `draw`, and `rooms`.
- This section is reserved for future hardware details such as memory layout, screen organization, attributes, and other platform-specific rules.

