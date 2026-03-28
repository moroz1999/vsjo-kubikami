# Domain

- This is a game where the hero runs through screen-sized rooms and moves between them.
- The project already contains elevators, items, room animations, teleports, water drops, and dedicated room logic.
- The common domain pattern is a global entity table plus a room-init stage that fills a buffer with pointers to entities belonging to the current room only.
- New room entities and mechanics should follow that pattern whenever possible instead of bypassing it with one-off hardcode.
- When changing logic, remember that hero coordinates and behavior are tied to the room, the screen, movement states, and the current-room buffers.

