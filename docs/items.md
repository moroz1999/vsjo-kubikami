# Items Domain

## Crowbar

- The crowbar is used in room `2,0` when the hero stands at `(21,12)`.
- Using it breaks the roof, starts the room ground animation, and removes the item from the pocket.
- The enemy route effect is `enemies.activate_2_0_roof_route`: it rewires `route_2_0_top_mid.bottom_right_point_ptr` to the normal `route_2_0_basement_entry`.
- After the rewire, enemies can enter the lower room path through the broken roof route without starting a jump, then use the separate basement jump point to climb back right.

## Screwdriver

- The screwdriver is used in room `6,3` when the hero stands between `x=11` and `x=12` at `y=20`.
- Using it repairs the generator, switches the generator-related room animations on, starts the `6,3` elevator upward, starts the three `4,1` elevators upward, and removes the item from the pocket.
- The enemy route effect is `enemies.activate_screwdriver_routes`.
- In room `4,1`, it sets `route_4_1_left_mid.alternative_point_ptr` to `route_4_1_air_low`, enabling the short upward air route as a fork.
- In room `6,3`, it sets `route_6_3_bottom_left.alternative_point_ptr` to `route_6_3_shaft_up_floor`, enabling the repaired left elevator shaft climb back up through `6,2` and toward `6,1`.
- Room `5,1` has no screwdriver-unlocked route branch; its bottom route stays connected to room `6,1`.
