# Route Points Domain

## Enemy Data

- Route following is a path-based enemy model that exists beside free roaming instead of replacing it.
- Switching rules between free roaming and route following are not decided yet; the current work only proves the route mechanism itself.
- A route-following enemy remembers the last point it reached and the point it is currently trying to reach.
- `last_route_point_ptr` is the last reached route point.
- `target_route_point_ptr` is the route point the enemy is currently moving toward.
- A route-following enemy starts from authored route pointers in `enemies.data.a80`.

## Route Point Data

```text
route_point:
  room_x                  byte
  room_y                  byte
  x                       byte
  y                       byte
  type                    byte
  top_left_point_ptr      word
  bottom_right_point_ptr  word
  alternative_point_ptr   word
```

- Route points belong to rooms and have room-local coordinates.
- Route points are authored so that a route-following enemy can physically reach the next target and should not skip over it.
- Cross-room entry points must sit on a screen edge.
- Neighboring route points are normally spaced 5-7 cells apart.
- Shorter or longer links are reserved for jump arcs, fall lanes, and turns into a specific doorway, shaft, or passage.
- Each route point is 11 bytes.

## Types

```text
route_point_normal
route_point_jump_left
route_point_jump_right
route_point_exit
route_point_wait
```

- `route_point_normal` is an ordinary point.
- `route_point_jump_left` starts the next transition with a left jump after `route_jump_delay_frames` frames.
- `route_point_jump_right` starts the next transition with a right jump after `route_jump_delay_frames` frames.
- `route_point_exit` is a room-exit point on a screen edge.
- `route_point_wait` is an elevator wait point: online enemies keep route-following active and stand until elevator or jump physics reaches the next target.

## Links

- Each route point has one `top_left_point_ptr` neighbor and one `bottom_right_point_ptr` neighbor.
- Both direct links are always valid.
- A point with only one physical neighbor stores the same neighbor in both link fields.
- `alternative_point_ptr` is an optional branch. A zero high byte disables the alternative branch.
- Route selection first compares `last_route_point_ptr` with the two direct links and selects the opposite direct neighbor.
- When `last_route_point_ptr` matches neither direct link, route selection falls back to `bottom_right_point_ptr`.
- If `alternative_point_ptr` is enabled, route selection chooses 50/50 between the direct neighbor and the alternative branch.
- Correctly authored point links make two-way lines, dead ends, item rewires, random forks, and exits work without special point types.

## Exit Points

- Exit points normally sit on a screen edge and lead to an edge entry point in a neighboring room.
- Teleport route handoff points can also use `route_point_exit` inside a room when the selected neighbor belongs to another room.
- After reaching an exit point, an online route follower moves to the selected neighbor only when that neighbor belongs to another room.
- Exit points can also act as normal edge entries: when the selected neighbor is in the same room, the enemy keeps routing physically instead of teleporting.
- Cross-room exit movement takes the selected point's room and position, marks the old cell for restore, and becomes offline immediately.

## Room Logic

- Room-specific route graph decisions and item-driven rewires are documented in [Route Point Logic](route-points.logic.md).

## Authoring Tools

- `routes/manifest.json` is the route manifest for enemy route asm generation and the browser map viewer.
- Route points are split by room under `routes/room_X_Y.json`.
- Run `php tools/map-viewer/import_from_asm.php` to refresh JSON data from current route asm files.
- Run `php tools/map-viewer/generate_asm.php` to emit reviewable asm files into `tools/map-viewer/generated/`; use `--project` only when intentionally replacing the root route asm files.
- `compile.bat` regenerates the project route asm from the split route JSON data before assembling the game.

## Movement References

- Online route movement is documented in [Enemies Online Movement](enemies.online.md).
- Offline route movement is documented in [Enemies Offline Movement](enemies.offline.md).
