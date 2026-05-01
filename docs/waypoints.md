# Route Points Domain

## Enemy Data

- Route following is a path-based enemy model that exists beside free roaming instead of replacing it.
- Switching rules between free roaming and route following are not decided yet; the current work only proves the route mechanism itself.
- A route-following enemy remembers the last point it reached and the point it is currently trying to reach.
- `last_route_point_ptr` is the last reached route point.
- `target_route_point_ptr` is the route point the enemy is currently moving toward.
- A route-following enemy initially chooses a nearby route point; during the first test route this start is fixed by hand.

## Route Point Data

```text
route_point:
  room_x                  byte
  room_y                  byte
  x                       byte
  y                       byte
  type                    byte
  next_route_point_ptr    word
  alternative_point_ptr   word
```

- Route points belong to rooms and have room-local coordinates.
- Route points are authored so that a route-following enemy can physically reach the next target and should not skip over it.
- Each route point is 9 bytes.

## Types

```text
route_point_normal
route_point_jump_left
route_point_jump_right
route_point_exit
```

- `route_point_normal` is an ordinary point.
- `route_point_jump_left` starts the next transition with a left jump.
- `route_point_jump_right` starts the next transition with a right jump.
- `route_point_exit` is a room-exit point on a screen edge.

## Links

- Each route point has a main continuation and an alternative continuation.
- Both direct links are always valid.
- A point without a fork uses the same continuation for both choices.
- A fork uses different main and alternative continuations.
- A dead end points both continuations back to the previous point.
- A room-exit point points both continuations to the matching entry point in the neighboring room.
- Correctly authored point links make normal points, forks, dead ends, and exits work without special-case route decisions.
- Current first-stage implementation uses only the main continuation, does not yet choose alternatives, and does not yet do low-probability turnbacks.

## Exit Points

- Exit points sit on a screen edge and lead to an entry point in a neighboring room.
- After reaching an exit point, an online route follower moves to the linked entry point, takes that point's room and position, marks its old cell for restore, and becomes offline immediately.

## Test Route

- The first room `0,0` test route is `(4,9)`, `(12,10)`, `(20,9)`, `(31,5)`, `(20,16)`, `(31,5)`.
- In the first room `0,0` test route, the fifth point descends to the basement end under the right slope and the sixth returns to the right screen edge.
- The current cross-room test route exits from room `0,0` at `(31,5)` to the room `1,0` entry point `(0,5)`.

## Movement References

- Online route movement is documented in [Enemies Online Movement](enemies.online.md).
- Offline route movement is documented in [Enemies Offline Movement](enemies.offline.md).
