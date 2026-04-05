# Algorithms

## Power-Of-Two Scaling

When an algorithm needs multiplication or division by `2^n`, use bit operations instead of generic math.
- `add a,a` doubles an 8-bit value with a short 1-byte instruction.
- `srl a` divides an unsigned 8-bit value by `2`.

## Room Logic Lifecycle

Room-specific gameplay logic lives in `logic/roomX_Y.a80` modules and is connected through three tables in `logic.a80`.
- `room_logic_pointers` stores the per-frame callback. The game loop calls it automatically each frame after hero movement and before water drops are updated.
- `room_onstart_pointers` stores an optional room-enter callback. `rooms.init_current_room` calls it after the current-room buffers are gathered and room bounds are calculated, but before the room is redrawn.
- `room_onend_pointers` stores an optional room-leave callback. `rooms.goto_*_room` calls it before the room coordinates are changed.

Use `dw 0` in the `onstart` or `onend` tables when a room does not need that hook.

Keep room mechanics inside the room module whenever the behavior is specific to one screen. Use shared engine modules only for generic helpers that can be reused by multiple rooms.
