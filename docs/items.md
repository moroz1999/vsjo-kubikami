# Items Domain

## Crowbar

- The crowbar is used in room `2,0` when the hero stands at `(21,12)`.
- Using it breaks the roof, starts the room ground animation, and removes the item from the pocket.
- The enemy route effect is `logic.room_2_0.activate_roof_route`: it rewires `route_2_0_top_mid.bottom_right_point_ptr` to the normal `route_2_0_basement_entry`.
- After the rewire, enemies can enter the lower room path through the broken roof route without starting a jump, then use the separate basement jump point to climb back right.

## Stone

- The stone is used in room `1,1` when the hero stands at `(20,19)`.
- Using it starts the glass break animation, removes the item from the pocket, and calls `logic.room_1_1.activate_glass_route`.
- The enemy route effect enables the two glass-edge jump points, so enemies can cross the room `1,1` glass only after it has been broken.

## Red Card

- The red card is used in room `6,1` when the hero stands at `y=11` and `x=8..12`.
- Using it opens the red door, starts the door animation, removes the item from the pocket, and does not change enemy route points.
- Enemies keep using the fall-and-exit lane from `route_6_1_red_door_left`; they do not go behind the red door after the card is applied.

## Screwdriver

- The screwdriver is used in room `6,3` when the hero stands between `x=11` and `x=12` at `y=20`.
- Using it repairs the generator, switches the generator-related room animations on, starts the `6,3` elevator upward, starts the three `4,1` elevators upward, and removes the item from the pocket.
- Using it does not change enemy route points.
- Room `4,1` keeps its normal `jump_right` route across the blocked floor gap.
- Room `6,3` has no screwdriver-unlocked enemy route branch.
- Room `5,1` has no screwdriver-unlocked route branch; its bottom route stays connected to room `6,1`.

## Debug Initial Item States

- `debug.apply_initial_item_states` runs once at startup before `rooms.init_current_room`.
- `debug.initial_broken_glass`, `debug.initial_red_door_opened`, `debug.initial_hatch_key_used`, `debug.initial_generator_started`, and `debug.initial_stairs_unfolded` are compile-time 0/1 flags for item effects that should start already applied.
- Each enabled flag applies the persistent final effect: final room sprite where the item changes room art, route/elevator/animation state where the item unlocks movement, and removal of the consumed item from `items.all_items`.
- Current defaults are broken glass `0`, red door opened by red card `1`, hatch key used `0`, generator started `0`, and stairs unfolded `0`.
