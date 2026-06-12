# Items Domain

## Crowbar

- The crowbar is used in room `2,0` when the hero stands at `(21,12)`.
- Using it breaks the roof, starts the room ground animation, and removes the item from the pocket.
- The enemy route effect is `logic.room_2_0.activate_roof_route`: it rewires `route_2_0_top_mid.bottom_right_point_ptr` to the normal `route_2_0_basement_entry`.
- After the rewire, enemies can enter the lower room path through the broken roof route without starting a jump, then use the separate basement jump point to climb back right.

## Toolkit

- The toolkit is used in room `2,1` when the hero stands at `y=19` and `x=8..19`.
- Using it starts `elevator_2_3` downward, calls `logic.room_2_1.activate_elevator_route`, and removes the item from the pocket.
- The enemy route effect breaks the pre-repair route across the stopped elevator: `route_2_1_left_mid.bottom_right_point_ptr` goes to `route_2_1_left_fall_exit`, and `route_2_1_right_mid.top_left_point_ptr` goes to `route_2_1_right_fall_exit`.
- After the rewire, route followers fall down the `2,1 -> 2,2 -> 2,3` shaft lanes and use the bottom wait points to ride the repaired elevator back up.

## Stone

- The stone is used in room `1,1` when the hero stands at `(20,19)`.
- Using it starts the glass break animation, removes the item from the pocket, and calls `logic.room_1_1.activate_glass_route`.
- The enemy route effect enables the two glass-edge jump points, so enemies can cross the room `1,1` glass only after it has been broken.
- Room `1,1` stores the persistent result in `logic.room_1_1.glass_broken`; `on_enter` applies the final broken-glass frame when this flag is set.

## Stairs

- The stairs are used in room `1,0` when the hero stands at `y=13` and `x=12..17`.
- Using them starts the unfolding animation and removes the item from the pocket.
- Room `1,0` stores the persistent result in `logic.room_1_0.stairs_unfolded`; `on_enter` applies the final unfolded-stairs frame when this flag is set.
- The enemy route effect is `logic.room_1_0.activate_stairs_route`: it rewires `route_1_0_lower_walk.top_left_point_ptr` to `route_1_0_stairs_lower_jump_left`, enabling the right-to-left stair jump chain only after the stairs are unfolded.

## Red Card

- The red card is used in room `6,1` when the hero stands at `y=11` and `x=8..12`.
- Using it opens the red door, starts the door animation, removes the item from the pocket, and does not change enemy route points.
- Enemies can climb from `route_6_1_left_entry` to the red-door approach at `(8,11)`, then fall to `route_6_1_left_return_entry` and leave back to `5,1`; they do not go behind the red door after the card is applied.

## Key

- The key is used in room `4,1` when the hero stands at `x=17..22` and `y=16..19`.
- Using it opens the hatch, starts the hatch animation, removes the item from the pocket, and sets `logic.room_4_1.hatch_opened`.
- The enemy route effect is `logic.room_4_1.activate_hatch_route`: it enables `route_4_1_hatch_fork.alternative_point_ptr = route_4_1_hatch_drop`.
- The hatch branch lets route followers drop from room `4,1` into room `4,2` only after the hatch has been opened.

## Screwdriver

- The screwdriver is used in room `6,3` when the hero stands between `x=11` and `x=12` at `y=20`.
- Using it repairs the generator, switches the generator-related room animations on, starts the `6,3` elevator upward, starts the three `4,1` elevators upward, enables the room `4,1` wait branch, and removes the item from the pocket.
- The enemy route effect is `logic.room_4_1.activate_elevator_wait_route`: it enables `route_4_1_left_mid.alternative_point_ptr = route_4_1_elevator_wait`.
- Room `4,1` keeps its normal `jump_right` route across the blocked floor gap, while the optional wait branch rides the hatch-screen elevator instead of using upward air points.
- Room `6,3` has no screwdriver-unlocked enemy route branch.
- Room `5,1` has no screwdriver-unlocked route branch; its bottom route stays connected to room `6,1`.

## Handle

- The handle is used in room `4,0` when the hero stands at `y=20` and `x=1..4`.
- Using it opens the balcony door at `(0,15)` with a four-frame screen animation and sets `logic.room_4_0.handle_used`.
- The final balcony-door patch is passable sky/gray art, so room `4,0` can restore and collide from `rooms.current_room_buf` after the door is open.

## Seeds

- The seeds are used in room `5,0` when the hero stands at `y=14` and `x=18..24`.
- Using them grows a flower above the pot at `(18,8)` with a four-frame screen animation and sets `logic.room_5_0.seeds_grown`.
- The final flower is `8x5`: its gray stem column is passable and reaches each leaf row, while its fewer wide green leaves are solid climb steps.

## Debug Initial Item States

- `debug.apply_initial_item_states` runs once at startup before `rooms.init_current_room`.
- `debug.initial_broken_glass`, `debug.initial_red_door_opened`, `debug.initial_hatch_key_used`, `debug.initial_elevator_repaired`, `debug.initial_generator_started`, and `debug.initial_stairs_unfolded` are compile-time 0/1 flags for item effects that should start already applied.
- Each enabled flag calls the matching item module's `apply_effect` routine, then removes the consumed item from `items.all_items`.
- Item `apply_effect` routines contain only the persistent gameplay effect: room/effect flags, route rewires, elevator states, and animation-state switches. They do not check hero coordinates, start one-shot screen animations, or remove the item from the hero pocket.
- Runtime item `action` routines handle hero-position checks, start the visible one-shot animation when needed, call `apply_effect`, and remove the item from the pocket.
- Room-art final frames are applied by room `on_enter` callbacks into `rooms.current_room_buf`.
- Current defaults are broken glass `0`, red door opened by red card `1`, hatch key used `1`, elevator repaired by toolkit `1`, generator started `0`, and stairs unfolded `0`.

## Persistent Room Art

- One-shot item animations draw their changing frames directly to the screen. They patch only the final frame into `rooms.current_room_buf` when the animation completes.
- Room `on_enter` callbacks check explicit room/effect flags instead of inferring state from item inventory fields.
- Persistent final-art flags are `logic.room_2_0.roof_broken`, `logic.room_1_1.glass_broken`, `logic.room_1_0.stairs_unfolded`, `logic.room_6_1.red_door_opened`, `logic.room_4_1.hatch_opened`, `logic.room_4_0.handle_used`, `logic.room_5_0.seeds_grown`, and `logic.room_2_3.dynamite_exploded`.
