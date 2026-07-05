# Items Domain

## Crowbar

- The crowbar is used in room `2,0` when the hero stands at `(21,12)`.
- Using it breaks the roof, starts the room ground animation, and removes the item from the pocket.
- Successful use starts AYFX effect `1`.
- The enemy route effect is `logic.room_2_0.activate_roof_route`: it rewires `route_2_0_top_mid.bottom_right_point_ptr` to the normal `route_2_0_basement_entry`.
- After the rewire, enemies can enter the lower room path through the broken roof route without starting a jump, then use the separate basement jump point to climb back right.

## Toolkit

- The toolkit is used in room `2,1` when the hero stands at `y=19` and `x=8..19`.
- Using it starts `elevator_2_3` downward, calls `logic.room_2_1.activate_elevator_route`, and removes the item from the pocket.
- The enemy route effect breaks the pre-repair route across the stopped elevator: `route_2_1_left_mid.bottom_right_point_ptr` goes to `route_2_1_left_fall_exit`, and `route_2_1_right_mid.top_left_point_ptr` goes to `route_2_1_right_fall_exit`.
- After the rewire, route followers fall down the `2,1 -> 2,2 -> 2,3` shaft lanes and use the bottom wait points to ride the repaired elevator back up.

## Stone

- The stone starts at `(28,10)` directly under the lower-right ceiling in room `2,1`.
- When the hero reaches column `x=12`, room `2,1` releases the stone and keeps moving it down one attribute row every three frames until it lands at `(28,19)`.
- Each falling step queues the previous stone cell for background restoration before the item is drawn at its new position.
- The stone is used anywhere in room `1,1` from the right edge of the glass to the screen edge (`x=20..31`, any `y`).
- Using it starts the glass break animation, removes the item from the pocket, and calls `logic.room_1_1.activate_glass_route`.
- The enemy route effect enables the two glass-edge jump points, so enemies can cross the room `1,1` glass only after it has been broken.
- Room `1,1` stores the persistent result in `logic.room_1_1.glass_broken`; `on_enter` applies the final broken-glass frame when this flag is set.

## Stairs

- The stairs are used in room `1,0` when the hero stands at `y=13` and `x=12..17`.
- Using them starts the unfolding animation and removes the item from the pocket.
- Room `1,0` stores the persistent result in `logic.room_1_0.stairs_unfolded`; `on_enter` applies the final unfolded-stairs frame when this flag is set.
- The enemy route effect is `logic.room_1_0.activate_stairs_route`: it enables `route_1_0_lower_walk.alternative_point_ptr = route_1_0_stairs_lower_jump_left`, making the stair climb an optional branch after the stairs are unfolded.

## Red Card

- The red card is used in room `6,1` when the hero stands at `y=11` and `x=8..12`.
- Using it opens the red door, starts the door animation, removes the item from the pocket, and does not change enemy route points.
- Enemies enter and leave room `6,1` through the shared point `(0,19)`, climb from `(1,19)` to the red-door approach at `(8,11)`, then fall through `(2,20)` back toward that shared entry/exit; they do not go behind the red door after the card is applied.

## Key

- The key is used in room `4,1` when the hero stands at `x=17..22` and `y=16..19`.
- Using it opens the hatch, starts the hatch animation, removes the item from the pocket, and sets `logic.room_4_1.hatch_opened`.
- The enemy route effect is `logic.room_4_1.activate_hatch_route`: it enables `route_4_1_hatch_fork.alternative_point_ptr = route_4_1_hatch_drop`.
- The hatch branch lets route followers drop from room `4,1` into room `4,2` only after the hatch has been opened.

## Screwdriver

- The screwdriver is used in room `6,3` when the hero stands between `x=11` and `x=12` at `y=20`.
- Using it repairs the generator, switches the generator-related room animations on, starts the `6,3` elevator upward, starts the three `4,1` elevators upward, enables the room `4,1` wait branch, and removes the item from the pocket.
- The enemy route effect is `logic.room_4_1.activate_elevator_wait_route`: it enables `route_4_1_right_mid.alternative_point_ptr = route_4_1_elevator_wait`.
- Room `4,1` keeps its normal `jump_right` route across the blocked floor gap, while the optional wait branch rides the right hatch-screen elevator to the upper exit into room `5,1`.
- Room `6,3` has no screwdriver-unlocked enemy route branch.
- Room `5,1` has no local screwdriver-unlocked route branch; its upper route is reached through the repaired room `4,1` elevator branch, and its bottom route stays connected to room `6,1`.

## Handle

- The handle lies on the far-right accessible step under the room `3,3` monster at `(29,17)`.
- The handle is used in room `4,0` when the hero stands at `y=20` and `x=1..4`.
- Using it opens the balcony door at `(0,16)` with a six-frame screen animation and sets `logic.room_4_0.handle_used`.
- The dark green/cyan door (`%00100101`) is one attribute cell wide: it first moves whole one cell right, then rises five cells parallel to the left wall; the final two-column patch keeps the raised door at `x=1`, `y=11..15`, changes `x=0`, `y=16..20` to gray balcony background, and leaves `x=1`, `y=16..20` black.

## Seeds

- The seeds lie at `(21,11)` directly above the center of the flower in room `5,3`.
- The seeds are used in room `5,0` when the hero stands at `y=14` and `x=18..24`.
- Using them grows a flower above the pot at `(18,8)` with a four-frame screen animation and sets `logic.room_5_0.seeds_grown`.
- The final flower is `8x5`: its gray stem column is passable and reaches each leaf row, while its fewer wide green leaves are solid climb steps.

## Bug Jar

- The bug jar lies under the upper bed in room `5,0` at `(6,7)`, reachable after the seeds grow the flower climb.
- The bug jar is used in boss room `3,3`; for now, the whole screen is an active use zone.
- Using the bug jar removes it from the pocket and starts `logic.room_3_3.start_dissolve`.
- Room `3,3` removes the boss by decrementing the visible boss point budget each frame. When the budget reaches zero, the boss state becomes gone, the last drawn points are restored once, and future boss drawing stays disabled.

## Dynamite

- The dynamite is used in room `2,3` when the hero stands at `x=20..22`.
- Using it starts the explosion animation at `(23,11)`, sets `logic.room_2_3.dynamite_exploded`, and removes the item from the pocket.
- Room `2,3` applies the final black explosion patch on enter when `logic.room_2_3.dynamite_exploded` is set.

## Bottle

- The empty bottle lies on the lower balcony in room `3,0` at `(29,20)`.
- The empty bottle is used in room `6,3` on the ledge by the poison pool left of the lift, when the hero stands at `y=5` and `x=8..12`.
- Using the empty bottle removes it from the pocket and immediately puts `items.poison_bottle` into the pocket.
- The poison bottle inventory colors use the room `6,3` poison-pool animation palette: `%00100110` and `%01100111`.

## Poison Bottle

- The poison bottle is used in boss room `1,3`; for now, the whole screen is an active use zone.
- Using the poison bottle removes it from the pocket, sets `logic.room_1_3.boss_poisoned`, restores the last boss footprint, and stops the boss from updating or drawing.

## Cube

- The cube lies in room `1,0` at `(19,17)`.
- Its two gigacolor attributes reference `hero.hero_color1` and `hero.hero_color2`, so it always uses exactly the hero palette.
- The cube is collectible and has no use effect yet.
- Fully dissolving the right boss in room `3,3` calls `logic.room_1_0.release_cube`, marks the cube as no longer located in any room, and sets `logic.room_1_0.cube_escaped`.
- When `cube_escaped` is set, room `1,0` paints `(19,17)` black on enter so the empty box remains visibly open.

## Valve

- The valve lies in room `0,1` at `(8,8)`.
- The valve is used in room `4,3` when the hero stands at `(4,14)`.
- Using it removes the item from the pocket, immediately removes the initial water row `y=9`, and starts the visible room `4,3` drain at water level `y=10`; progress persists across room transitions.
- The remaining levels move `y=10 -> 11 -> 12`, one row every `50` frames.
- Each room `4,3` drain step clears the departed physical-water row and moves its full-width animated surface down with the new level.
- Room `5,3` uses the final `y=12` physical-water level immediately after the valve is applied.
- The effect disables the old room `4,3` surface at `x=6..17`, `y=8` and enables post-valve surfaces across `x=2..31` in room `4,3` and `x=0..29` in room `5,3`.

## Debug Initial Item States

- `debug.apply_initial_item_states` runs once at startup before `rooms.init_current_room`.
- `debug.initial_broken_glass`, `debug.initial_red_door_opened`, `debug.initial_hatch_key_used`, `debug.initial_elevator_repaired`, `debug.initial_generator_started`, `debug.initial_stairs_unfolded`, `debug.initial_dynamite_exploded`, and `debug.initial_water_lowered` are compile-time 0/1 flags for item effects that should start already applied.
- Each enabled flag calls the matching item module's `apply_effect` routine, then removes the consumed item from `items.all_items`.
- Item `apply_effect` routines contain only the persistent gameplay effect: room/effect flags, route rewires, elevator states, and animation-state switches. They do not check hero coordinates, start one-shot screen animations, or remove the item from the hero pocket.
- Runtime item `action` routines handle hero-position checks, start the visible one-shot animation when needed, call `apply_effect`, and remove the item from the pocket.
- Room-art final frames are applied by room `on_enter` callbacks into `rooms.current_room_buf`.
- Current defaults are broken glass `0`, red door opened by red card `0`, hatch key used `0`, elevator repaired by toolkit `0`, generator started `0`, stairs unfolded `0`, dynamite exploded `0`, and water lowered by valve `0`.
- With all initial-effect flags disabled, authored items remain at their `items.all_items` room positions until collected and used.
- Game restart restores only each item's mutable `room_x`, `room_y`, `room_pos_x`, and `room_pos_y` fields. Type, name, action, and colors remain untouched, and debug initial item states are applied after this location reset.

## Persistent Room Art

- One-shot item animations draw their changing frames directly to the screen. They patch only the final frame into `rooms.current_room_buf` when the animation completes.
- Room `on_enter` callbacks check explicit room/effect flags instead of inferring state from item inventory fields.
- Persistent final-art flags are `logic.room_2_0.roof_broken`, `logic.room_1_1.glass_broken`, `logic.room_1_0.stairs_unfolded`, `logic.room_6_1.red_door_opened`, `logic.room_4_1.hatch_opened`, `logic.room_4_0.handle_used`, `logic.room_5_0.seeds_grown`, `logic.room_2_3.dynamite_exploded`, and `logic.room_4_3.water_lowered`.
