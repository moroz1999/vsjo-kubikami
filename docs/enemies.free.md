# Enemies Free Movement

## State

- Enemy state recalculation is controlled by an enemy-local state timer.
- Enemy state timer delay is stored per enemy.
- Enemy state recalculation chooses standing with chance 1/4 and walking with chance 3/4.
- When entering the walking state, an enemy randomly chooses left or right direction.
- Enemy random movement decisions mix the low bit of the Z80 `R` register into the enemy random sequence.
- Standing enemies do not move.
- Walking enemies move horizontally in their selected direction.
- While occupying a water cell, walking enemies keep their horizontal movement behavior but skip dry ledge decisions.
- Swimming gravity sinks walking enemies after `swim_delay` frames instead of switching them into the normal falling state.

## Walking

- Moving enemies check the next room cell through the same `objects.check_coords` path used by the hero.
- Enemies move only when the next cell is reported as `objects.b_empty`.
- When the next cell in the walking direction is blocked, enemies check the up-side cell in that direction.
- If the up-side cell is empty, enemies climb there diagonally in the same movement step.
- If the up-side cell is also blocked, enemies reverse direction immediately.
- When the next cell is empty, enemies also check the down-side cell in the movement direction.
- When the down-side cell is empty, enemies first have a 1/4 chance to start a directional jump before checking the drop depth.
- If the early jump is not chosen and the cell one more row below is occupied, enemies step diagonally down in the same movement step without entering falling state.
- If the early jump is not chosen and the drop is deeper than one cell, enemies choose between falling over the edge with chance 1/4, turning back with chance 1/4, and jumping with chance 1/2.

## Falling And Jumping

- Enemies enter falling state when the cell below them is empty.
- Falling enemies move downward until the cell below is no longer empty.
- After landing, free-moving falling enemies continue walking in the same direction they had before falling.
- Falling speed is shared by all enemies.
- Enemy jump state uses an enemy-local jump step and phase-delay timer.
- Jumping enemies ignore fall-state recalculation and state-timer recalculation until the jump arc ends.
- Enemy jumps use the same eight-step arc shape as the hero, but apply enemy movement rules and stay inside the current room.
- After an enemy jump arc ends, normal falling checks resume on the next enemy update.
