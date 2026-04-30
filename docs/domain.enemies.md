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
- Enemy records keep a screen status: online when the enemy belongs to the active room, offline otherwise.
- Enemy screen status is refreshed during room initialization only.

## State

- Enemy state recalculation is controlled by an enemy-local state timer.
- Enemy state timer delay is stored per enemy.
- Enemy state recalculation chooses standing with chance 1/4 and walking with chance 3/4.
- When entering the walking state, an enemy randomly chooses left or right direction.
- Enemy random movement decisions mix the low bit of the Z80 `R` register into the enemy random sequence.
- Enemy jump state uses an enemy-local jump step and phase-delay timer.

## Movement

- Only online enemies process per-frame movement.
- Offline enemies currently skip movement and are reserved for later offline logic.
- Standing enemies do not move.
- Walking enemies move horizontally in their selected direction.
- Enemies enter falling state when the cell below them is empty.
- Falling enemies move downward until the cell below is no longer empty.
- After landing, falling enemies continue walking in the same direction they had before falling.
- Falling speed is shared by all enemies.
- Jumping enemies ignore fall-state recalculation and state-timer recalculation until the jump arc ends.
- Enemy jumps use the same eight-step arc shape as the hero, but apply enemy movement rules and stay inside the current room.
- After an enemy jump arc ends, normal falling checks resume on the next enemy update.
- Moving enemies check the next room cell through the same `objects.check_coords` path used by the hero.
- Enemies move only when the next cell is reported as `objects.b_empty`.
- When the next cell in the walking direction is blocked, enemies check the up-side cell in that direction.
- If the up-side cell is empty, enemies climb there diagonally in the same movement step.
- If the up-side cell is also blocked, enemies reverse direction immediately.
- When the next cell is empty, enemies also check the down-side cell in the movement direction.
- When the down-side cell is empty, enemies first have a 1/4 chance to start a directional jump before checking the drop depth.
- If the early jump is not chosen and the cell one more row below is occupied, enemies step diagonally down in the same movement step without entering falling state.
- If the early jump is not chosen and the drop is deeper than one cell, enemies choose between falling over the edge with chance 1/4, turning back with chance 1/4, and jumping with chance 1/2.

## Collision

- Enemy collision applies 32 damage through the standard `hero.decrease_health` armor and health path.

## Drawing

- All enemies share one flashing color value, `enemies.enemy_flash_color`.
- The shared enemy flash color is selected from `enemies.enemy_flash_colors` through the common draw color switch.
- Enemy drawing is connected to the main draw pass through `enemies.draw_enemies`.
- Enemy drawing checks the room-init online status before painting each enemy.
- Enemies are drawn before the hero, so the existing hero draw can stay visually on top when positions overlap.
