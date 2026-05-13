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
- Enemy records keep a simulation mode: online when the enemy belongs to the active room, offline otherwise.
- Enemy simulation mode is refreshed during room initialization and can be forced offline by route exits and physical room transitions.

## Collision

- Enemy collision applies 32 damage through the standard `hero.decrease_health` armor and health path.

## Drawing

- All enemies share one flashing color value, `enemies.enemy_flash_color`.
- The shared enemy flash color is selected from `enemies.enemy_flash_colors` through the common draw color switch.
- Enemy drawing is connected to the main draw pass through `enemies.draw_enemies`.
- Enemy drawing checks the current simulation mode before painting each enemy.
- Enemies are drawn before the hero, so the existing hero draw can stay visually on top when positions overlap.
- Route debug drawing is compile-time gated by `route_points_debug_enabled` and disabled by default.
- When route debug drawing is enabled, standing enemies are yellow, route-following enemies are green, and current-room route points are bright magenta.

## Topic Details

- [Free Movement](enemies.free.md)
- [Online Movement](enemies.online.md)
- [Offline Movement](enemies.offline.md)
- [Route Points](waypoints.md)
- [Route Point Logic](route-points.logic.md)
