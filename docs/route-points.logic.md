# Route Point Logic

This file tracks the current authored route graph and gameplay rewires. The `route_point` data layout and generic link selection rules live in [Route Points Domain](waypoints.md).

## Cross-Room Route

- The route starts in room `0,0` at `(4,9)`, continues through `(12,10)`, `(20,9)`, and the right edge `(31,5)`.
- Room `0,0` also has the lower return segment `(20,16) -> (31,5)`.
- Room `1,0` continues from `(0,5)` through `(10,3)`, `(20,13)`, and the right exit `(31,7)`.
- Room `2,0` continues the default top path through `(0,7)`, `(8,7)`, `(17,7)`, and the right exit `(31,7)` to room `3,0`.
- Room `3,0` continues from the room `2,0` right exit with a gravity-driven vertical fall route `(0,7) -> (2,21)`.
- When the `3,0` fall reaches the bottom edge, the enemy moves offline into room `3,1` at `y=0` and targets `(2,7)`.

## Room 2,0 Roof

- `route_2_0_top_mid` at `(17,7)` is the roof rewire point.
- By default, `route_2_0_top_mid.bottom_right_point_ptr` leads to `route_2_0_top_right` at `(31,7)`.
- Applying the crowbar at `(21,12)` breaks the roof and rewires `route_2_0_top_mid.bottom_right_point_ptr` to `route_2_0_basement_entry`.
- The roof rewire is not an alternative branch; after the crowbar it is the direct bottom-right route.
- Room `2,0` has two route points at `(21,12)`.
- `route_2_0_basement_entry` is a normal entry from the broken roof and continues toward `(15,16)`.
- `route_2_0_basement_jump` is a separate `jump_right` point for returning from the lower path toward `route_2_0_top_right`.
- The lower path is `(21,12 normal entry) -> (15,16) -> (6,16) -> (21,12 jump right) -> (31,7)`.

## Room 3,1 Split

- Room `3,1` routes from `(2,7)` down into the bottom split at `(8,19)`.
- `route_3_1_bottom_fork.alternative_point_ptr` is enabled and points left, so the first entry into the split can choose left or right 50/50.
- The left edge of the floor break has only `route_3_1_bottom_jump_right` at `(12,19)`, so enemies reaching that edge start a right jump instead of falling into the break.
- The right edge of the floor break has `route_3_1_right_jump_left` at `(14,19)` for the return jump.
- The two break-edge jump points are not linked to each other directly. Each jump targets a normal route point on the opposite side, so route selection can resume before another jump is considered.

## Room 4,1 Generator Route

- Room `4,1` exits from its right edge into the room `5,1` bottom route.
- By default, `route_4_1_left_mid` at `(8,19)` continues directly through `(16,19)` to the right route.
- Applying the screwdriver enables `route_4_1_left_mid.alternative_point_ptr = route_4_1_air_low`.
- The `4,1` air route climbs at `x=16`, returns to `(16,19)`, and uses a `jump_right` point to cross the blocked floor gap toward `(23,19)`.

## Rooms 5,1 Through 6,3

- Room `5,1` has a bottom route from the left side to the right exit into room `6,1`.
- Room `5,1` has no screwdriver-unlocked route branch.
- Room `5,1` exits to the left elevator shaft lane in room `6,1`, not to the right-side shafts.
- The elevator shaft is the left shaft in rooms `6,1`, `6,2`, and `6,3`.
- The default downward route uses the left elevator shaft lane at `x=15` in rooms `6,1` and `6,2`, then enters room `6,3` at `(15,0)`.
- Room `6,3` routes around the left elevator shaft obstruction through `(16,7) -> (15,14) -> (13,17)` and joins the lower patrol at `(19,19)`.
- Room `6,3` has a bottom patrol loop.
- By default, `route_6_3_bottom_left` at `(19,19)` keeps the upward elevator shaft disabled.
- Applying the screwdriver enables `route_6_3_bottom_left.alternative_point_ptr = route_6_3_shaft_up_floor`.
- The repaired upward route uses a separate left elevator shaft lane through `(19,17) -> (13,14) -> (15,13) -> (16,7)` in room `6,3`, then `x=16` through rooms `6,2` and `6,1`; it does not use the right-side shafts or jump points.

## Spacing Notes

- Rooms `0,1`, `1,1`, `2,1`, `4,1`, and `4,2` have route points spaced around 6-8 cells where room geometry allows it.
- Rooms with walls or pits splitting the floor use separate local route segments instead of linking through blocked geometry.
