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
- Enemy screen status is refreshed during room initialization and can be forced offline by route exits.

## State

- Enemy state recalculation is controlled by an enemy-local state timer.
- Enemy state timer delay is stored per enemy.
- Enemy state recalculation chooses standing with chance 1/4 and walking with chance 3/4.
- When entering the walking state, an enemy randomly chooses left or right direction.
- Enemy random movement decisions mix the low bit of the Z80 `R` register into the enemy random sequence.
- Enemy jump state uses an enemy-local jump step and phase-delay timer.
- Route-following is a separate behavior model from free roaming; route followers do not use random standing/walking choices while following a route.

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
- Route-following enemies return to route-following behavior after fall or jump physics while they still have a route target.
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
- Enemy drawing checks the current screen status before painting each enemy.
- Enemies are drawn before the hero, so the existing hero draw can stay visually on top when positions overlap.
- Temporary debug drawing colors standing enemies yellow, free-moving enemies with the existing red flash, and route-following enemies green.
- Temporary route-point debug drawing paints current-room route points black.

## Route Points

- Route following is a path-based enemy model that exists beside free roaming instead of replacing it.
- Switching rules between free roaming and route following are not decided yet; the current work only proves the route mechanism itself.
- A route-following enemy remembers the last point it reached and the point it is currently trying to reach.
- A route-following enemy initially chooses a nearby route point; during the first test route this start is fixed by hand.
- Route points belong to rooms and have room-local coordinates.
- Route points are authored so that a route-following enemy can physically reach the next target and should not skip over it.
- A route point can be normal, left-jump-starting, right-jump-starting, or a room exit.
- Normal points continue ordinary route movement.
- Jump-starting points mean the next transition should begin with a jump in the point's authored direction.
- Exit points sit on a screen edge and lead to an entry point in a neighboring room.
- Each route point has a main continuation and an alternative continuation.
- A point without a fork uses the same continuation for both choices.
- A fork uses different main and alternative continuations.
- A dead end points both continuations back to the previous point.
- A room-exit point points both continuations to the matching entry point in the neighboring room.
- After reaching a point, an enemy normally chooses between the point's main and alternative continuation.
- Route movement can occasionally turn back to the previously reached point; the probability should stay low.
- Correctly authored point links make normal points, forks, dead ends, and exits work without special-case route decisions.
- Online route followers move physically toward the current target and use the regular enemy walking, obstacle, falling, and jump behavior while doing so.
- Online route followers start a left or right jump immediately after reaching a `route_point_jump_left` or `route_point_jump_right` point.
- Online route followers step diagonally down on a one-cell ledge and fall from deeper drops; they do not choose jumping or turning back at that ledge.
- Online route followers treat a route point as reached only when the enemy's room-local position matches the point.
- After reaching an exit point, an online route follower moves to the linked entry point, takes that point's room and position, marks its old cell for restore, and becomes offline immediately.
- Planned offline route followers advance along the same route, but without physics: they are treated as reaching the current target, move directly to that point's room and position, then choose the next target.
- The first room `0,0` test route is `(4,9)`, `(12,10)`, `(20,9)`, `(31,9)`, `(20,18)`, `(31,9)`.
- In the first room `0,0` test route, the fifth point descends to the basement end under the right slope and the sixth returns to the right screen edge.
- The current cross-room test route exits from room `0,0` at `(31,9)` to the room `1,0` entry point `(0,9)`.
- Current first-stage implementation moves online route followers horizontally toward the target and relies on normal enemy physics for climbs, falls, and jumps.
- Current first-stage implementation uses only the main continuation, does not yet choose alternatives, does not yet do low-probability turnbacks, and does not yet simulate offline route movement.
