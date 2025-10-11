# TaskLinkPolicies (Kanboard Plugin)

**v0.3.0** – Dedicated **Project Policies** tab, sidebar hook, logging, and robust policy checks.

- Adds its own tab in Project settings via `template:project:sidebar` (see Kanboard hooks list).
- Routes: `/project/:project_id/policies` (index), `/project/:project_id/tasklink-policies/save` (save).
- ACL: Only Project Managers can open/save.
- Logs: All checks write to `data/debug.log` with prefix `TLP:` (enable DEBUG in config to see detailed debug lines).

