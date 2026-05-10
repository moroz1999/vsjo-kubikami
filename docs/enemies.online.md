# Enemies Online Movement

## Scope

- Only online enemies process per-frame physical movement.
- Enemy movement pacing is controlled by enemy-local movement delay and movement timer fields.
- Route-following is a separate behavior model from free roaming; route followers do not use random standing/walking choices while following a route.
- Online route followers move physically toward the current target and use the regular enemy walking, obstacle, falling, and jump behavior while doing so.
- When a route target has the same `x` and a lower `y`, the route follower uses normal gravity instead of route-specific downward movement.
- When a route target has the same `x` and a higher `y`, the route follower moves vertically toward it and defers falling until that upward target is no longer active.

## Route Movement

- Online route followers treat a route point as reached only when the enemy's room-local position matches the point.
- Online route followers move physically only while the selected target belongs to the enemy's current room.
- When the target `x` differs, horizontal movement takes priority. The route follower sets direction toward the target and uses the normal `move_enemy_left` or `move_enemy_right` path.
- When the target `x` matches and the target is above, the route follower tries one vertical cell upward if that cell is empty.
- When the target `x` matches and the target is below, the route follower lets gravity move it down.
- Online route followers choose the direct neighbor opposite to `last_route_point_ptr`.
- If the previous point matches neither direct link, online route followers use `bottom_right_point_ptr`.
- If the reached point has an enabled `alternative_point_ptr`, online route followers choose 50/50 between the direct neighbor and the alternative branch.
- Online route followers advance their target before gravity is applied; gravity itself does not choose route targets.
- Route-following enemies return to route-following behavior after fall or jump physics while they still have a route target.
- Online route followers start a left or right jump immediately after reaching a `route_point_jump_left` or `route_point_jump_right` point.
- Online route followers step diagonally down on a one-cell ledge and fall from deeper drops; they do not choose jumping or turning back at that ledge.
- Falling route followers still check whether they reached their current route point, so gravity-driven shaft drops can advance through intermediate route points.
- Online enemies that reach or stand on the bottom screen edge move to the same `x` at `y=0` in the room below and become offline.
- Route followers use the same bottom-edge handoff from `state_route`; they do not need to enter `state_fall` first.
- After reaching an exit point, an online route follower moves to the selected linked entry point, takes that point's room and position, marks its old cell for restore, and becomes offline immediately.
- Visible enemy position changes mark the previous cell for restore through the shared position setter.
- Cross-room enemy movement uses a shared leave-room path that marks the old cell for restore, stores the new room-local position, resets the offline route timer, and switches the enemy offline.

## Route Point References

- Route point data and link rules are documented in [Route Points Domain](waypoints.md).
