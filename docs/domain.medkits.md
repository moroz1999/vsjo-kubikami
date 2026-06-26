# Medkits Domain

## Data

- Medkits are room entities gathered into `rooms.room_medkits_buf` for the current room.
- Big medkits occupy a 3x3 attribute rectangle and restore `hero.max_health`.
- Small medkits occupy the same 3x3 attribute rectangle but restore half of `hero.max_health`.

## Collection

- Collected medkits switch to `state_off`.
- After collection, the medkit rectangle is restored from the current room data.

## Placement

- Room `6,0` has a big medkit at `(20,8)`.
