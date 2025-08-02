# Laravel TUI - TODO

## Core Architecture
- [x] Design and implement a constraint-based, nestable layout engine (vertical, horizontal, sidebar, etc.)
- [x] Each layout/widget receives region (width, height, offset) and renders only within that region
- [x] Layout engine recalculates regions on every frame, using current terminal size
- [x] Widgets and layouts are composable and deeply nestable

## Redraw Loop
- [x] Framework-managed redraw/input loop (hidden from end user)
- [x] Handles alternate screen buffer, raw mode, and terminal resizing
- [x] User-facing API is declarative: users only declare widget/layout tree and handle actions

## Widget API
- [x] Standardize widget API: all widgets/layouts are chainable, composable, and accept constraints
- [x] Implement core widgets: navbar, block, sidebar, table, list, paragraph, tabs, divider, gauge, chart, etc.
- [x] Each widget has a render($width, $height) method and handles its own borders, alignment, and truncation
- [x] Responsive: widgets/layouts never overflow terminal viewport
- [x] Tabs: Support for keyboard shortcuts (including Ctrl+Key), navigability, and onSelected actions
- [x] Table: Auto-fits container width, robust border/padding math, no overflow or artifacts
- [x] Paragraph, Block, Navbar, List, Sidebar, Divider, Gauge, Chart: Implemented with modern APIs

## Extensibility & Future
- [x] Allow custom widgets/layouts (via closures, classes)
- [ ] Plan for scrollable, collapsible, or paginated content for small terminals (ScrollableContainer in progress)
- [ ] Document all architectural/design decisions in README and code
- [ ] Blade-like or closure-based custom widget/layout syntax (planned)

## Theme System
- [x] Central ThemeManager for registration, switching, and usage
- [x] Support for custom and built-in themes
- [ ] Expand theme customization and documentation

## Known Issues / Lessons Learned
- [x] Ensure all widgets/layouts fit within terminal viewport (no overflow or off-screen rendering)
- [x] All sizing/positioning must be recalculated every frame
- [x] Never require the user to manage the redraw loop manually
- [ ] Continue to test edge cases (resizing, very small terminals, complex nesting)
- [ ] Encourage user feedback during beta

## Documentation
- [x] README updated for beta status, architecture, and usage
- [ ] Expand code-level documentation and examples
- [ ] Add migration notes for any future breaking changes
