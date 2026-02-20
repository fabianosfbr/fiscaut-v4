# Feature-Developer Agent Playbook (fiscaut-v4.1)

## Mission
Deliver user-facing features by extending the Filament JS layer in this repo—primarily schema/builders and UI components—while preserving the project’s existing patterns (minified/bundled module style, exported component factories, and consistent option/prop wiring).

This agent focuses on implementing **new form/table/widget components**, **enhancing schema primitives (wizard/tabs)**, and **integrating features into existing component ecosystems** without breaking existing API contracts.

---

## Repository Map (What to Touch)

### Primary focus areas

#### 1) Schemas (composition primitives / layout)
- **Directory**: `public/js/filament/schemas`
- **Components**: `public/js/filament/schemas/components`
- **Key files**
  - `public/js/filament/schemas/components/wizard.js` — wizard schema primitive (steps, navigation, state)
  - `public/js/filament/schemas/components/tabs.js` — tabs schema primitive (tab groups, active tab logic)

Use these when:
- You need a multi-step workflow (wizard)
- You need grouped views/sections (tabs)
- You are adding new schema-level behaviors (validation gating, navigation rules, state persistence)

#### 2) Forms (input components)
- **Directory**: `public/js/filament/forms/components`
- **Key files**
  - `textarea.js` — multiline input
  - `tags-input.js` — tags/array input
  - `rich-editor.js` — rich text editor integration
  - `key-value.js` — map/dictionary entry UI
  - `checkbox-list.js` — list of checkboxes with selections

Use these when:
- Adding new input types or extending input behaviors (masking, validation, serialization)
- Standardizing how values are bound/serialized to schema/state

#### 3) Tables (column components)
- **Directory**: `public/js/filament/tables/components/columns`
- **Key files**
  - `toggle.js` — boolean toggle column
  - `text-input.js` — inline editable text column
  - `checkbox.js` — boolean checkbox column

Use these when:
- Adding new column renderers or inline editing
- Extending bulk behavior, formatting, alignment, or per-row actions

#### 4) Widgets (dashboard/stat components)
- **Directory**: `public/js/filament/widgets/components`
- **Stat overview**: `public/js/filament/widgets/components/stats-overview/stat`

Use these when:
- Adding dashboard widgets or summary UI elements
- Integrating new metric renderers or interactive stat blocks

---

## Key Observations / Conventions (Derived from the Codebase)

### 1) Files are bundled/minified ES modules
The exported symbols in the provided context are short (e.g., `o`, `I`, `n`, `s`, `ge`, `a`, `c`). This indicates:
- You are editing **compiled/bundled** artifacts or vendor-like output.
- Names may be minified; **structure and export shape** matters more than symbol names.

**Best practice**
- Preserve module export patterns exactly.
- Avoid large refactors that reorder exports or change public API shape.
- Make small, surgical changes and validate in-browser.

### 2) Components are organized by Filament domain
The directory structure strongly mirrors Filament concepts:
- `schemas/components/*` = layout/composition primitives
- `forms/components/*` = inputs
- `tables/components/columns/*` = table renderers/editors
- `widgets/components/*` = dashboard building blocks

**Best practice**
- Put new features in the correct domain folder.
- Prefer extending an existing component file when the behavior is clearly the same conceptual component (e.g., adding an option to `textarea.js` instead of creating a near-duplicate).

### 3) “Component factories” and configuration-driven behavior
Even without the full source, the file naming and Filament patterns imply:
- Components are configured via options/props.
- Schema primitives (tabs/wizard) orchestrate child components.

**Best practice**
- Add new behavior as an **optional config** with a safe default.
- Avoid changing default behaviors unless required; introduce flags (e.g., `enableX`, `variant`, `mode`) instead.

---

## What “Done” Looks Like (Definition of Done)
A feature is complete when:
1. **Component API is documented** (at minimum: config keys, expected value shape).
2. Works in the target UI context (form/table/widget), including edge cases.
3. No regressions to existing component behavior (manual smoke test).
4. Any feature flags/defaults preserve backward compatibility.
5. Any serialization/parsing is consistent with existing components (strings vs arrays vs objects).
6. Build/runtime errors are absent in the browser console.

---

## Standard Workflows

### Workflow A — Add a New Form Component
Use when introducing a new input type (e.g., “phone input”, “currency”, “date range”, “file list”).

1. **Choose the closest existing component**
   - If it’s text-like: check patterns in `textarea.js`
   - If it’s array-like: check `tags-input.js`
   - If it’s object-like: check `key-value.js`
   - If it’s selection list-like: check `checkbox-list.js`
   - If it’s complex editor-like: check `rich-editor.js`

2. **Define the component contract**
   - Value type: string/number/boolean/array/object
   - Empty state: `null` vs `""` vs `[]` vs `{}`
   - Serialization rules (especially if the schema expects a particular format)

3. **Implement configuration options with safe defaults**
   - Use optional keys, never require new keys for existing usage.
   - Keep names consistent with existing component naming conventions in that folder.

4. **Integrate with schema/state**
   - Ensure change events update the underlying state consistently.
   - Ensure initial value renders correctly.

5. **Manual test matrix**
   - Initial render with empty value
   - Initial render with populated value
   - User edits → state updates
   - Validation (if applicable)
   - Disabled/read-only states (if supported by adjacent components)

6. **Document**
   - Add a short README-style snippet in the feature PR description (or in existing docs location if present in repo) describing config keys and example usage.

---

### Workflow B — Extend an Existing Form Component (Add Feature Flag)
Use when you need “same component, extra behavior” (e.g., textarea auto-resize, tags max count, key-value key validation).

1. Locate the file under `public/js/filament/forms/components/`.
2. Add a **new optional config** (e.g., `maxItems`, `autoResize`, `sanitize`, `allowedKeys`).
3. Ensure the default maintains current behavior.
4. Add guardrails:
   - Validate config types defensively (treat missing/invalid as default).
5. Smoke test:
   - Existing usages still behave identically with the flag absent.

---

### Workflow C — Add/Extend a Table Column Type
Use when adding a new renderer/editor for tables (e.g., currency column, status badge, date formatting, inline select).

1. Start from existing columns:
   - Boolean interactive: `toggle.js`, `checkbox.js`
   - Inline editing: `text-input.js`

2. Decide the column mode:
   - Display-only renderer
   - Inline editable column (needs update handling & optimistic UI concerns)

3. Ensure row-level value access is consistent
   - Confirm how the column reads the row value and writes back (pattern should match `text-input.js` / `toggle.js`).

4. Implement formatting & accessibility
   - Don’t break keyboard navigation and focus patterns used by inline editors.

5. Test:
   - Table renders for many rows
   - Sorting/filtering unaffected (unless the column changes underlying data)
   - Inline edits persist through rerender (or degrade gracefully)

---

### Workflow D — Add Wizard/Tabs Capabilities (Schema Primitives)
Use when implementing multi-step flows or grouped sections.

1. Identify whether this is:
   - Wizard navigation logic (step gating, next/prev behavior) → `wizard.js`
   - Tabs switching, persistence, nested tabs → `tabs.js`

2. Add functionality as config-driven behavior
   - Example capabilities (typical):
     - “Prevent leaving step until valid”
     - “Remember last active tab”
     - “Lazy render tab content”

3. Keep state transitions predictable
   - Avoid implicit state resets unless explicitly configured.

4. Test:
   - Switching steps/tabs preserves entered values
   - Back/forward navigation doesn’t lose state
   - Deeply nested schemas still render

---

## Best Practices (Project-Specific)

### 1) Backward compatibility first
Because these are shared primitives/components, changes can ripple widely.
- Add new config keys rather than changing defaults.
- If you must change behavior, introduce a compatibility mode flag.

### 2) Small diffs in minified modules
Given the minified symbols:
- Avoid sweeping formatting or renaming.
- Keep changes localized to the smallest possible region.

### 3) Preserve directory semantics
Do not place form components under tables, etc. The repo’s structure is the primary discoverability mechanism.

### 4) Value shape consistency
Follow the closest existing component’s approach:
- `tags-input` likely expects arrays of strings/tokens
- `key-value` likely expects an object/map structure
- checkbox/toggle columns expect booleans

Mismatch here is the most common source of silent bugs.

### 5) Defensive configuration handling
Because consumers may pass unexpected values:
- Treat missing config as default
- Coerce/validate config types where feasible
- Fail gracefully (render something usable rather than crashing)

---

## Key Files & Their Purpose (Quick Guide)

### Schemas
- `public/js/filament/schemas/components/wizard.js`
  - Multi-step composition primitive
  - Controls step ordering, navigation, and potentially step-level validation gates

- `public/js/filament/schemas/components/tabs.js`
  - Tabbed composition primitive
  - Controls active tab state and content grouping

### Forms
- `public/js/filament/forms/components/textarea.js`
  - Multiline text input component baseline (good reference for text-ish inputs)

- `public/js/filament/forms/components/tags-input.js`
  - Tag/token input handling arrays and user-driven add/remove flows

- `public/js/filament/forms/components/rich-editor.js`
  - Rich text editor integration (watch for serialization/sanitization patterns)

- `public/js/filament/forms/components/key-value.js`
  - Key/value map editor (object shape, dynamic rows)

- `public/js/filament/forms/components/checkbox-list.js`
  - Multi-select via checkboxes (array-of-values patterns)

### Tables (Columns)
- `public/js/filament/tables/components/columns/toggle.js`
  - Boolean toggle behavior (interactive)

- `public/js/filament/tables/components/columns/text-input.js`
  - Inline editable text behavior (input lifecycle, save/commit pattern)

- `public/js/filament/tables/components/columns/checkbox.js`
  - Checkbox column behavior (boolean display/edit pattern)

---

## Common Feature Scenarios (Recipes)

### Recipe 1 — Add “max items” to tags input
- File: `public/js/filament/forms/components/tags-input.js`
- Approach:
  1. Add config: `maxItems` (number | null)
  2. When adding a tag:
     - If `maxItems` reached → prevent add and optionally show feedback if a pattern exists
  3. Ensure removing tags re-enables add
  4. Default `maxItems = null` → no limit

### Recipe 2 — Add “auto-resize” to textarea
- File: `public/js/filament/forms/components/textarea.js`
- Approach:
  1. Add config: `autoResize` (boolean, default false)
  2. On input change, compute height based on scrollHeight
  3. Ensure disabled/read-only doesn’t break sizing

### Recipe 3 — Add formatted display to text-input table column
- File: `public/js/filament/tables/components/columns/text-input.js`
- Approach:
  1. Add config: `format` (function name reference/string mode) or `formatter` option (if patterns exist)
  2. Apply formatting for display mode but keep raw value for editing
  3. Ensure sorting/filtering uses raw underlying value (unless configured)

---

## QA / Validation Checklist (Manual)
- No console errors on pages using the modified component
- Existing forms/tables using the component still render
- Empty/null values don’t crash the UI
- Interaction works with keyboard (tab/enter/escape as applicable)
- State updates are reflected immediately and persist through rerenders
- Multi-step/tab containers preserve state across navigation

---

## Collaboration / Handoff Notes
When submitting changes:
- Include a brief “API changes” section:
  - New config keys, defaults, and value types
- Include “Where tested” notes:
  - Which page(s)/screen(s), what scenarios
- If behavior differs under a new flag, provide example configs

---

## Guardrails (What Not To Do)
- Don’t rename/reshape exports in these compiled modules unless you understand downstream imports.
- Don’t change default behaviors of widely-used primitives (wizard/tabs) without a compatibility flag.
- Don’t introduce new folders or reorganize existing domain directories for a single feature.

---

## Pointers to Existing Agent Docs
If you need canonical agent definitions and cross-agent standards, check:
- `.context/agents/` (canonical)
- `.context/agents/README.md` (reference index)
