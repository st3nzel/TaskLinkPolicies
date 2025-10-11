# TaskLinkPolicies (Kanboard Plugin)

Enforce modern Kanban dependency logic based on **internal task links**:

- relates to
- blocks / is blocked by
- duplicates / is duplicated by
- is a child of / is a parent of
- targets milestone / is a milestone of
- fixes / is fixed by

## Features

- Prevent a blocked task from leaving **Backlog** until all blockers left Backlog
- Prevent closing a blocked task until all blockers are closed
- Parent tasks require all children to be closed before closing
- Milestones require all target tasks to be closed before closing
- Optional duplicate policies: allow / disallow both active / auto-close duplicates together
- Optional enforcement for “is fixed by”
- Per-project configuration under **Project → Integrations**
- Works with customized boards; Backlog = first column by default or specify a custom column id

## Install

1. Copy the folder to `plugins/TaskLinkPolicies` (folder is case-sensitive)
2. Go to *Project → Settings → Integrations* to configure rules

## Notes

- Implemented by overriding `taskModel` and `taskPositionModel` services in the DI container (supported pattern in Kanboard).
- No database schema changes.
- MIT License.
