# Domain

- This is a game where the hero runs through screen-sized rooms and moves between them.
- The project already contains elevators, items, room animations, teleports, water drops, and dedicated room logic.
- The common domain pattern is a global entity table plus a room-init stage that fills a buffer with pointers to entities belonging to the current room only.
- New room entities and mechanics should follow that pattern whenever possible instead of bypassing it with one-off hardcode.
- When changing logic, remember that hero coordinates and behavior are tied to the room, the screen, movement states, and the current-room buffers.
- The selected room is loaded from immutable room data into `rooms.current_room_buf` on enter. Redraw, restore points, surface checks, and water checks use this mutable current-room buffer.
- Room art in `rooms/` is stored as RLE streams of the first 22 attribute rows. `rooms.init_current_room` unpacks the selected stream into `rooms.current_room_buf` before room `on_enter` callbacks run.
- Permanent item-driven room art is applied to `rooms.current_room_buf`, either when a one-shot animation finishes or from the room `on_enter` callback when the room's explicit effect flag is set.

## Topic Details

- [Enemies](domain.enemies.md)
- [Items](items.md)
- [Armors](domain.armors.md)
