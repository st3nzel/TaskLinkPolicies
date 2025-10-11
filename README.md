# TaskLinkPolicies (Kanboard Plugin)

**v0.2.0** – fixes: correct route plugin name & access-map; corrected metadata usage in template; minor robustness improvements.

- Project-level settings appear under **Project → Integrations** (template hook).
- Saving is restricted to **Project Managers** (authorization map).
- Enforcement via model overrides for `TaskModel::close()` and `TaskPositionModel::movePosition()`.

Docs:
- Plugin registration: https://docs.kanboard.org/v1/plugins/registration/
- Routes: https://docs.kanboard.org/v1/plugins/routes/
- Authorization: https://docs.kanboard.org/v1/plugins/authorization/
- Default link labels: https://docs.kanboard.org/v1/user/tasks/
