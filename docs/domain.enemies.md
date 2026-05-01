# Enemies Domain

## Module Organization

- The `enemies` module should stay split by topic-specific include files.
- Do not accumulate unrelated enemy functions in `enemies.a80`; put movement, drawing, collision, data, and random helpers into separate `enemies.*.a80` files.

## Data

- Enemies are room entities drawn from the global `enemies.enemies` table.
- Enemy records use room coordinates as `room_x` and `room_y`, not a linear `room_id`.
- Enemy positions use room attribute coordinates, not pixel coordinates.
- Enemies are processed from the global table, without a current-room enemy buffer.
- Enemy records keep a screen status: online when the enemy belongs to the active room, offline otherwise.
- Enemy screen status is refreshed during room initialization and can be forced offline by route exits.

## Collision

- Enemy collision applies 32 damage through the standard `hero.decrease_health` armor and health path.

## Drawing

- All enemies share one flashing color value, `enemies.enemy_flash_color`.
- The shared enemy flash color is selected from `enemies.enemy_flash_colors` through the common draw color switch.
- Enemy drawing is connected to the main draw pass through `enemies.draw_enemies`.
- Enemy drawing checks the current screen status before painting each enemy.
- Enemies are drawn before the hero, so the existing hero draw can stay visually on top when positions overlap.
- Temporary debug drawing colors standing enemies yellow, free-moving enemies with the existing red flash, and route-following enemies green.
- Temporary route-point debug drawing paints current-room route points black.

## Topic Details

- [Free Movement](enemies.free.md)
- [Online Movement](enemies.online.md)
- [Offline Movement](enemies.offline.md)
- [Route Points](waypoints.md)
