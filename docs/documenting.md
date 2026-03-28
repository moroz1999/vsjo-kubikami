# Documenting

- Preserve user-authored line breaks.
- Preserve user-authored comments.
- Write with maximum meaning and minimum filler: concise, dense, and clear, without losing the core point.
- When expanding the documentation set, do not duplicate the same rule across multiple docs. Keep each rule in the most relevant document only.
- For formatting-only or commenting-only requests, do not change logic. Edit only formatting, blank lines, and comments.
- In asm files, prefer one short comment per semantic block instead of comments on every instruction.
- Comments should explain the algorithm flow and processing phases, not restate each instruction.
- Keep right-side comments only for code that is genuinely non-obvious, such as dense arithmetic, optimization tricks, unusual register contracts, magic constants, or non-trivial control flow.
- New asm comments should be brief and in English.
- For structs, initializers, and tables, short schema comments are allowed when they help explain the data layout.
- In struct initializers, short right-side field-name comments such as `; room_x`, `; delay`, or `; frames_ptr` are encouraged when they make the field order easier to read.
- When reporting an optimization result to the user, keep it brief: name the measured path, show before and after totals, and include the t-state delta.
