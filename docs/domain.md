# Domain

- This is a game where the hero runs through screen-sized rooms and moves between them.
- The project already contains elevators, items, room animations, teleports, water drops, and dedicated room logic.
- The common domain pattern is a global entity table plus a room-init stage that fills a buffer with pointers to entities belonging to the current room only.
- New room entities and mechanics should follow that pattern whenever possible instead of bypassing it with one-off hardcode.
- When changing logic, remember that hero coordinates and behavior are tied to the room, the screen, movement states, and the current-room buffers.
- The selected room is loaded from immutable room data into `rooms.current_room_buf` on enter. Redraw, restore points, surface checks, and water checks use this mutable current-room buffer.
- Room art in `rooms/` is stored as ZX0 streams of the first 22 attribute rows. `rooms.init_current_room` unpacks the selected stream into `rooms.current_room_buf` before room `on_enter` callbacks run.
- Permanent item-driven room art is applied to `rooms.current_room_buf`, either when a one-shot animation finishes or from the room `on_enter` callback when the room's explicit effect flag is set.
- Water wave visuals are background room animations drawn one attribute row above the physical water surface in rooms `0,1`, `1,2`, and `4,3`; the physical water remains in room data for movement checks.
- Timed rain in room `1,0` uses room waterdrops with a phase mask; disabling a visible rain drop must queue its last attribute point for restore before parking it at the source.
- Room `1,3` boss restores its old arm lines and jaw column with black attributes for speed; moving 2x2 joint/head markers keep last drawn attribute addresses so erase can clear by address, while the red floor under jaws is still restored through `draw.restore_pixel`.
- Room `3,3` has a point-only Bezier boss: its right anchor is fixed near the wall at `(30,12)`, while the left endpoint acts as the head. In idle mode the head targets the area around `(5,16)`; in hunting mode it targets the hero. Current head movement always steps toward the selected target after `boss_hunt_move_delay`, so losing the hero returns the head smoothly instead of snapping. Five dots per segment are recalculated at runtime; the central two segments draw an extra vertical dot per point. Old dots erase to black from stored screen addresses, and colors flicker between bright green-on-cyan and bright cyan-on-green. Applying the bug jar changes the boss to a dissolving state that draws fewer points each frame; once the visible point budget reaches zero, the final restored frame clears the old dots and future boss drawing is disabled.
- `objects.check_coords` treats water as passable, but hero state checks must also treat water below the hero as non-solid; otherwise the water surface becomes jumpable ground.
- Hero floor checks at the bottom row must go through the room-edge-aware coordinate check instead of reading `rooms.current_room_buf` past row `21`.
- Swimming upward from water into a non-water passable cell starts a hero jump arc in the requested direction; swimming inside water remains swim movement.
- The program opens on a pixel menu before game initialization. Number keys `1` through `5` activate the matching menu item directly.
- The menu stores the selected keyboard, Kempston, or Cursor mode in `menu.input_mode` and five redefined key scan codes in `menu.assigned_keys`. Gameplay input does not consume these settings yet.
- Zero hero health opens a cleared screen with `Конец Игры`; poisoning the room `1,3` purple boss opens a cleared screen with `Успех`. Both end screens wait for any key and return to the main menu.

## Topic Details

- [Enemies](domain.enemies.md)
- [Items](items.md)
- [Armors](domain.armors.md)
- [Medkits](domain.medkits.md)
