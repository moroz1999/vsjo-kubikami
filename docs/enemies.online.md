# Enemies Online Movement

## Scope

- Only online enemies process per-frame physical movement.
- Enemy movement pacing is controlled by enemy-local movement delay and movement timer fields.
- Route-following is a separate behavior model from free roaming; route followers do not use random standing/walking choices while following a route.
- Online route followers move physically toward the current target and use the regular enemy walking, obstacle, falling, and jump behavior while doing so.
- Outside water, when a route target has the same `x` and a lower `y`, the route follower uses normal gravity instead of route-specific downward movement.
- Outside water, route followers do not self-propel upward toward route targets. A target above the same `x` must be reached by a jump, an elevator, or another external position change.
- While occupying a water cell, online enemies use swimming gravity instead of entering the normal falling state.
- Swimming enemies sink after `swim_delay` frames, matching the hero's water gravity delay.
- While submerged, route followers can swim vertically toward a same-`x` route target.
- Route-following swim gravity is suppressed while the current target is horizontally offset or above the enemy; this keeps horizontal and upward swim routes from being pulled downward between route steps.
- `route_point_wait` points keep route-following active while the enemy stands on an elevator lane and waits for the next target to become reachable.

## Route Movement

- Online route followers treat a route point as reached only when the enemy's room-local position matches the point.
- Online route followers move physically only while the selected target belongs to the enemy's current room.
- When the target `x` differs, horizontal movement takes priority. The route follower sets direction toward the target and uses the normal `move_enemy_left` or `move_enemy_right` path.
- When the target `x` matches and the target is above, the route follower waits in air and swims upward in water.
- When the target `x` matches and the target is below, the route follower lets gravity move it down in air and swims downward in water.
- Online route followers choose the direct neighbor opposite to `last_route_point_ptr`.
- If the previous point matches neither direct link, online route followers preserve horizontal movement direction: left selects `top_left_point_ptr`, and right selects `bottom_right_point_ptr`.
- If the reached point has an enabled `alternative_point_ptr`, online route followers choose 50/50 between the direct neighbor and the alternative branch.
- Online route followers advance their target before gravity is applied; gravity itself does not choose route targets.
- Online route followers do not advance route targets while `state_jump` is active; jump targets are processed after the arc ends and routing resumes.
- Route-following enemies return to route-following behavior after fall or jump physics while they still have a route target.
- Online route followers wait `route_jump_delay_frames` frames after reaching a `route_point_jump_left` or `route_point_jump_right` point, then start the selected jump.
- Pending route-jump delay suppresses fall-state checks so edge jump points can wind up before the arc starts.
- Online route followers step diagonally down on a one-cell ledge and fall from deeper drops; they do not choose jumping or turning back at that ledge.
- Falling route followers check whether they reached their current route point immediately after each downward fall step, before the next bottom-edge handoff can run.
- Online enemies that reach or stand on the bottom screen edge move to the same `x` at `y=0` in the room below and become offline.
- Route followers use the same bottom-edge handoff from `state_route`; they do not need to enter `state_fall` first.
- After reaching an exit point, an online route follower moves to the selected linked entry point, takes that point's room and position, marks its old cell for restore, and becomes offline immediately.
- The route-exit handoff immediately treats the linked entry point as reached and selects the next target from that new room before offline simulation continues.
- Visible enemy position changes mark the previous cell for restore through the shared position setter.
- Cross-room enemy movement uses a shared leave-room path that marks the old cell for restore, stores the new room-local position, resets the offline route timer, and switches the enemy offline.
- Online enemies can stand on elevator tops as solid floor while still walking left or right through the normal movement path.
- Online enemies carried out of the room by an elevator use the shared offline room handoff: bottom riders enter the room below, and top riders enter the room above.

## Route Point References

- Route point data and link rules are documented in [Route Points Domain](waypoints.md).
