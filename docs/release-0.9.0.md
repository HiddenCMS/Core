# HiddenCMS Core 0.9.0

- Replace the iframe-based live editor with a dedicated outline layout builder inspired by Gantry 5.
- Add a neutral canvas for sections, rows, columns and widgets with drag and drop reordering.
- Add drag resizing between adjacent columns with twelve-column snapping.
- Restore independent vertical scrolling in the layout workspace.
- Restore the complete widget selection wizard for adding and configuring widgets.
- Add row and widget appearance controls, responsive column widths, and explicit undo and redo actions.
- Save the complete outline transactionally, with server-side validation and automatic rollback on failure.
- Integrate outline assignments, inheritance and display options directly into the builder.
- Restore TinyMCE and the media library in widget configuration dialogs.
- Remove the legacy iframe editor and its obsolete assets.
- Fix deferred media-picker loading on administration pages.

## Compatibility

PHP >=8.1; Altitude ^0.5. Database schema remains 0.8.0; no new migration.

## Validation

PHP syntax, translation catalogue and git diff checks pass. The builder was tested locally for widget creation and configuration, row and widget appearance, column resizing, reordering, undo and transactional save. The media picker was tested from an administration form.
