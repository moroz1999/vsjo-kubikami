# TRD BASIC Loader

## Responsibility

`boot.B` is only a tokenized bootstrap. It configures the display and RAM, loads `LOADER.C` by name through TR-DOS, and transfers control to the machine-code loader. It must not load the screen, GS data, or game itself.

The current program is equivalent to:

```basic
1 BORDER 0
10 PAPER 0: CLS
20 CLEAR 62975
30 RANDOMIZE USR 15619: REM: LOAD "LOADER" CODE
40 RANDOMIZE USR 62976
```

`RANDOMIZE USR 15619` enters the TR-DOS interceptor at `#3D03`. The `REM: LOAD ...` tail supplies the command consumed by TR-DOS. `LOADER.C` carries load address `#F600`, and line `40` starts it there.

## Tokenized Line Format

Each BASIC line stored in `release.a80` has:

1. A two-byte big-endian line number.
2. A two-byte little-endian body length.
3. Tokenized line data.
4. A terminating byte `#0D`, included in the body length.

The loader uses these tokens:

| Token | Byte |
|---|---:|
| `USR` | `#C0` |
| `PAPER` | `#DA` |
| `BORDER` | `#E7` |
| `REM` | `#EA` |
| `LOAD` | `#EF` |
| `RANDOMIZE` | `#F9` |
| `CLS` | `#FB` |
| `CLEAR` | `#FD` |
| `CODE` | `#AF` |

Numeric literals contain both their ASCII representation and the ZX BASIC numeric marker `#0E` followed by the five-byte numeric value. The integer form used here is:

| Value | Hex value | Bytes after ASCII |
|---:|---:|---|
| `0` | `#0000` | `#0E,0,0,0,0,0` |
| `15619` | `#3D03` | `#0E,0,0,#03,#3D,0` |
| `62975` | `#F5FF` | `#0E,0,0,#FF,#F5,0` |
| `62976` | `#F600` | `#0E,0,0,0,#F6,0` |

The ASCII and binary forms must agree. Editing only the printed digits produces an invalid or misleading loader.

## RAMTOP and Loader Placement

The BASIC program is relocated by the ROM to its normal runtime area beginning at `#5CCB`; the `org #5B00` used while assembling `boot.B` is only a temporary source buffer for `savetrd`.

Placing `LOADER.C` near `#5DC0` required `CLEAR 23999` and left too little BASIC workspace, causing `RAMTOP no good`. The loader now resides at `#F600`, so `CLEAR 62975` sets RAMTOP to `#F5FF` and leaves the normal BASIC area available. This keeps it above the main game image and the runtime room buffer.

These values form one contract:

- `CLEAR` must use `trd_loader_address-1`.
- The final `USR` must use `trd_loader_address`.
- The `LOADER.C` directory load address must equal `trd_loader_address`.
- The BASIC runtime program must end below the loader.
- The saved game image and loader scratch buffers must end below the loader. Runtime-only buffers may reuse loader memory after the final `JP` transfers control to the game.

`release.a80` asserts the BASIC-program and loader boundary. The TR-DOS loader document defines the remaining memory boundaries.

## TRD Export

`savetrd` stores the tokenized bytes as `boot.B`; its final argument `1` makes BASIC line `1` the autostart line. A `.B` directory entry describes a BASIC program, so its directory fields must not be interpreted as a machine-code load address.

See [TR-DOS Disk Loading](trdos.md) for the file order and machine-code handoff.
