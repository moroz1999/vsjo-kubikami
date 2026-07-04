# Enemies Domain

## Module Organization

- The `enemies` module should stay split by topic-specific include files.
- Do not accumulate unrelated enemy functions in `enemies.a80`; put movement, drawing, collision, data, and random helpers into separate `enemies.*.a80` files.
- Route point records live in per-room include files named `routes/enemies.route_X_Y.a80`; `routes/enemies.route.a80` keeps the route pointer table and shared route logic.
- Gameplay route rewires caused by room items belong to the corresponding `logic.room_X_Y` module, not to `routes/enemies.route.a80`.

## Data

- Enemies are room entities drawn from the global `enemies.enemies` table.
- Enemy records use room coordinates as `room_x` and `room_y`, not a linear `room_id`.
- Enemy positions use room attribute coordinates, not pixel coordinates.
- Enemies are processed from the global table, without a current-room enemy buffer.
- Online and offline enemy updates use separate global-table passes.
- The online pass runs inside `game_process.calculate_state` before drawing.
- The offline pass runs after all visible frame drawing and before the interrupt wait.
- Enemy records keep simulation mode separately from movement behavior.
- Enemies in the active room are always online; enemies outside it are always offline.
- Room initialization and offline route steps refresh simulation mode directly from enemy room coordinates.
- Enemy records keep a movement behavior: free, route, or aggressive.
- Enemy `state` stores only transient physical motion: stand, walk, fall, or jump.
- Route and aggressive behavior are not duplicated in `state`; they persist through fall and jump transitions in `behavior_mode`.
- Enemy records store individual horizontal vision range and aggressive stick-delay fields.
- Offline enemies currently always use route behavior.
- Online route enemies have a `1/8` chance to switch to free behavior at each reached route point.
- Each enemy record stores an individual free-to-route delay and timer.
- While an enemy is online and free, its timer counts down; after expiration it has a `1/8` chance to switch to route behavior.
- The current enemy delays are `150`, `180`, `220`, `250`, `160`, `190`, `205`, `235`, and `245` frames.
- Every failed or successful timer roll reloads the enemy's configured delay.
- A timer that expires during fall or jump waits at zero until stable physical motion resumes.
- Route/free behavior changes preserve route pointers.
- Generated room route tables keep each room's route point pointers in authored global table order.
- Offline target attachment is attempted at most once per enemy.
- A targetless online enemy searches after each successful free-to-route timer roll until a suitable point is found.
- Route attachment selects the first point in the enemy's room within the inclusive horizontal window `x +/- 7`, `y +/- 2`.
- A route-target search is never repeated, including when no suitable point was found.
- Every physical room handoff validates that the stored route target belongs to the new room.
- A stale or missing target is cleared and replaced through the existing first-point-in-window attachment search.
- Authored route exits keep their selected entry target because it already belongs to the destination room.
- Activating a room completes any pending route handoff whose target already belongs to another room.
- The enemy table contains nine enemies.
- `enemy_0` through `enemy_7` start in rooms `0,0`, `3,1`, `5,1`, `0,1`, `1,2`, `5,3`, `5,2`, and `0,3`.
- `enemy_8` is the second starting enemy in room `0,0`.
- Current movement delays for `enemy_0` through `enemy_8` are `10`, `18`, `7`, `13`, `16`, `6`, `9`, `12`, and `14`.
- Current horizontal vision ranges for `enemy_0` through `enemy_8` are `5`, `9`, `4`, `7`, `8`, `4`, `5`, `6`, and `7`.
- New `enemy_4` through `enemy_8` use distinct state-choice delays `70`, `90`, `50`, `110`, and `30`.
- Slower enemies have longer horizontal vision and aggressive stick delays; faster enemies have shorter values for both.
- Game restart restores authored room/cell placement, physical state, first state timer, direction, behavior, and both route anchors. Transient motion fields are cleared, runtime timers are reloaded from the immutable per-enemy delays, and the enemy random seed returns to its initial value; immutable profile fields are not copied.

## Collision

- Enemy collision applies 16 damage through the standard `hero.decrease_health` armor and health path.
- Each accepted enemy-damage event starts AYFX effect `4`, the fifth effect in the zero-based bank.
- Standard enemy damage uses the delayed-damage flag and starts a `100`-frame hero invulnerability timer.
- Further delayed damage is ignored while that timer is active; immediate damage bypasses the timer.
- Water-drop collision damage also uses the delayed path, preserving its shared contact-damage protection.
- Oxygen loss damages health through the separate immediate path and is never delayed by enemy invulnerability.
- The room `1,3` and `3,3` boss bites each deal `(max_health + max_armor) / 3`, consume armor before health at a 1:1 rate, and use the standard `100`-frame delayed-damage timer; three accepted bites kill from full armor and health.
- Online enemies in the active room collide with the hero.

## Drawing

- Enemy drawing is connected to the main draw pass through `enemies.draw_enemies`.
- Enemy drawing checks room coordinates; room membership guarantees that every visible enemy is online.
- All visible enemies use the shared blinking palette; physical state and behavior do not change their color.
- Aggressive enemies pursue the hero horizontally while their behavior remains active.
- Aggressive timeout always returns the enemy to free behavior.
- Enemies are drawn before the hero, so the existing hero draw can stay visually on top when positions overlap.
- Route debug drawing is compile-time gated by `debug.route_points_debug_enabled`.
- Route debug drawing is disabled by default.
- Route debug helpers and colors live in `debug.a80`.
- When route debug drawing is enabled, current-room route points are bright magenta.

## Topic Details

- [Free Movement](enemies.free.md)
- [Online Movement](enemies.online.md)
- [Offline Movement](enemies.offline.md)
- [Aggressive Movement](enemies.aggressive.md)
- [Route Points](waypoints.md)
- [Route Point Logic](route-points.logic.md)
