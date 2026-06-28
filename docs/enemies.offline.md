# Enemies Offline Movement

## Scope

- A targetless offline enemy first performs one route attachment attempt.
- Route attachment searches only the generated point table for the enemy's room.
- The first point in authored table order inside the inclusive `x +/- 7`, `y +/- 2` window becomes the enemy's target.
- Route point type does not filter attachment candidates.
- The attachment attempt is recorded even when no suitable point exists, so a failed search is not repeated.
- Offline enemies without a target skip movement after a failed route attachment.
- Offline route-following enemies advance through route points without physics.
- Every enemy outside the active room is offline and uses route behavior.
- Entering offline mode immediately cancels aggressive behavior and its pending exit timer.
- After an offline route step enters the active room, room-coordinate refresh immediately switches the enemy online.
- The enemy keeps route behavior after becoming online and continues toward points with physical movement.
- The `1/8` route-to-free roll is performed after entering the active room at a route point.
- Offline enemies are never drawn because their room differs from the active room.
- Offline route movement pacing is controlled by an enemy-local offline route timer.
- Offline route followers spend 50 frames per route point through an enemy-local timer.
- Offline route followers use the same top-left/bottom-right and optional alternative branch selection as online route followers, but without physical point effects.
- A same-room offline step updates stored horizontal direction toward the point being reached before that point selects its next link.
- Reaching an offline `jump_left` or `jump_right` point updates direction toward the selected next target without starting jump physics.
- This direction update lets one-way return links leave the room `0,2` water loop and the room `0,3` left loop.
- When an offline route follower's previous point matches neither direct link, the shared selector uses its stored horizontal movement direction.
- Offline route followers treat `route_point_wait` like any other route point; wait behavior is only meaningful while the enemy is online.
- Enemies that physically fell through the bottom edge are forced offline in the room below; route followers continue from their selected target.
- Elevator and accidental edge handoffs replace targets that still belong to the previous room.
- If the destination room has no point inside the attachment window, the target stays empty and the offline enemy stops instead of moving back through a stale target.
- Offline and online route followers share the same target-selection helper after a route point is reached.

## Route Movement

- Offline route followers advance along the same route, but without physics: after their offline timer expires, they are treated as reaching the current target, move directly to that point's room and position, then choose the next target.
- Offline route movement refreshes simulation mode after moving to a route point and selecting its next target.
- If the new point belongs to the active room, the enemy becomes online and performs the route-to-free roll.
- If room activation catches an offline enemy paused on an exit with its next target outside, the pending handoff is completed through the normal cross-room route path.

## Route Point References

- Route point data and link rules are documented in [Route Points Domain](waypoints.md).
