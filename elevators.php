<?php

$elevator_room_y = 1;
$current_room_y = 1;
$each_room_height = 22;
$total_rooms = 4;
$elevator_room_base_coord_y = 20;
$elevator_current_height = 2;

// Calculate the top boundary of the current room
// top = (total_rooms - current_room_y) * each_room_height
$current_room_top_coord_y = $current_room_y * $each_room_height;

// Bottom boundary is just top - height of one room
$current_room_bottom_coord_y = $current_room_top_coord_y + $each_room_height - 1;

// Calculate global Y coordinate of the elevator
// elevator_room_y * each_room_height + elevator_room_base_coord_y - elevator_current_height
$elevator_global_coord_y = $elevator_room_y * $each_room_height
    + $elevator_room_base_coord_y
    - $elevator_current_height;

// Determine elevator's relative position
$is_in_current_room = $elevator_global_coord_y > $current_room_top_coord_y && $elevator_global_coord_y <= $current_room_bottom_coord_y;
$is_above_current_room = $elevator_global_coord_y <= $current_room_top_coord_y;
$is_below_current_room = $elevator_global_coord_y > $current_room_bottom_coord_y;

echo 'room top:' . $current_room_top_coord_y . "\n";
echo 'room bottom:' . $current_room_bottom_coord_y . "\n";
echo 'y:' . $elevator_global_coord_y . "\n";
echo 'is_above_current_room:' . $is_above_current_room . "\n";
echo 'is_in_current_room:' . $is_in_current_room . "\n";
echo 'is_below_current_room:' . $is_below_current_room . "\n";