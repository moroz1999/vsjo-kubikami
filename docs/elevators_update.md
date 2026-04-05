# Elevator Update

`elevators_update.update_room` keeps the original update order and adds edge pauses on top of it.

- If `timer > 0`, it is decremented and the elevator does nothing this frame.
- If `timer = 0`, the code first stores the regular `delay` into `timer`, then processes movement.
- If the elevator is already at an edge, or reaches an edge by this step, it flips `state` and overwrites `timer` with `edge_delay`.

This preserves the old frame order that already worked with room transitions.
