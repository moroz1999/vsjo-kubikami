# Algorithms

## Power-Of-Two Scaling

When an algorithm needs multiplication or division by `2^n`, use bit operations instead of generic math.
- `add a,a` doubles an 8-bit value with a short 1-byte instruction.
- `srl a` divides an unsigned 8-bit value by `2`.

## Room Logic Lifecycle

Room-specific gameplay logic lives in `logic/roomX_Y.a80` modules and is connected through callback pointers in each `rooms.room` entry.
- `logic_pointer` stores the per-frame callback. The game loop calls it automatically each frame after hero movement and before water drops are updated.
- `extra_draw_pointer` stores the room-specific renderer. Room initialization copies it into the early and late draw call sites: phase `a=0` restores or draws background-layer effects, and phase `a=1` draws foreground room effects. Rooms without custom drawing use `draw.empty_room_extra_draw`.
- `on_enter_pointer` stores the room-enter callback. `rooms.init_current_room` calls it after current-room entity buffers are gathered, room bounds are calculated, and source room data is copied into `rooms.current_room_buf`, but before the room is redrawn.
- `onend_pointer` stores an optional room-leave callback. `rooms.goto_*_room` calls it before the room coordinates are changed.

Use `logic.empty_room_on_enter` for rooms that do not need enter-time work. Use `dw 0` only for optional leave hooks that do not need to run.

Keep room mechanics inside the room module whenever the behavior is specific to one screen. Use shared engine modules only for generic helpers that can be reused by multiple rooms.
