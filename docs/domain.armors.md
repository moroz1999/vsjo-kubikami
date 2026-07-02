# Armors Domain

## Data

- Armors are room entities gathered into `rooms.room_armors_buf` for the current room.
- Big armor occupies a 3x3 attribute rectangle.
- Small armor occupies a 1x2 attribute rectangle.
- Big armor restores `hero.max_armor`.
- Small armor restores half of `hero.max_armor`.

## Drawing

- Armor drawing uses the shared draw color switch through the armor two-color table.
- Armor drawing selects the blinking color once per armor entity.
- Armor cells are written directly to screen attributes after calculating the top-left attribute address.

## Collection

- Collected armor switches to `state_off`.
- After collection, the armor rectangle is restored from the current room data.

## Placement

- Room `6,0` has a small armor at `(23,9)`.
- Room `5,3` has a big armor at `(22,5)`.
