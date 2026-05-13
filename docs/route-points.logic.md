# Route Point Logic

This file tracks the current authored route graph and gameplay rewires. The `route_point` data layout and generic link selection rules live in [Route Points Domain](waypoints.md).

## Cross-Room Route

- The route starts in room `0,0` at `(4,9)`, continues through `(12,10)`, `(20,9)`, and the right edge `(31,5)`.
- Room `0,0` also has the lower return segment `(20,16) -> (31,5)`.
- Room `1,0` continues from `(0,5)` through `(10,3)`, `(20,13)`, and the right exit `(31,7)`.
- Room `2,0` continues the default top path through `(0,7)`, `(8,7)`, `(17,7)`, and the right exit `(31,7)` to room `3,0`.
- Room `3,0` continues from the room `2,0` right exit with a gravity-driven vertical fall route `(0,7) -> (2,21)`.
- When the `3,0` fall reaches the bottom edge, the enemy moves offline into room `3,1` at the top edge `(2,0)`.

## Room 2,0 Roof

- `route_2_0_top_mid` at `(17,7)` is the roof rewire point.
- By default, `route_2_0_top_mid.bottom_right_point_ptr` leads to `route_2_0_top_right` at `(31,7)`.
- Applying the crowbar at `(21,12)` breaks the roof and calls `logic.room_2_0.activate_roof_route`, which rewires `route_2_0_top_mid.bottom_right_point_ptr` to `route_2_0_basement_entry`.
- The roof rewire is not an alternative branch; after the crowbar it is the direct bottom-right route.
- Room `2,0` has two route points at `(21,12)`.
- `route_2_0_basement_entry` is a normal entry from the broken roof and continues toward `(15,16)`.
- `route_2_0_basement_jump` is a separate `jump_right` point for returning from the lower path toward `route_2_0_top_right`.
- The lower path is `(21,12 normal entry) -> (15,16) -> (6,16) -> (21,12 jump right) -> (31,7)`.

## Room 1,1 Glass

- Room `1,1` left edge `route_1_1_left_entry` and room `0,1` right edge `route_0_1_right_entry` are paired exit points.
- Room `1,1` right edge `route_1_1_right_entry` and room `2,1` left edge `route_2_1_left_entry` are paired exit points.
- Room `1,1` left approach points are `(0,19) -> (7,19) -> (13,19)`.
- Room `1,1` starts with the glass route locked: `route_1_1_left_inner` points back to `route_1_1_left_mid`, and `route_1_1_right_mid` points back to `route_1_1_right_entry`.
- The glass-edge points are `route_1_1_left_jump_right` at `(18,19)` and `route_1_1_right_jump_left` at `(20,19)`.
- Applying the stone in room `1,1` activates `logic.room_1_1.activate_glass_route`, rewiring `route_1_1_left_inner.top_left_point_ptr` into the right-jump point and `route_1_1_right_mid.top_left_point_ptr` into the left-jump point.
- After the stone route activation, `route_1_1_left_inner.bottom_right_point_ptr` still points to `route_1_1_left_mid`, so enemies arriving there from `route_1_1_right_jump_left` use the fallback to continue left instead of jumping back right.
- After the stone route activation, room `1,1` can route through the glass area in both directions using the paired jump points.

## Room 2,1 Elevator Test

- Room `2,1` can show the stopped `elevator_2_3` platform from below; its top sits at local `y=20`, so riders stand at `y=19`.
- The debug route enemy currently starts at `route_2_1_right_entry` `(31,19)` and targets `route_2_1_right_mid`, so it moves right-to-left first.
- Room `1,1` right entry `(31,19)` and room `2,1` left entry `(0,19)` are paired exit points.
- Room `2,1` right entry `(31,19)` and room `3,1` left exit `(0,19)` are paired exit points.
- The right and left route segments are connected through the elevator lane as `(31,19) -> (24,19) -> (20,19) -> (7,19) -> (0,19)`.

## Room 3,1 Split

- Room `3,1` routes from the top edge `(2,0)` down through `(2,7)`, then shifts to the clear column `(3,8) -> (3,14) -> (3,19)` before entering the bottom split at `(7,19)`.
- `route_3_1_bottom_fork.alternative_point_ptr` is enabled and points upward, so the split can choose the right branch or the upward branch 50/50.
- The upward split branch uses the aligned vertical points `(7,14)` and `(7,7)`, then returns down through the same vertical lane.
- The left edge of the floor break has only `route_3_1_bottom_jump_right` at `(12,19)`, so enemies reaching that edge start a right jump instead of falling into the break.
- The right edge of the floor break has `route_3_1_right_jump_left` at `(14,19)` for the return jump.
- The two break-edge jump points are not linked to each other directly. Each jump targets a normal route point on the opposite side, so route selection can resume before another jump is considered.
- The right floor route continues through `(20,19)` and `(26,19)` before the right edge exit at `(31,19)`.

## Room 4,1 Generator Route

- Room `4,1` exits from its right edge into the room `5,1` bottom route.
- By default, `route_4_1_left_mid` at `(8,19)` continues directly through `(16,19)` to the right route.
- Applying the screwdriver calls `logic.room_4_1.activate_air_route`, enabling `route_4_1_left_mid.alternative_point_ptr = route_4_1_air_low`.
- The `4,1` air route climbs at `x=16`, returns to `(16,19)`, and uses a `jump_right` point to cross the blocked floor gap toward `(23,19)`.

## Rooms 5,1 Through 6,3

- Room `5,1` has a bottom route from the left side to the right exit into room `6,1`.
- Room `5,1` has no screwdriver-unlocked route branch.
- Room `5,1` exits to the left edge entry `(0,19)` in room `6,1`.
- Room `5,1` has a separate right-to-left closed-door return lane from `route_5_1_red_door_entry` to `route_5_1_red_door_exit`; it is used only by the closed red door path from room `6,1`.
- Room `6,1` uses jump points on the left side to climb toward the red door: `(0,19) -> (4,17) -> (5,14) -> (1,12) -> (8,11)`.
- Before the red card is applied, `route_6_1_red_door_left` points down-left to `route_6_1_red_door_fall` at `(1,19)`, then to the dedicated `route_6_1_red_door_exit` at `(0,19)`, so enemies fall from the closed door area and leave toward the separate room `5,1` return lane.
- Applying the red card calls `logic.room_6_1.activate_red_door_route` and `logic.room_6_2.activate_shaft_up_entry_route`, enabling the door route from `(8,11)` to `(13,11)` and unlocking the upward entry from room `6,2` into `route_6_1_shaft_bottom_entry`.
- After the red card, the right half of room `6,1` routes down through `(13,11) -> (15,16) -> (15,21)` and up through `(16,21) -> (16,16) -> (16,11) -> (13,11)`.
- The elevator shaft is the left shaft in rooms `6,1`, `6,2`, and `6,3`.
- The default downward route uses the left elevator shaft lane at `x=15` in rooms `6,1` and `6,2`, then enters room `6,3` at `(15,0)`.
- Room `6,3` routes around the left elevator shaft obstruction through `(16,7) -> (15,14) -> (13,17)` and joins the lower patrol at `(19,19)`.
- Room `6,3` has a bottom patrol loop.
- By default, `route_6_3_bottom_left` at `(19,19)` keeps the upward elevator shaft disabled.
- Applying the screwdriver calls `logic.room_6_3.activate_shaft_up_route`, enabling `route_6_3_bottom_left.alternative_point_ptr = route_6_3_shaft_up_floor`.
- The repaired upward route uses a separate left elevator shaft lane through `(19,17) -> (13,14) -> (15,13) -> (16,7)` in room `6,3`, then `x=16` through room `6,2`; entry into room `6,1` is gated by the red card.

## Spacing Notes

- Rooms `0,1`, `1,1`, `2,1`, `4,1`, and `4,2` have route points spaced around 5-7 cells where room geometry allows it.
- Rooms with walls or pits splitting the floor use separate local route segments instead of linking through blocked geometry.
