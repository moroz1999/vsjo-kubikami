# Enemies Offline Movement

## Scope

- Offline non-route enemies skip movement.
- Offline route-following enemies advance through route points without physics.
- Offline route movement pacing is controlled by an enemy-local offline route timer.
- Offline route followers spend 50 frames per route point through an enemy-local timer.
- Offline route followers use the same top-left/bottom-right and optional alternative branch selection as online route followers, but without physical point effects.
- Enemies that physically fell through the bottom edge are forced offline in the room below; route followers continue from their selected target.
- Offline and online route followers share the same target-selection helper after a route point is reached.

## Route Movement

- Offline route followers advance along the same route, but without physics: after their offline timer expires, they are treated as reaching the current target, move directly to that point's room and position, then choose the next target.
- Offline route movement refreshes the enemy simulation mode after moving to a route point.

## Route Point References

- Route point data and link rules are documented in [Route Points Domain](waypoints.md).
