# Route Map Viewer

Small browser viewer for room borders and enemy route points.
Wheel over the map zooms from `1x` to `4x`.

## Files

- `routes.json` is the route manifest used by the viewer and generator.
- `routes/room_X_Y.json` files store editable route points split by room.
- `routes-data.js` is generated from the split JSON data so `index.html` also works from a direct file open.
- `import_from_asm.php` imports current project route data from `enemies.route*.a80`, `rooms.a80`, `rooms_unpacked/`, and route rewires in `logic/*.a80`.
- `generate_asm.php` generates `enemies.route.a80` and `enemies.route_X_Y.a80` files from `routes.json` and `routes/room_X_Y.json`.

## Commands

```sh
php tools/map-viewer/import_from_asm.php
php tools/map-viewer/generate_asm.php
php tools/map-viewer/generate_asm.php --project
```

By default, generated asm files go to `tools/map-viewer/generated/`.
Use `--project` only when you intentionally want to overwrite the project route asm files.
