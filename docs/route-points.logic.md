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

## Room 1,0 Stairs

- Room `1,0` keeps its left-to-right route connected as `(0,5) -> (10,3) -> (20,13) -> (31,7)`.
- The lower right-to-left route extends into the house basement as `(20,13) -> (14,13) -> (8,13) -> (4,17) -> (1,18)`, then turns back from the left wall.
- The unfolded stairs enable an optional upward jump chain from `route_1_0_lower_walk` through `(19,12 jump_left) -> (15,9 jump_left) -> (12,7 jump_left)` back to `route_1_0_arch`.
- The stair jump points exist in the route table, but they are not connected to the main route until `logic.room_1_0.activate_stairs_route` sets `route_1_0_lower_walk.alternative_point_ptr` to `route_1_0_stairs_lower_jump_left`.
- The debug stairs key is `debug.initial_stairs_unfolded`; when enabled it calls `logic.stairs.apply_effect`, so the final room art and enemy route rewire both start already applied.
- No current route test enemy starts in room `1,0`; the stairs branch remains reachable from `route_1_0_lower_walk` when an enemy enters the lower right-to-left route.

## Room 0,2 Water Loop

- Room `0,2` starts from the top entry `(10,0)`, which is paired with room `0,1` bottom entry `(10,21)`.
- The room `0,2` water route travels around the air-bubble sky pockets through `(4,3) -> (3,11) -> (11,18) -> (26,17) -> (27,10) -> (26,4) -> (5,3) -> (6,0)`.
- Room `0,1` uses `route_0_1_bottom_entry` as the only right-bank descent point; enemies coming from `route_0_1_left_mid` dive from there into room `0,2`.
- Room `0,2` uses `route_0_2_upper_left` at `(4,3)` for the incoming descent and `route_0_2_top_exit_approach` at `(5,3)` to approach the upward exit while staying in water.
- `route_0_2_top_exit` at `(6,0)` returns from `route_0_2_top_exit_approach` to `route_0_1_water_return_entry`.
- `route_0_1_water_return_entry` receives the enemy at `(6,21)`, then routes up to `route_0_1_water_jump_right` at the water surface `(6,20)`.
- `route_0_1_water_jump_right` starts a right jump after surfacing from `0,2`, and targets `route_0_1_mid` on the dry floor so the enemy continues right instead of diving again.
- The `0,2` loop depends on enemy swimming: route followers swim vertically while submerged instead of waiting for jumps or elevators.

## Room 1,2 Teleports

- Room `1,2` routes enemies from `route_1_2_right_teleport` at the right teleport landing `(28,9)` into water at `(25,11)`, then left through `(19,13) -> (13,13) -> (7,13)`.
- `enemy_4` starts in room `1,2` at `route_1_2_water_mid` and initially targets `route_1_2_water_mid_right`.
- Along the underwater chain in room `1,2`, `topLeft`/`L` points leftward and `bottomRight`/`R` points back rightward.
- `route_1_2_right_teleport` is an exit point paired with `route_5_2_gap_exit`: arrivals from `5,2` continue left into water, and returns from water exit back to `5,2`.
- The room `1,2` underwater route surfaces at `(5,9)` and reaches `route_1_2_left_teleport` at the left teleport `(2,9)`, which exits to `route_0_1_teleport_right`.
- `route_1_2_left_teleport` uses `topLeft`/`L` for the return toward `route_1_2_water_left_surface` and `bottomRight`/`R` for the room `0,1` teleport handoff.
- `route_0_1_teleport_right` sits to the right of the room `0,1` teleport at `(19,9)` and routes to `route_0_1_right_mid` at `(25,19)`, closing the teleport route back into the room `0,1` floor route.

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

## Rooms 2,1 Through 2,3 Elevator Repair Route

- Room `2,1` can show the stopped `elevator_2_3` platform from below; its top sits at local `y=20`, so riders stand at `y=19`.
- Room `1,1` right entry `(31,19)` and room `2,1` left entry `(0,19)` are paired entry points.
- Room `2,1` right entry `(31,19)` and room `3,1` left entry `(0,19)` are paired entry points.
- Room `2,1` upper-right cave entry `(31,7)` and room `3,1` upper-left ledge entry `(0,7)` are paired entry points.
- The upper-right cave in room `2,1` is a dead end: the entry `(31,7)` walks left to `route_2_1_cave_wall` `(24,7)`, the wall point returns to the entry, and the entry then exits back to room `3,1`.
- Before the toolkit repair, the right and left route segments are connected through the stopped elevator lane as `(31,19) -> (24,19) -> (20,19 normal) -> (7,19) -> (0,19)`.
- `route_2_1_elevator_jump_left` keeps its historical label at `(20,19)`, but it is a normal route point rather than a jump point.
- Applying the toolkit in room `2,1` calls `logic.room_2_1.activate_elevator_route`, starts `elevator_2_3` downward, rewires `route_2_1_left_mid.bottom_right_point_ptr` to `route_2_1_left_fall_exit`, and rewires `route_2_1_right_mid.top_left_point_ptr` to `route_2_1_right_fall_exit`.
- The repaired left descent uses shaft edge `x=8`: `route_2_1_left_fall_exit` `(8,21)` -> `route_2_2_left_top_entry` `(8,0)` -> `route_2_2_left_bottom_exit` `(8,21)` -> `route_2_3_left_top_entry` `(8,0)` -> `route_2_3_left_fall_landing` `(8,19)`.
- The repaired right descent mirrors it on shaft edge `x=19`: `route_2_1_right_fall_exit` `(19,21)` -> `route_2_2_right_top_entry` `(19,0)` -> `route_2_2_right_bottom_exit` `(19,21)` -> `route_2_3_right_top_entry` `(19,0)` -> `route_2_3_right_fall_landing` `(19,19)`.
- The bottom wait points sit one cell inside the descent lanes so they do not overlap the fall path: left lane waits at `route_2_3_left_lift_wait` `(9,19)`, and right lane waits at `route_2_3_right_lift_wait` `(18,19)`.
- `route_2_3_left_fall_landing` goes right to `route_2_3_right_lift_wait`, and `route_2_3_right_fall_landing` goes left to `route_2_3_left_lift_wait`.
- The left-to-right chain is `route_2_3_left_fall_landing` -> `route_2_3_right_lift_wait` -> `route_2_3_right_lift_top_exit`; the right lift-top exit is the wait point's `bottomRight` link.
- The mirrored right-to-left chain is `route_2_3_right_fall_landing` -> `route_2_3_left_lift_wait` -> `route_2_3_left_lift_top_exit`; the left lift-top exit is the wait point's `topLeft` link.
- Neither wait point links to the fall landing on the same side.
- Every point on the right lift-up chain keeps the point below in `topLeft` and the next point above in `bottomRight`, preserving the enemy's rightward route direction through rooms `2,3`, `2,2`, and `2,1`.
- Every point on the left lift-up chain is mirrored: the next point above stays in `topLeft` and the point below stays in `bottomRight`, preserving leftward route direction through the shaft.
- In room `2,2`, `elevator_2_3` is not drawn while its global top is below the room bottom. Lift handoff entries use local `y=21`; the enemy floor check now tests for an elevator below the lower screen edge before treating that edge as a fall.
- After a lift route exit hands an enemy from room `2,3` into room `2,2`, the enemy is forced offline. If room `2,2` is not active, offline route steps can still advance it upward through one point per offline route timer, without elevator physics.
- The left lift-up route uses `route_2_3_left_lift_top_exit` at `(9,0)`, then room `2,2` at `(9,21)` -> `(9,10)` -> `(9,0)` -> room `2,1` at `(9,21)` -> `route_2_1_left_elevator_top_jump_left` `(9,19)`.
- The mirrored right lift-up route uses `route_2_3_right_lift_wait` `(18,19)` -> `route_2_3_right_lift_top_exit` `(18,0)`, then room `2,2` at `(18,21)` -> `(18,10)` -> `(18,0)` -> room `2,1` at `(18,21)` -> `route_2_1_right_elevator_top_jump_right` `(18,19)`.
- At the top stop, `route_2_1_left_elevator_top_jump_left` jumps left to `route_2_1_left_entry`, and `route_2_1_right_elevator_top_jump_right` jumps right to `route_2_1_right_mid`.
- No current enemy starts in room `2,1`; the repaired shaft route remains reachable from the room `1,1` and `3,1` entries.

## Room 3,1 Split

- Room `3,1` routes from the top edge `(2,0)` down to the left ledge fork at `(2,7)`.
- The ledge fork can enter left into room `2,1` through `(0,7)` or drop right toward the ground fork at `(7,19)`.
- The ground fork can enter left into room `2,1` through `(0,19)` or continue right to the pre-hole fork at `(11,19)`.
- The pre-hole fork can continue to the right-jump point at `(12,19)` or branch left to `route_3_1_elevator_wait` at `(4,19)`.
- `route_3_1_elevator_wait` targets `route_3_1_elevator_top_jump_left` at `(4,7)` and waits for elevator motion instead of flying upward.
- `route_3_1_elevator_top_jump_left` starts the left dismount jump toward the upper-left ledge entry.
- `route_3_1_left_entry` can alternatively branch to the right elevator wait point `route_3_1_elevator_wait_right` at `(5,19)`, which targets the upper-room lift entry while the physical elevator carries the enemy through the top screen edge.
- `elevator_3_1` starts at `cur_height=0`, placing its platform top at room `3,1` local `y=20`; its `end_height=34` carries riders to room `3,0` local `y=7`.
- The right elevator branch currently enters room `3,0` through the temporary lower-edge test point `route_3_0_lift_bottom_entry` `(5,21)`, then keeps riding to `route_3_0_lift_jump_left` `(5,7)` for a left dismount toward `route_3_0_left_entry`.
- The right side of the floor break has only `route_3_1_right_jump_left` at `(14,19)` and `route_3_1_right_entry` at `(31,19)`.
- The ledge and ground forks duplicate their left entries in `alternative_point_ptr` so one-way arrivals from a fall or drop still choose between the left entry and the right continuation 50/50 without adding filler points.

## Room 4,1 Generator Route

- Room `4,1` left edge `route_4_1_left_entry` and room `3,1` right edge `route_3_1_right_entry` are paired entry points.
- Room `4,1` right edge `route_4_1_right_entry` and room `5,1` left edge `route_5_1_left_entry` are paired entry points.
- No current enemy starts in room `4,1`; the generator route remains reachable from the room `3,1` and `5,1` entries.
- `route_4_1_left_mid` at `(8,19)` continues through the hatch fork at `(15,19)` to `route_4_1_left_jump` at `(16,19)`.
- `route_4_1_left_jump` is a `jump_right` point for crossing the blocked floor gap toward `(23,19)`.
- Applying the key opens the hatch and calls `logic.room_4_1.activate_hatch_route`, enabling `route_4_1_hatch_fork.alternative_point_ptr = route_4_1_hatch_drop`.
- `route_4_1_hatch_drop` at `(18,21)` exits down into room `4,2` through `route_4_2_top_entry` at `(18,0)`.
- Applying the screwdriver calls `logic.room_4_1.activate_elevator_wait_route`, enabling `route_4_1_right_mid.alternative_point_ptr = route_4_1_elevator_wait`.
- `route_4_1_elevator_wait` at `(25,19)` waits on the right hatch-screen elevator lane, targets `route_4_1_elevator_top` at `(25,6)`, then walks right to `route_4_1_upper_right_exit`.
- The upper right exit in room `4,1` is paired with `route_5_1_upper_left_entry` at `(0,6)`, so the repaired elevator branch leads into the upper route of room `5,1`.
- Returning left from room `5,1` uses `route_4_1_elevator_drop_landing` at `(28,19)`, on the right edge of `elevator_4_1_3`, instead of the elevator wait point, so the descent route is separate from the screwdriver-enabled ascent wait.
- The old upward air points are removed; the hatch-screen branch reaches its high target by elevator wait behavior instead of route-driven vertical climbing.

## Room 4,2 Slope And Exits

- Room `4,2` top entry `route_4_2_top_entry` receives enemies from the opened hatch in room `4,1`, then gravity carries them to `route_4_2_top_landing` at `(18,7)`.
- The slope path reaches the right-edge fork as `(18,7) -> (24,13) -> (31,14)`; the down branch continues through `(23,15) -> (16,18) -> (19,21)`.
- `route_4_2_slope_right` at `(31,14)` is an exit-capable fork: its direct path exits right to `route_5_2_left_entry`, its left return goes through `route_4_2_slope_jump_left` at `(30,14)`, and its alternative branch continues down the slope.
- `route_4_2_slope_jump_left` is a right-side `jump_left` point that jumps back to `route_4_2_slope_mid` at `(24,13)`.
- No current enemy starts in room `3,2`; its right route remains reachable from room `4,2`.
- Room `4,2` right edge `route_4_2_slope_right` and room `5,2` left edge `route_5_2_left_entry` are paired entry points at local `y=14`.
- `route_4_2_lower_right` at `(23,15)` uses `topLeft` for the descent toward `route_4_2_lower_left` and `bottomRight` for returning up toward `route_4_2_slope_right`.
- `route_4_2_lower_left` at `(16,18)` uses `topLeft` for the left branch through `(12,15) -> (7,18 jump right) -> (6,18) -> (0,16)`, `bottomRight` for the return toward `route_4_2_lower_right`, and `alternative` for the bottom exit branch.
- `route_4_2_bottom_exit` at `(19,21)` exits down to `route_4_3_top_entry`, which descends the room `4,3` steps to `route_4_3_after_stairs` at `(25,7)`.
- Room `3,2` has a right-side route from `(31,16)` through `(29,15) -> (24,13) -> (17,11)`, where `route_3_2_teleport_exit` hands the enemy to room `0,3`.
- Room `0,3` receives that teleport route at `route_0_3_teleport` `(30,19)`, climbs the one-cell step from `(23,19)` to `(18,18)`, then descends left as `(18,18 jump left) -> (12,16) -> (7,16 normal) -> (1,19 normal dead end)`.
- The room `0,3` return route uses the separate lower jump `(5,19 jump right) -> (12,16)`, then walks through `(18,18 normal)` back to `(23,19)` so the descent jump points are not used in reverse.
- The room `3,2` right edge remains paired with room `4,2` through `route_3_2_right_entry` and `route_4_2_left_entry`; the new teleport branch extends the left side of `route_3_2_right_inner` without removing that edge pair.
- Room `5,2` has a left-floor fork at `route_5_2_floor_right` `(16,14)`: the direct path jumps right across the gap from `(22,14)`, and the alternative path falls into the gap.
- Returning from `route_5_2_gap_jump_left` to `route_5_2_floor_right` preserves the enemy's leftward direction and selects `route_5_2_floor_mid` as the direct continuation; the optional gap-exit branch remains eligible for its normal 50/50 selection.
- `route_5_2_gap_exit` at the room `5,2` teleport `(23,17)` exits to `route_1_2_right_teleport` at `(28,9)`, one cell left of the room `1,2` right teleport destination `(29,9)`.
- `route_5_2_gap_exit` uses `route_5_2_gap_exit_jump_left` at `(24,17)` as its same-room return from the pit; that jump point targets `route_5_2_floor_mid` to bypass the optional gap-exit branch on `route_5_2_floor_right`.
- The room `5,2` right side uses `route_5_2_gap_jump_left` at `(25,14)` to jump left across the gap.

## Rooms 4,3 And 5,3 Water Elevator Loop

- Room `4,3` receives the downward route from room `4,2` at `route_4_3_top_entry` `(19,0)`, then follows the shifted steps rightward to `route_4_3_after_stairs` `(25,7)`.
- `route_4_3_after_stairs` keeps `topLeft` toward the left water drop and `bottomRight` toward the upper-right exit into room `5,3`.
- The upper route exits room `4,3` through `route_4_3_upper_right_exit` `(31,7)` to `route_5_3_upper_left_entry` `(0,7)`.
- `route_4_3_upper_right_exit` also has the alternative `route_4_3_upper_jump_left` at `(29,7)`, two cells left of the exit, which starts a left jump and links back to `route_4_3_top_entry`.
- Room `5,3` upper route walks along `y=7` through `(0,7) -> (8,7) -> (16,7) -> (24,7) -> (29,7)`, where the right-wall point is a dead end and reverses back to room `4,3`.
- Returning from room `5,3` upper route to `route_4_3_upper_right_exit` walks left to `route_4_3_after_stairs`, then continues through the left water branch.
- The left water branch in room `4,3` drops from the upper route into `route_4_3_water_drop_entry` `(17,9)` without using the elevator, then swims right through `(23,10) -> (29,10) -> (31,10)`.
- `route_4_3_lower_water_exit` `(31,10)` exits to the lower water entry `route_5_3_lower_left_entry` `(0,10)`.
- Room `5,3` lower water route swims through `(0,10) -> (8,10) -> (16,10) -> (24,10)`, then the terminal point reverses back to room `4,3`.
- Returning from room `5,3` lower water at `route_4_3_lower_water_exit` targets `route_4_3_elevator_wait` `(17,20)` on the bottom elevator lane.
- `route_4_3_elevator_wait` targets `route_4_3_elevator_top_jump_right` `(17,7)`, which starts a local right jump toward `route_4_3_after_stairs` and rejoins the upper route.
- Do not use a long right jump to cross room `4,3` and climb upward in one arc: enemy jumps are fixed short arcs, and upward movement outside water must come from a jump, elevator, or room geometry.

## Rooms 5,1 Through 6,3

- Room `5,1` has a bottom route from the left side to the right entry into room `6,1`.
- Room `5,1` upper-left entry `(0,6)` and room `4,1` upper-right exit `(31,6)` are paired entry points.
- The room `5,1` upper route covers the upper floor at `y=6`, jumps through the gap from `(19,6 jump_right)` to `(26,4)`, jumps left across the high gap from `(22,4 jump_left)` to `(15,4)`, jumps up from `(3,4 jump_right)` to `(10,2)`, walks through `(22,2)`, then jumps from `(25,2 jump_right)` to the top exit `(29,0)`.
- The leftward return through room `5,1` uses normal return points instead of jump points: `(29,0)` -> `(26,2 normal)` -> `(22,2 normal)` -> `(16,2)` -> `(10,2)` -> `(3,4 normal)` -> `(15,4)` -> `(20,6)` -> `(16,6)` -> `(8,6)` -> `(0,6)`.
- `route_5_1_top_exit` exits into room `5,0` at `route_5_0_bottom_entry` `(27,20)`. The receiving point is one row above the bottom edge so the enemy stands on room floor instead of immediately falling back to `5,1`.
- Room `5,0` routes left across its bottom floor through `(27,20) -> (20,20) -> (13,20) -> (6,20) -> (0,20)`.
- Room `5,0` left exit `(0,20)` and room `4,0` right entry `(31,20)` are paired entry points; room `4,0` then covers the bottom floor through `(31,20) -> (24,20) -> (16,20) -> (8,20)`.
- Room `5,1` right edge `route_5_1_right_entry` at `(31,19)` and room `6,1` left edge `route_6_1_left_entry` at `(0,19)` are paired entry points.
- Room `5,1` no longer has a separate red-door return lane; its points stay on the main `Y=1` chain.
- Room `6,1` uses one shared left entry/exit point at `(0,19)`, then starts its climb at `route_6_1_left_jump_low` `(1,19)` and reaches `route_6_1_red_door_left` at `(8,11)`.
- The right-side fall lands at `route_6_1_red_door_fall` `(2,20)`, which routes back to the shared entry/exit point; the enemy then exits to `route_5_1_right_entry`.
- Offline same-room steps update horizontal direction before selecting the next link, so the abstract `(2,20) -> (0,19)` return selects `5,1` at the shared entry/exit instead of starting another climb.
- Room `6,1` keeps the old shaft route removed; enemies approach the red door but do not continue into the shaft or behind the door.
- Applying the red card opens the door visually but no longer rewires enemy route points; enemies do not go behind the red door.
- Rooms `6,2` and `6,3` currently have no enemy route points.
- Applying the screwdriver starts generator/elevator effects and unlocks the room `4,1` right-elevator wait branch, but it does not unlock an enemy route branch in room `6,3`.

## Spacing Notes

- Rooms `0,1`, `1,1`, `2,1`, `4,1`, and `4,2` have route points spaced around 5-7 cells where room geometry allows it.
- Rooms with walls or pits splitting the floor use separate local route segments instead of linking through blocked geometry.
