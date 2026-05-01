# Enemies Offline Movement

## Scope

- Offline non-route enemies skip movement.
- Offline route-following enemies advance through route points without physics.
- Offline route movement pacing is controlled by an enemy-local offline route timer.
- Offline route followers spend 50 frames per route point through an enemy-local timer.

## Route Movement

- Offline route followers advance along the same route, but without physics: after their offline timer expires, they are treated as reaching the current target, move directly to that point's room and position, then choose the next target.
- Offline route movement refreshes the enemy screen status after moving to a route point.

## Route Point References

- Route point data and link rules are documented in [Route Points Domain](waypoints.md).
