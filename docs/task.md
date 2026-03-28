Review how elevator handling is organized in the project. In short: there is a buffer that, during room initialization, receives pointers to elevators that belong to the current room. We need to add repeated animation display in the same way.

1. Add an animation structure with room number, coordinate, width, height, frame count, current frame, delay, status (`on` / `off`), and a pointer to frame data.
2. Add an animation list. Include a couple of sample entries.
3. Copy pointers to animation structures into a dedicated buffer during room initialization.
4. Add a procedure that iterates over the pointer list for animations in the current room.
5. Account for delay. The delay is measured in frames and simply means how many frames must be skipped.
6. Add a procedure that draws the current animation frame. See `animate.a80` for an example.
