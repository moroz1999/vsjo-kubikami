# Elevator Update

`elevators_update.update_room` keeps the original update order and adds edge pauses on top of it.

- If `timer > 0`, it is decremented and the elevator does nothing this frame.
- If `timer = 0`, the code first stores the regular `delay` into `timer`, then processes movement.
- If the elevator is already at an edge, or reaches an edge by this step, it flips `state` and overwrites `timer` with `edge_delay`.

This preserves the old frame order that already worked with room transitions.

## Riders

- The hero treats elevators as a separate floor source, not as room geometry: `hero.hero_elevator` and `hero.current_elevator` are refreshed through `elevators_itemcheck.check` with global Y coordinates.
- A hero standing on an elevator is kept in ground state even when the room cell below is empty.
- Elevator movement carries the hero and online enemies through `elevator_move_items`.
- Enemies treat elevator tops as solid floor during fall checks and ledge checks, so they can stand on elevators and walk left or right along them while the platform itself stays outside room geometry.
- Enemy elevator riders use the shared bottom-room handoff when carried below the screen and a matching top-room handoff when carried above it.
