# Project Rules

The project rules are split into topic-specific files under `docs/`.
Before starting any task, the agent must read every document from `docs/` that is relevant to the requested work.

# General Rules

1. Never remove user-authored line breaks.
2. Never remove user-authored comments.
3. When the request is only about formatting or commenting, do not change logic. In such tasks, edit only formatting, blank lines, and comments.
4. Before writing or changing asm code, load the relevant instruction details and limits into context first, and choose solutions with t-states and sequence cost in mind.

- [Code Style](docs/code_style.md) - asm layout, naming, structure, or file organization.
- [Documenting](docs/documenting.md) - comments, documentation, translation, or cleanup of written explanations.
- [Optimization](docs/optimization.md) - performance measurement and code optimization workflow.
- [Platform](docs/platform.md) - build flow, hardware limits, and platform compatibility.
- [Domain](docs/domain.md) - gameplay logic, room entities, and hero-room interactions.
- [Algorithms](docs/algorithms.md) - read this before implementing new code
