# Route Point Logic

This file tracks the current authored route graph and gameplay rewires. The `route_point` data layout and generic link selection rules live in [Route Points Domain](waypoints.md).

## Cross-Room Route

- The route starts in room `0,0` at `(4,9)`, continues through `(12,10)`, `(20,9)`, `(27,9)`, and the right edge `(31,5)`.
- Room `0,0` also has a branch from `route_0_0_right` down through `(20,16)` before returning to the right edge `(31,5)`.
- Room `0,1` connects its left-side route to the right-bank descent entry `(10,21)`, paired with room `0,2` top entry `(10,0)`.
- Room `0,2` returns upward through `(6,0)`, paired with room `0,1` water return entry `(6,21)`.
- Room `1,0` continues from `(0,5)` through `(10,3)`, `(20,13)`, and the right entry `(31,7)`.
- Room `2,0` continues the default top path through `(0,7)`, `(8,7)`, `(17,7)`, and the top-right entry `(31,7)` to room `3,0`.
- Room `3,0` continues from the room `2,0` top-right entry with a gravity-driven vertical fall route `(0,7) -> (2,21)`.
- When the `3,0` fall reaches the bottom edge, the enemy moves offline into room `3,1` at the top edge `(2,0)`.

## Room 0,2 Water Loop

- Room `0,2` starts from the top entry `(10,0)`, which is paired with room `0,1` bottom entry `(10,21)`.
- The room `0,2` water route travels around the air-bubble sky pockets through `(4,3) -> (3,11) -> (11,18) -> (26,17) -> (27,10) -> (26,4) -> (5,3) -> (6,0)`.
- Room `0,1` uses `route_0_1_bottom_entry` as the only right-bank descent point; enemies coming from `route_0_1_left_mid` dive from there into room `0,2`.
- Room `0,2` uses `route_0_2_upper_left` at `(4,3)` for the incoming descent and `route_0_2_top_exit_approach` at `(5,3)` to approach the upward exit while staying in water.
- `route_0_2_top_exit` at `(6,0)` returns from `route_0_2_top_exit_approach` to `route_0_1_water_return_entry`.
- `route_0_1_water_return_entry` receives the enemy at `(6,21)`, then routes up to `route_0_1_water_jump_right` at the water surface `(6,20)`.
- `route_0_1_water_jump_right` starts a right jump after surfacing from `0,2`, and targets `route_0_1_mid` on the dry floor so the enemy continues right instead of diving again.
- The `0,2` loop depends on enemy swimming: route followers swim vertically while submerged instead of waiting for jumps or elevators.
- The debug route enemy starts at `route_0_1_right_entry` `(31,19)` with that same right entry as its last point and `route_0_1_right_mid` as its target, so reaching `route_0_1_right_mid` selects `route_0_1_mid` instead of turning back.

## Room 1,2 Teleports

- Room `1,2` routes enemies from `route_1_2_right_teleport` at the right teleport landing `(28,9)` into water at `(25,11)`, then left through `(19,13) -> (13,13) -> (7,13)`.
- Along the underwater chain in room `1,2`, `topLeft`/`L` points leftward and `bottomRight`/`R` points back rightward.
- `route_1_2_right_teleport` is an exit point paired with `route_5_2_gap_exit`: arrivals from `5,2` continue left into water, and returns from water exit back to `5,2`.
- The room `1,2` underwater route surfaces at `(5,9)` and reaches `route_1_2_left_teleport` at the left teleport `(2,9)`, which exits to `route_0_1_teleport_right`.
- `route_1_2_left_teleport` uses `topLeft`/`L` for the room `0,1` teleport handoff and `bottomRight`/`R` for the return toward `route_1_2_water_left_surface`.
- `route_0_1_teleport_right` sits to the right of the room `0,1` teleport at `(19,9)` and routes to `route_0_1_right_mid` at `(24,19)`, closing the teleport route back into the room `0,1` floor route.

## Room 2,0 Roof

- `route_2_0_top_mid` at `(17,7)` is the roof rewire point.
- By default, `route_2_0_top_mid.bottom_right_point_ptr` leads to `route_2_0_top_right_entry` at `(31,7)`.
- Applying the crowbar at `(21,12)` breaks the roof and calls `logic.room_2_0.activate_roof_route`, which rewires `route_2_0_top_mid.bottom_right_point_ptr` to `route_2_0_basement_entry`.
- The roof rewire is not an alternative branch; after the crowbar it is the direct bottom-right route.
- Room `2,0` has two route points at `(21,12)`.
- `route_2_0_basement_entry` is a normal entry from the broken roof and continues toward `(15,16)`.
- `route_2_0_basement_mid` at `(15,16)` keeps `topLeft` / viewer `L` toward `route_2_0_basement_end` on the left and `bottomRight` / viewer `R` back toward `route_2_0_basement_entry`.
- `route_2_0_basement_jump` is a separate `jump_right` point for returning from the lower path toward `route_2_0_top_right_entry`.
- The lower path is `(21,12 normal entry) -> (15,16) -> (6,16) -> (21,12 jump right) -> (31,7)`.

## Room 1,1 Glass

- Room `1,1` left edge `route_1_1_left_entry` and room `0,1` right edge `route_0_1_right_entry` are paired entry points.
- Room `1,1` right edge `route_1_1_right_entry` and room `2,1` left edge `route_2_1_left_entry` are paired entry points.
- Room `1,1` left approach points are `(0,19) -> (7,19) -> (13,19)`.
- Room `1,1` starts with the glass route locked: `route_1_1_left_inner` points back to `route_1_1_left_mid`, and `route_1_1_right_mid` points back to `route_1_1_right_entry`.
- The glass-edge points are `route_1_1_left_jump_right` at `(18,19)` and `route_1_1_right_jump_left` at `(20,19)`.
- Applying the stone in room `1,1` activates `logic.room_1_1.activate_glass_route`, rewiring `route_1_1_left_inner.bottom_right_point_ptr` into the right-jump point and `route_1_1_right_mid.top_left_point_ptr` into the left-jump point.
- After the stone route activation, `route_1_1_left_inner.top_left_point_ptr` still points to `route_1_1_left_mid`, so enemies arriving there from `route_1_1_right_jump_left` continue left through the direction-preserving fallback.
- After the stone route activation, room `1,1` can route through the glass area in both directions using the paired jump points.

## Room 2,1 Elevator Test

- Room `2,1` can show the stopped `elevator_2_3` platform from below; its top sits at local `y=20`, so riders stand at `y=19`.
- Room `1,1` right entry `(31,19)` and room `2,1` left entry `(0,19)` are paired entry points.
- Room `2,1` right entry `(31,19)` and room `3,1` left entry `(0,19)` are paired entry points.
- Room `2,1` upper-right cave entry `(31,7)` and room `3,1` upper-left ledge entry `(0,7)` are paired entry points.
- The upper-right cave entry in room `2,1` is currently a terminal paired entry point; both direct links return to room `3,1`.
- The right and left route segments are connected through the elevator lane as `(31,19) -> (24,19) -> (20,19 jump left) -> (7,19) -> (0,19)`.
- `route_2_1_elevator_jump_left` at `(20,19)` is the top dismount jump toward the left route segment.

## Room 3,1 Split

- Room `3,1` routes from the top edge `(2,0)` down to the left ledge fork at `(2,7)`.
- The ledge fork can enter left into room `2,1` through `(0,7)` or drop right toward the ground fork at `(7,19)`.
- The ground fork can enter left into room `2,1` through `(0,19)` or continue right to the pre-hole fork at `(11,19)`.
- The pre-hole fork can continue to the right-jump point at `(12,19)` or branch left to `route_3_1_elevator_wait` at `(4,19)`.
- `route_3_1_elevator_wait` targets `route_3_1_elevator_top_jump_left` at `(4,7)` and waits for elevator motion instead of flying upward.
- `route_3_1_elevator_top_jump_left` starts the left dismount jump toward the upper-left ledge entry.
- The right side of the floor break has only `route_3_1_right_jump_left` at `(14,19)` and `route_3_1_right_entry` at `(31,19)`.
- The ledge and ground forks duplicate their left entries in `alternative_point_ptr` so one-way arrivals from a fall or drop still choose between the left entry and the right continuation 50/50 without adding filler points.

## Room 4,1 Generator Route

- Room `4,1` left edge `route_4_1_left_entry` and room `3,1` right edge `route_3_1_right_entry` are paired entry points.
- Room `4,1` right edge `route_4_1_right_entry` and room `5,1` left edge `route_5_1_left_entry` are paired entry points.
- The debug route enemy starts in room `4,1` at the left edge and targets `route_4_1_left_mid`, so it tests the room from left to right.
- `route_4_1_left_mid` at `(8,19)` continues through the hatch fork at `(15,19)` to `route_4_1_left_jump` at `(16,19)`.
- `route_4_1_left_jump` is a `jump_right` point for crossing the blocked floor gap toward `(23,19)`.
- Applying the key opens the hatch and calls `logic.room_4_1.activate_hatch_route`, enabling `route_4_1_hatch_fork.alternative_point_ptr = route_4_1_hatch_drop`.
- `route_4_1_hatch_drop` at `(18,21)` exits down into room `4,2` through `route_4_2_top_entry` at `(18,0)`.
- Applying the screwdriver calls `logic.room_4_1.activate_elevator_wait_route`, enabling `route_4_1_left_mid.alternative_point_ptr = route_4_1_elevator_wait`.
- `route_4_1_elevator_wait` at `(16,19)` waits on the hatch-screen elevator lane, targets `route_4_1_elevator_top` at `(16,6)`, then returns to `route_4_1_left_jump`.
- The old upward air points are removed; the hatch-screen branch reaches its high target by elevator wait behavior instead of route-driven vertical climbing.

## Room 4,2 Slope And Exits

- Room `4,2` top entry `route_4_2_top_entry` receives enemies from the opened hatch in room `4,1`, then gravity carries them to `route_4_2_top_landing` at `(18,7)`.
- The slope path reaches the right-edge fork as `(18,7) -> (24,13) -> (31,14)`; the down branch continues through `(23,15) -> (16,18) -> (19,21)`.
- `route_4_2_slope_right` at `(31,14)` is an exit-capable fork: its direct path exits right to `route_5_2_left_entry`, and its alternative branch continues down the slope.
- Room `4,2` right edge `route_4_2_slope_right` and room `5,2` left edge `route_5_2_left_entry` are paired entry points at local `y=14`.
- `route_4_2_lower_right` at `(23,15)` uses `topLeft` for the descent toward `route_4_2_lower_left` and `bottomRight` for returning up toward `route_4_2_slope_right`.
- `route_4_2_lower_left` at `(16,18)` uses `topLeft` for the left branch through `(12,15) -> (7,18 jump right) -> (6,18) -> (0,16)`, `bottomRight` for the return toward `route_4_2_lower_right`, and `alternative` for the bottom exit branch.
- `route_4_2_bottom_exit` at `(19,21)` exits down to `route_4_3_top_entry`, which falls to the top platform at `(19,7)`.
- Room `3,2` has a short right-edge receiving route from `(31,16)` toward `(24,13)`.
- A route-following enemy starts at room `5,2` left entry `(0,14)` with `route_4_2_slope_right` as its last point and `route_5_2_floor_mid` as its target, so it begins moving right.
- Room `5,2` has a left-floor fork at `route_5_2_floor_right` `(16,14)`: the direct path jumps right across the gap from `(22,14)`, and the alternative path falls into the gap.
- Returning from `route_5_2_gap_jump_left` or `route_5_2_gap_exit_jump_left` to `route_5_2_floor_right` preserves the enemy's leftward direction and selects `route_5_2_floor_mid` as the direct continuation; the optional gap-exit branch remains eligible for its normal 50/50 selection.
- `route_5_2_gap_exit` at the room `5,2` teleport `(23,17)` exits to `route_1_2_right_teleport` at `(28,9)`, one cell left of the room `1,2` right teleport destination `(29,9)`.
- `route_5_2_gap_exit` uses `route_5_2_gap_exit_jump_left` at `(24,17)` as its same-room return from the pit; that jump point targets `route_5_2_floor_right`.
- The room `5,2` right side uses `route_5_2_gap_jump_left` at `(25,14)` to jump left across the gap.

## Rooms 5,1 Through 6,3

- Room `5,1` has a bottom route from the left side to the right entry into room `6,1`.
- Room `5,1` has no screwdriver-unlocked route branch.
- Room `5,1` right edge `route_5_1_right_entry` and room `6,1` left edge `route_6_1_left_entry` are paired entry points.
- Room `5,1` no longer has a separate red-door return lane; its points stay on the main `Y=1` chain.
- Room `6,1` climbs from `route_6_1_left_entry` through the left ledges to `route_6_1_red_door_left` at `(8,11)`, then falls through `route_6_1_red_door_fall` to `route_6_1_left_return_entry` and exits back to `route_5_1_right_entry`.
- Room `6,1` keeps the old shaft route removed; enemies approach the red door but do not continue into the shaft or behind the door.
- Applying the red card opens the door visually but no longer rewires enemy route points; enemies do not go behind the red door.
- Rooms `6,2` and `6,3` currently have no enemy route points.
- Applying the screwdriver starts generator/elevator effects and unlocks the room `4,1` hatch-screen wait branch, but it does not unlock an enemy route branch in room `6,3`.

## Spacing Notes

- Rooms `0,1`, `1,1`, `2,1`, `4,1`, and `4,2` have route points spaced around 5-7 cells where room geometry allows it.
- Rooms with walls or pits splitting the floor use separate local route segments instead of linking through blocked geometry.
