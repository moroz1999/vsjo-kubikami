# Code Style

- Use `lower_snake_case` for labels, procedures, constants, struct fields, tables, buffers, and modules.
- Put procedure labels and jump targets on their own line, without a colon, starting at column zero.
- Name internal labels with a function or semantic prefix such as `update_room_next` or `jump_process_left4`. Do not create anonymous names like `loop1`.
- Write instructions, directives, and operands in lowercase: `ld`, `call`, `equ`, `db`, `dw`, `ds`, `include`, `incbin`.
- In new asm lines, keep `28` spaces from the start of the line to the mnemonic.
- After the mnemonic, use one `tab` before the operand or argument.
- Keep labels at column zero with no indentation.
- Use `module ...` / `endmodule` as the main container for a file or submodule.
- Use `struct ...` / `ends` for packed structure definitions. Treat field order as part of the contract.
- Split large subsystems into related `include` files near the root module, as in `elevators_*.a80`, `rooms_*.a80`, and `logic/*.a80`.
- Name tables and buffers by purpose: `*_table`, `*_buf`, `*_end`, `*_end_marker`, `*_amount`, `*_lim`.
- Do not write a function as one long wall of code. Split it into semantic blocks of 2-10 instructions such as input read, checks, main action, result store, and exit.
- Keep exactly one blank line between neighboring logical blocks inside a procedure. In practice, there must be exactly two consecutive newline characters between blocks.
- Start a new block when a new algorithm phase begins. If several instructions perform one short action, keep them in one block.

