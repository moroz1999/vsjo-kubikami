# Enemies Domain

## Module Organization

- The `enemies` module should stay split by topic-specific include files.
- Do not accumulate unrelated enemy functions in `enemies.a80`; put movement, drawing, collision, data, and random helpers into separate `enemies.*.a80` files.

## Data

- Enemies are room entities drawn from the global `enemies.enemies` table.
- Enemy records use room coordinates as `room_x` and `room_y`, not a linear `room_id`.
- Enemy positions use room attribute coordinates, not pixel coordinates.
- Enemy movement pacing is controlled by enemy-local movement delay and movement timer fields.
- Enemies are processed from the global table, without a current-room enemy buffer.

## State

- Enemy state recalculation is controlled by an enemy-local state timer.
- Enemy state recalculation chooses standing with chance 1/4 and walking with chance 3/4.
- When entering the walking state, an enemy randomly chooses left or right direction.

## Movement

- Standing enemies do not move.
- Walking enemies move horizontally in their selected direction.
- Moving enemies check the next room cell through the same `objects.check_coords` path used by the hero.
- Enemies move only when the next cell is reported as `objects.b_empty`.
- When the next cell is empty, enemies also check the down-side cell in the movement direction.
- If the down-side cell is empty, enemies have a 1/2 chance to reverse direction and skip that movement step.

## Collision

- Enemy collision applies 32 damage through the standard `hero.decrease_health` armor and health path.

## Drawing

- All enemies share one flashing color value, `enemies.enemy_flash_color`.
- The shared enemy flash color is selected from `enemies.enemy_flash_colors` through the common draw color switch.
- Enemy drawing is connected to the main draw pass through `enemies.draw_enemies`.
- Enemy drawing checks whether each enemy belongs to the current room before painting it.
- Enemies are drawn before the hero, so the existing hero draw can stay visually on top when positions overlap.
