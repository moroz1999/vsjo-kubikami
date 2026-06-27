# Enemies Domain

## Module Organization

- The `enemies` module should stay split by topic-specific include files.
- Do not accumulate unrelated enemy functions in `enemies.a80`; put movement, drawing, collision, data, and random helpers into separate `enemies.*.a80` files.
- Route point records live in per-room include files named `enemies.route_X_Y.a80`; `enemies.route.a80` keeps the route pointer table and shared route logic.
- Gameplay route rewires caused by room items belong to the corresponding `logic.room_X_Y` module, not to `enemies.route.a80`.

## Data

- Enemies are room entities drawn from the global `enemies.enemies` table.
- Enemy records use room coordinates as `room_x` and `room_y`, not a linear `room_id`.
- Enemy positions use room attribute coordinates, not pixel coordinates.
- Enemies are processed from the global table, without a current-room enemy buffer.
- Enemy records keep simulation mode separately from movement behavior.
- Enemies in the active room are always online; enemies outside it are always offline.
- Room initialization and offline route steps refresh simulation mode directly from enemy room coordinates.
- Enemy records keep a movement behavior: free, route, or aggressive.
- Enemy `state` stores only transient physical motion: stand, walk, fall, or jump.
- Route and aggressive behavior are not duplicated in `state`; they persist through fall and jump transitions in `behavior_mode`.
- Offline enemies currently always use route behavior.
- Online route enemies have a `1/8` chance to switch to free behavior at each reached route point.
- Each enemy record stores an individual free-to-route delay and timer.
- While an enemy is online and free, its timer counts down; after expiration it has a `1/8` chance to switch to route behavior.
- The current enemy delays are `150`, `180`, `220`, and `250` frames.
- Every failed or successful timer roll reloads the enemy's configured delay.
- A timer that expires during fall or jump waits at zero until stable physical motion resumes.
- Route/free behavior changes preserve route pointers.
- Generated room route tables keep each room's route point pointers in authored global table order.
- Offline target attachment is attempted at most once per enemy.
- A targetless online enemy searches after each successful free-to-route timer roll until a suitable point is found.
- Route attachment selects the first point in the enemy's room within the inclusive horizontal window `x +/- 7`, `y +/- 2`.
- A route-target search is never repeated, including when no suitable point was found.

## Collision

- Enemy collision applies 32 damage through the standard `hero.decrease_health` armor and health path.
- Online enemies in the active room collide with the hero.

## Drawing

- Enemy drawing is connected to the main draw pass through `enemies.draw_enemies`.
- Enemy drawing checks room coordinates; room membership guarantees that every visible enemy is online.
- Free online enemies are red.
- Route-following enemies are green, including online route followers in the active room.
- Standing online enemies are yellow.
- `behavior_aggressive` enemies are purple.
- Aggressive behavior is currently a placeholder; pursuit logic is not implemented yet.
- Enemies are drawn before the hero, so the existing hero draw can stay visually on top when positions overlap.
- Route debug drawing is compile-time gated by `debug.route_points_debug_enabled`.
- Route debug helpers and colors live in `debug.a80`.
- When route debug drawing is enabled, current-room route points are bright magenta.

## Topic Details

- [Free Movement](enemies.free.md)
- [Online Movement](enemies.online.md)
- [Offline Movement](enemies.offline.md)
- [Route Points](waypoints.md)
- [Route Point Logic](route-points.logic.md)
