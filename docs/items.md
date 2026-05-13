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
- Using it opens the red door, starts the door animation, removes the item from the pocket, and calls `logic.room_6_1.activate_red_door_route` and `logic.room_6_2.activate_shaft_up_entry_route`.
- `logic.room_6_1.activate_red_door_route` rewires `route_6_1_red_door_left` from the closed-door fall-and-exit lane into the right-half door route.
- `logic.room_6_2.activate_shaft_up_entry_route` rewires `route_6_2_shaft_up_top_exit.bottom_right_point_ptr` to `route_6_1_shaft_bottom_entry`, so the `6,1` right-half route points from below are unavailable until the red card has opened the door.

## Screwdriver

- The screwdriver is used in room `6,3` when the hero stands between `x=11` and `x=12` at `y=20`.
- Using it repairs the generator, switches the generator-related room animations on, starts the `6,3` elevator upward, starts the three `4,1` elevators upward, and removes the item from the pocket.
- The enemy route effects are split between `logic.room_4_1.activate_air_route` and `logic.room_6_3.activate_shaft_up_route`.
- In room `4,1`, `logic.room_4_1.activate_air_route` sets `route_4_1_left_mid.alternative_point_ptr` to `route_4_1_air_low`, enabling the short upward air route as a fork.
- In room `6,3`, `logic.room_6_3.activate_shaft_up_route` sets `route_6_3_bottom_left.alternative_point_ptr` to `route_6_3_shaft_up_floor`, enabling the repaired left elevator shaft climb back up through `6,2` and toward `6,1`.
- Room `5,1` has no screwdriver-unlocked route branch; its bottom route stays connected to room `6,1`.
