# Enemies Aggressive Movement

## Vision

- Vision is checked once per frame for every online enemy.
- Horizontal vision is bidirectional and uses an enemy-local range.
- Vertical vision is always the inclusive `y +/- 2` window.
- Vision is coordinate-based and does not test room walls or other occlusion.
- Current horizontal ranges for `enemy_0` through `enemy_8` are `11`, `19`, `9`, `14`, `17`, `8`, `10`, `13`, and `15` cells.
- Horizontal vision increases with movement delay, so slower enemies see farther.
- Seeing the hero switches free or route behavior to aggressive behavior immediately.
- Entering aggression records the previous behavior, but that value does not control chase exit.

## Pursuit

- Aggressive enemies are drawn in purple.
- Aggressive movement uses the enemy-local movement timer and normal horizontal movement helpers.
- The enemy chooses left or right from the hero's current horizontal coordinate on every movement step.
- Normal obstacle climbing, ledge handling, falling, jumping, swimming gravity, and elevator behavior remain active.
- Aggressive behavior suppresses free standing/walking state rerolls.

## Losing The Hero

- Seeing the hero reloads the enemy-local aggressive stick timer every frame.
- The stick timer starts counting down when the hero leaves the vision window.
- Current stick delays for `enemy_0` through `enemy_8` are `120`, `225`, `90`, `160`, `200`, `75`, `105`, `140`, and `175` frames.
- Aggressive stick delay increases with movement delay, so slower enemies pursue longer after losing sight.
- Until the timer expires, pursuit continues toward the hero's current horizontal coordinate.
- Timer expiration always switches the enemy to free behavior and reloads its state and free-to-route timers.
- After chase exit, route behavior can resume only through the regular timed `1/8` free-to-route roll.
- Active fall or jump motion is not interrupted when aggression starts or ends.
- Entering offline mode cancels aggression immediately, resets the timer, and forces route behavior.
