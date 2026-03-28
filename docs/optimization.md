# Optimization

- Always calculate the total t-states for the exact hot path via MCP before any optimization.
- Count the whole path being compared, not one isolated instruction.
- After the code change, recalculate the same path via MCP.
- If total t-states did not go down, do not treat the optimization as successful without an explicit reason.
- Report the result to the user briefly: what path was measured, before, after, and the delta in t-states.

