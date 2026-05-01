# Enemies Online Movement

## Scope

- Only online enemies process per-frame physical movement.
- Enemy movement pacing is controlled by enemy-local movement delay and movement timer fields.
- Route-following is a separate behavior model from free roaming; route followers do not use random standing/walking choices while following a route.
- Online route followers move physically toward the current target and use the regular enemy walking, obstacle, falling, and jump behavior while doing so.

## Route Movement

- Online route followers treat a route point as reached only when the enemy's room-local position matches the point.
- Current first-stage implementation moves online route followers horizontally toward the target and relies on normal enemy physics for climbs, falls, and jumps.
- Route-following enemies return to route-following behavior after fall or jump physics while they still have a route target.
- Online route followers start a left or right jump immediately after reaching a `route_point_jump_left` or `route_point_jump_right` point.
- Online route followers step diagonally down on a one-cell ledge and fall from deeper drops; they do not choose jumping or turning back at that ledge.
- After reaching an exit point, an online route follower moves to the linked entry point, takes that point's room and position, marks its old cell for restore, and becomes offline immediately.

## Route Point References

- Route point data and link rules are documented in [Route Points Domain](waypoints.md).
