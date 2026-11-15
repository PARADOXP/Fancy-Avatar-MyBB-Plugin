# Fancy Avatar for MyBB

Assigns a generated avatar to every account that does not have a custom one. Three providers are supported: DiceBear (24+ styles), UI Avatars (initials), and Avataaars (cartoon characters). Each avatar is derived from a per-user seed, so the same user keeps the same look.

- **Version:** 1.1.0
- **Codename:** `fancyavatar`
- **Compatibility:** MyBB 1.8.x (`18*`)
- **Author:** PARAD0X — [MyBB profile](https://community.mybb.com/user-134311.html)
- **License:** GPL-3.0 in this repository (the source file header still says MIT)
- **Dependencies:** PluginLibrary 13, bundled as `inc/plugins/pluginlibrary.php`

## Features

- Automatic avatar assignment on registration, including accounts created in the Admin CP.
- Repair pass on login for accounts whose avatar is empty or still the board default.
- User CP box with a live preview, a shuffle button and a "Use This Avatar" button that saves the chosen URL.
- Moderator option in the profile tools menu to assign a fresh avatar to a user they can manage.
- Admin CP page under **Users → Fancy Avatar** with statistics, a batch action for accounts without avatars, and a force action that overwrites everyone.
- Settings for provider, style, size, seed method, border radius, excluded groups and provider-specific parameters.
- Settings change detection through a stored hash, with a refresh pass for existing provider avatars.
- Templates and stylesheet registered through PluginLibrary and removed on deactivation.

## Requirements

| Requirement | Version / note |
| --- | --- |
| MyBB | 1.8.x (`compatibility: 18*`) |
| PHP | 7.4+ (tested on PHP 8.x) |
| PluginLibrary | Bundled — `inc/plugins/pluginlibrary.php` (v13, LGPL-3.0+, © Andreas Klauer) |
| Database | MySQL/MariaDB. Activation runs MySQL-only `ALTER TABLE ... MODIFY` |
| Outbound HTTP | None from the server; avatars are hot-linked from the provider CDN |

No API key is required. The browser loads the avatar directly from DiceBear, UI Avatars or Avataaars.

## Installation

1. Copy this repository over the MyBB root (the directory containing `index.php`).
2. **Admin CP → Configuration → Plugins → Fancy Avatar → Install**, then **Activate**.
3. Open **Configuration → Settings → Fancy Avatar Settings** and select a provider, size and seed method.
4. Optional: **Users → Fancy Avatar** to generate avatars for existing accounts.

### What activation changes

- Registers the `fancyavatar` settings group from `settings.json` (16 settings).
- Registers the 3 templates from `Templates/` and the stylesheet from `Assets/fancyavatar.css`.
- Widens `{TABLE_PREFIX}users.avatar` to `VARCHAR(500) NOT NULL DEFAULT ''`; provider URLs are longer than MyBB's default 255 characters.
- Inserts `{$fancyavatar_option}` after `{$purgespammer}` in `member_profile_modoptions_manageuser`.
- Inserts `{$fancyavatar_ucp_option}` after `{$avatar_remote}` in `usercp_avatar`.
- Inserts a hidden `fancyavatar_settings_hash` row into `settings` and stores the current settings hash.
- Deletes the `templates` datacache entry.

Deactivation removes the templates, stylesheet, template group and the two template edits. Settings, the hidden hash row, the widened column and the avatars already stored on user accounts remain.

Uninstall removes the settings group, templates, stylesheet, template group and the `member_profile_modoptions_manageuser` edit, then rebuilds settings. It does not revert the column width, does not remove the hidden hash row, does not remove the `usercp_avatar` edit and does not clear avatars stored on user accounts.

## Repository layout

```
admin/
└── modules/
    └── user/
        └── fancyavatar.php                 # routing stub required by the user module
inc/
├── languages/
│   └── english/
│       ├── admin/fancyavatar.lang.php
│       └── fancyavatar.lang.php
└── plugins/
    ├── fancyavatar.php                     # bootstrap, metadata, hooks, ACP routing
    ├── pluginlibrary.php
    └── FancyAvatar/
        ├── Admin/fancyavatar.php           # batch/force page
        ├── Assets/fancyavatar.css
        ├── Assets/fancyavatar.js           # shipped but never loaded
        ├── Functions/avatar_functions.php  # URL generation, seeding, batch/refresh
        ├── Functions/hook_functions.php    # hook callbacks and misc actions
        ├── Templates/fancyavatar_mod_options_change_avatar.html
        ├── Templates/fancyavatar_usercp_avatar_option.html
        ├── Templates/fancyavatar_usercp_info.html
        ├── install.php
        ├── settings.json
        └── uninstall.php
```

`admin/modules/user/fancyavatar.php` is an empty guard file. The action handler declares `file => 'fancyavatar.php'`, so the admin router expects that file to exist; the `admin_load` hook normally intercepts the request and loads `inc/plugins/FancyAvatar/Admin/fancyavatar.php` instead.

## Settings

Sixteen settings are registered in the `fancyavatar` group.

| Setting | Default | Purpose |
| --- | --- | --- |
| Enable Fancy Avatar | `1` | Master switch for assignment and the settings hash refresh. |
| Avatar Provider | `dicebear` | `dicebear`, `uiavatars` or `avataaars`. |
| Avatar Size (pixels) | `128` | Provider size and stored `avatardimensions`. |
| Avatar Seed Method | `username` | `username`, `email`, `uid` or `random`. |
| Border Radius (%) | `50` | CSS `border-radius`; 50 is a circle. |
| Exclude User Groups | empty | Usergroups skipped during automatic assignment. |
| Refresh Avatar on Profile View | `0` | Registered but never read by any code path. |
| DiceBear Style | `adventurer` | One of 24 listed DiceBear styles. |
| DiceBear Background Color | `transparent` | Hex colour without `#`, or transparent. Also used as the UI Avatars background. |
| DiceBear Avatar Shape | `default` | `default`, `circle` or `square`. |
| DiceBear Mouth Style | `default` | `default`, `smile`, `laughing`, `nervous`, `sad`, `open`, `serious` or `tongue`. |
| DiceBear Expression Style | `default` | Facial expression for supported styles. |
| UI Avatars Font Color | `default` | Auto contrast or a fixed hex colour. |
| UI Avatars Bold Text | `no` | Adds `bold=true`. |
| UI Avatars Initials Length | `2` | Adds `length` when not `2`. |
| Avataaars Avatar Style | `circle` | `circle` or `transparent`. |

## How it works

### Assignment paths

- **Registration.** `datahandler_user_insert` writes a provider URL into the pending insert data when the submitted avatar is empty. `datahandler_user_insert_end` re-checks the stored row and assigns again only if it is still empty or the board default.
- **Login.** `member_login` checks the account and assigns a new avatar when the stored avatar is empty or the default path.
- **Avatar deletion.** `usercp_do_avatar_end` is intended to restore a generated avatar after the user removes theirs.
- **User CP.** `usercp_avatar_end` renders the preview box. The shuffle and save actions are handled by `misc.php`.
- **Moderators.** `fancyavatar_change` assigns a fresh avatar to a user the moderator can manage.
- **Admin CP.** The Users module page runs a batch pass for default-avatar accounts or a force pass for every account.

### Settings refresh

`pre_output_page` computes an MD5 of 13 provider-related settings and compares it with the `fancyavatar_settings_hash` setting. When the values differ it calls `fancyavatar_refresh_all()`, which selects users whose avatar contains a provider domain and regenerates each URL. The Admin CP batch and force actions call the hash update directly.

### Provider URLs

- **DiceBear** — `https://api.dicebear.com/7.x/{style}/svg?seed={seed}&size={size}` plus optional `backgroundColor`, `shape`, `mouth` and `expression` parameters, each gated by a style whitelist.
- **UI Avatars** — `https://ui-avatars.com/api/?name={seed}&size={size}&background={colour|random}&rounded=true&format=svg` plus optional `color`, `bold` and `length`.
- **Avataaars** — `https://avataaars.io/?...` with ten attributes selected from fixed arrays by a 31-bit hash of the seed.

Seed methods:

| Method | Value |
| --- | --- |
| `username` | The username. |
| `email` | The email address, falling back to the username. |
| `uid` | The numeric user id, falling back to the username. |
| `random` | A new `uniqid()` value on every call. |

## Hooks

| Hook | Handler | Effect |
| --- | --- | --- |
| `global_start` | `fancyavatar_global_start` | Loads the front-end language file when enabled. |
| `pre_output_page` | `fancyavatar_pre_output_page` | Compares the settings hash and refreshes provider avatars. |
| `datahandler_user_insert` | `fancyavatar_new_user_insert` | Adds an avatar to the pending user insert. |
| `datahandler_user_insert_end` | `fancyavatar_new_user_insert_end` | Post-insert fallback assignment. |
| `usercp_do_avatar_end` | `fancyavatar_avatar_deleted` | Intended to reassign after avatar removal. |
| `member_profile_start` | `fancyavatar_profile_mod_option` | Builds the moderator "Change to Fancy Avatar" item. |
| `usercp_profile_end` | `fancyavatar_usercp_profile` | Dead code; builds an unused local variable. |
| `usercp_avatar_end` | `fancyavatar_usercp_avatar` | Renders the User CP preview and shuffle box. |
| `postbit` | `fancyavatar_postbit` | Intended postbit fallback; runs too late to affect `useravatar`. |
| `postbit_author_user` | `fancyavatar_postbit_author_user` | Not a MyBB hook; never fires. |
| `member_login` | `fancyavatar_member_login` | Assigns an avatar on login when the account has none. |
| `misc_start` | `fancyavatar_misc_handler` | Handles `fancyavatar_change`, `fancyavatar_set` and `fancyavatar_batch`. |
| `admin_config_settings_manage`, `_change`, `_start` | `fancyavatar_admin_lang` | Loads the language file on the settings pages. |
| `admin_user_menu` | `fancyavatar_admin_menu` | Adds **Fancy Avatar** under Users. |
| `admin_user_action_handler` | `fancyavatar_admin_action_handler` | Declares the `fancyavatar` action with file `fancyavatar.php`. |
| `admin_load` | `fancyavatar_admin_load` | Routes `user-fancyavatar` to the plugin admin page and exits. |

## Requests

| Request | Method | Handler |
| --- | --- | --- |
| `misc.php?action=fancyavatar_change&uid=N&my_post_key=...` | GET | Assign a new avatar to a managed user |
| `misc.php?action=fancyavatar_set` | POST (`my_post_key`) | Save the previewed URL for the current user |
| `misc.php?action=fancyavatar_batch` | POST (`my_post_key`, superadmin) | Batch assign from the front end |
| `index.php?module=user-fancyavatar` | GET | Statistics and action buttons |
| `index.php?module=user-fancyavatar&action=run` | POST | Batch assign |
| `index.php?module=user-fancyavatar&action=run_force` | POST | Force assign to every user |

## Database

The plugin adds no table. It changes one column and one settings row:

- `{TABLE_PREFIX}users.avatar` becomes `VARCHAR(500) NOT NULL DEFAULT ''`.
- `{TABLE_PREFIX}settings` receives `fancyavatar_settings_hash` with `gid = 0`, an empty title/description and the current hash as its value.

Provider avatars are stored in `users.avatar` with `avatartype = remote` and `avatardimensions = {size}|{size}`. There is no separate table and no record of which provider produced a given URL beyond the domain substring.

## Known defects and limitations

These are properties of the current source, documented rather than fixed.

### Security and authorisation

1. **Any URL containing a provider domain is accepted as an avatar.** `fancyavatar_is_provider_url()` only checks `strpos()` for `dicebear.com`, `ui-avatars.com` or `avataaars.io`. `misc.php?action=fancyavatar_set` therefore accepts `https://attacker.example/?dicebear.com` and stores it as the current user's remote avatar. That gives every viewer's browser a tracking beacon and bypasses any avatar moderation policy.
2. **Avatar assignment through a GET link exposes the post key.** The moderator option is an `<a href="misc.php?action=fancyavatar_change&uid=N&my_post_key=...">`. The handler performs a state change on GET, and the post key appears in browser history, referrer headers, proxy logs and any embedded third-party resource on the following page.
3. **The Admin CP page declares no permission.** The plugin never extends `user_admin_permissions`. `check_admin_permissions()` only denies an action when the module declares a permission for it, so any administrator who can reach the Users module can run the batch and force actions.
4. **The force action is destructive and irreversible.** `fancyavatar_force_update()` updates every row in `users`, including accounts with uploaded avatars, accounts in excluded groups and administrators. There is no backup, no undo and no server-side confirmation beyond the button in the form.
5. **The exclusion list is concatenated into SQL.** `fancyavatar_batch_update()` appends `usergroup NOT IN ({$db->escape_string($exclude_groups)})`. `escape_string()` does not validate integers, so a modified setting containing `0) OR 1=1 --` changes the query. Only administrators can edit settings, but the value is never checked as a comma-separated id list.
6. **Usernames and language strings are interpolated into inline JavaScript without escaping.** `fancyavatar_usercp_avatar_option.html` emits `var fancyavatarUsername = '{$mybb->user['username']}';` and `confirm('{$lang->fancyavatar_ucp_confirm}')`. A username or translation containing an apostrophe, backslash or newline breaks the script; a board that allows quotes in usernames exposes a script-injection sink.
7. **Seed selection leaks identity data to the provider.** `email` puts the address in the query string; the browser then sends it to the avatar CDN and it remains in the page source and any logs on the provider side. `uid` exposes the numeric account id the same way.
8. **Guests can reach `fancyavatar_set`.** The handler does not check `$mybb->user['uid']` before running the update; for a guest it updates `uid = 0` (no row) and redirects to the User CP, which then asks for a login. Not exploitable on its own, but the endpoint should reject anonymous requests explicitly.

### Functional correctness

9. **Avatar removal never restores a generated avatar.** `fancyavatar_avatar_deleted()` returns unless `$mybb->input['action'] == 'do_deleteavatar'`. MyBB 1.8 removes an avatar through `action=do_avatar` with a `remove` submit field; there is no `do_deleteavatar` action. The reassignment path is dead.
10. **The postbit fallback does not work.** `fancyavatar_postbit()` changes `$post['avatar']`, but `build_postbit()` builds `$post['useravatar']` from `$post['avatar']` *before* the `postbit` hook fires. The default `postbit` template uses `{$post['useravatar']}`, so changing `avatar` has no visible effect.
11. **`postbit_author_user` is a template, not a hook.** `fancyavatar_postbit_author_user()` is registered on a name that MyBB never runs as a hook, so the second fallback never fires at all.
12. **The profile page gets no fallback avatar.** The plugin only adds a moderator option on `member_profile_start`; it never replaces `$memprofile['avatar']`. Accounts with an empty avatar show the board default on their profile until they log in or an admin runs the batch.
13. **`refresh_on_view` is dead.** The setting is registered in `settings.json` and described in both language files, but no PHP or JavaScript reads `fancyavatar_refresh_on_view`.
14. **The settings hash can get stuck and trigger a refresh on every page.** `fancyavatar_refresh_all()` calls `fancyavatar_update_settings_hash()` only when `$updated > 0`. If a settings change occurs while no user has a provider avatar, the stored hash never matches, so every front-end page runs the `SELECT ... WHERE avatar LIKE '%dicebear.com%' OR ...` scan again.
15. **The refresh pass is synchronous and unbounded.** When the hash differs, `fancyavatar_refresh_all()` loads every matching user and issues one `UPDATE` per row in the page request. A large board can hit `max_execution_time` or a database timeout, and the request that triggered the refresh is the one that pays the cost.
16. **Provider matching is substring-based.** `fancyavatar_provider_where()` uses `avatar LIKE '%dicebear.com%'` and the other domains with no host parsing. A custom avatar such as `https://example.com/?ref=dicebear.com` matches and is overwritten by the next refresh.
17. **Batch exclusion has an SQL precedence bug.** The where clause is built as `avatar = '' OR avatar IS NULL OR avatar = '{default}' AND usergroup NOT IN (...)`. `AND` binds tighter than `OR`, so excluded groups with an empty avatar are still updated. Only accounts whose avatar equals the board default are filtered.
18. **Batch and force run one query per user.** Both loops select all users and call `update_query()` per row. There is no chunking, transaction or progress reporting, and a timeout leaves a partial update with no summary.
19. **The front-end batch action forgets the hash update.** `misc.php?action=fancyavatar_batch` calls `fancyavatar_batch_update()` and redirects without calling `fancyavatar_update_settings_hash()`, so the next page load sees a mismatch and runs `refresh_all()` over the same users again.
20. **Registration seeding uses a non-existent property.** `fancyavatar_new_user_insert()` reads `$userhandler->inserts['uid']`, but `UserDataHandler` has no `inserts` property; the value is always missing and `$uid` is 0. With the `uid` seed method, new registrations are generated from the username instead of the account id.
21. **The post-insert fallback usually does nothing.** Because `datahandler_user_insert` already writes an avatar into `user_insert_data`, the stored row is non-empty when `datahandler_user_insert_end` runs, so the second assignment is skipped. The fallback only fires when the pre-insert path failed.
22. **Users cannot permanently remove an avatar.** With the plugin enabled, `member_login` reassigns whenever the avatar is empty or the board default. Removing an avatar sticks only until the next login (and defect 9 means it is not restored immediately).
23. **The User CP shuffle does not use the configured parameters.** The inline DiceBear generator builds `.../svg?seed=...&size=...` and omits `backgroundColor`, `shape`, `mouth` and `expression`. A shuffled avatar saved from the User CP can therefore look different from one generated on registration with the same settings.
24. **The User CP shuffle breaks UI Avatars initials.** `fancyavatarShuffle()` generates a random 12-character string and passes it as the `name` parameter. The preview and any saved avatar show random letters instead of the user's initials; the `fancyavatarUsername` JavaScript variable is declared but unused.
25. **UI Avatars receives the wrong name under non-username seeds.** When the seed method is `email` or `uid`, `fancyavatar_generate_uiavatars_url()` passes the email address or numeric id as `name`, so the initials are derived from the email or the digits rather than the username.
26. **The UI Avatars background is controlled by a DiceBear setting.** `fancyavatar_generate_uiavatars_url()` reads `fancyavatar_bgcolor`, whose title and description say it only applies to DiceBear. There is no separate UI Avatars background setting, and `transparent` silently becomes `background=random`.
27. **Three mouth options are silently ignored.** The settings offer `laughing`, `nervous` and `open`, but `$valid_mouth_values` contains only `smile`, `sad`, `serious` and `tongue`. The other three never reach the URL.
28. **The `shape` parameter is applied by style whitelist only.** Any value other than `default` is appended for a fixed list of DiceBear styles; the plugin does not verify that the selected style actually supports `shape` in DiceBear 7.x.
29. **Avataaars depends on a single external host.** `https://avataaars.io/` is the only source; there is no local fallback, timeout handling or alternate mirror. If the host is unavailable, affected avatars render broken.
30. **The `random` seed is time-based and unstable.** `fancyavatar_get_seed()` uses `md5(uniqid(...))`, which is predictable and produces a new value on every call. Unassigned users get a different avatar on each render, and the value stored at registration cannot be reproduced.
31. **The Avataaars hash is platform-dependent on 32-bit PHP.** `fancyavatar_hash_seed()` shifts and masks a 31-bit integer. On 32-bit builds the intermediate `($hash << 5) + $hash` can overflow into a float before the mask, so the same seed can map to different attributes after moving between 32-bit and 64-bit PHP.
32. **`fancyavatar_usercp_profile()` is dead code.** It assigns `$fancyavatar_info` as a local variable, but no template edit adds `{$fancyavatar_info}` to `usercp_profile`. The template `fancyavatar_usercp_info.html` also prints `{$radius}`, which the function never sets.
33. **`fancyavatar_get_avatar_html()` and `fancyavatar_get_dicebear_styles()` are unused.** No caller exists for either helper. The HTML helper also concatenates `$class` into the `class` attribute without escaping.
34. **`fancyavatar_get_avatar_url()` performs two queries for one user.** It selects the user row, then calls `fancyavatar_has_default_avatar()`, which selects the same row again. It is only used by the unused HTML helper, so the cost is latent.
35. **`fancyavatar_assign_avatar()` always reports success.** It ignores the return value of `update_query()` and returns `true` even when the uid does not exist or the update fails.

### Data integrity and lifecycle

36. **Re-activation duplicates template variables.** `find_replace_templatesets()` replaces `{$purgespammer}` with `{$purgespammer}{$fancyavatar_option}`. Running activation again still finds `{$purgespammer}` and appends a second `{$fancyavatar_option}`. The same happens to `{$avatar_remote}` and `{$fancyavatar_ucp_option}`.
37. **Uninstall leaves the User CP template edit behind.** `fancyavatar_remove_template_edits()` removes only `{$fancyavatar_option}`. `{$fancyavatar_ucp_option}` remains in `usercp_avatar`, so an uninstalled board still renders an empty variable and a reinstall can duplicate the block.
38. **Uninstall leaves the hidden settings row.** `fancyavatar_settings_hash` is inserted with `gid = 0`, while `$PL->settings_delete('fancyavatar')` deletes only rows belonging to the group. The hash row survives every uninstall.
39. **Deactivation leaves stored avatars and settings.** Removing the templates and stylesheet does not clear `users.avatar` or the settings group. Provider avatars stay hotlinked on posts after deactivation, with no UI to regenerate or remove them.
40. **The avatar column is widened on every activation and never reverted.** `ALTER TABLE users MODIFY avatar VARCHAR(500)` runs each time the plugin is activated, even if the column is already 500 characters, and uninstall does not restore the original width.
41. **`install.php` does not install anything.** `fancyavatar_install_run()` only loads PluginLibrary. The settings group, templates, stylesheet, column change and hash row are all created in `fancyavatar_activate_run()`. `fancyavatar_is_installed()` checks the settings group, so the plugin is reported uninstalled between Install and Activate.
42. **The schema change is MySQL-only.** `MODIFY avatar VARCHAR(500)` is MySQL syntax. PostgreSQL and SQLite boards fail activation, and there is no schema version or upgrade path.
43. **Template edits fail silently when the anchors are missing.** Both `find_replace_templatesets()` calls assume the stock `{$purgespammer}` and `{$avatar_remote}` markers. A custom theme that removed either marker activates without the User CP box or the moderator option, and nothing reports the failure.
44. **The `users.avatar` width is the only storage change; existing custom URLs are not checked.** A board that already stores URLs longer than 255 characters can have them truncated before activation, and the plugin cannot recover the original values.
45. **Batch statistics are full-table counts with no index use.** The admin page runs `COUNT(*)` over `users` three times, including a `LIKE '%domain%'` scan, on every page view.

### Code quality and integration

46. **`Assets/fancyavatar.js` is never loaded.** Only the CSS is registered through PluginLibrary. The JavaScript file defines an unused `FancyAvatar` object, and its URL builder supports DiceBear only, so it is already out of date with the PHP and inline code.
47. **The real User CP logic is 150 lines of inline JavaScript inside a template.** It reimplements seeding, hashing, all three URL builders and the form submission in `fancyavatar_usercp_avatar_option.html`, while the shipped asset is dead.
48. **Three independent URL builders exist.** `Functions/avatar_functions.php`, the inline User CP script and `Assets/fancyavatar.js` each construct provider URLs with slightly different parameter sets, which is why the shuffle output differs from the server output.
49. **The stylesheet contains unused classes.** `.fancyavatar-profile-avatar`, `.fancyavatar-admin-preview`, `.fancyavatar-style-guide`, `.fancyavatar-loading`, `.fancyavatar-usercp-notice`, `.fancyavatar-postbit` and `.fancyavatar-profile` are not referenced by any template, and the admin page uses a `fancyavatar_batch_box` class that the stylesheet does not define.
50. **Three templates are fetched without `$templatelist` entries.** `$templates->render()` loads each one on first use; none of the plugin templates is declared on the pages that need it.
51. **The admin page is hand-built HTML.** It uses `echo "<br />"`, raw `<div>` wrappers and PHP string concatenation instead of templates or the ACP form API, and it does not use a language file for the two section headings (`fancyavatar_batch_title` is defined but never printed).
52. **Language files and settings JSON disagree.** The `setting_fancyavatar_*` language entries duplicate the titles and descriptions in `settings.json`; PluginLibrary stores the JSON values, so the language entries are never used. The front-end file also carries admin-only strings, and the admin file lacks some front-end strings.
53. **The plugin metadata is inconsistent.** `_info()` has no `license` key, the file docblock declares MIT, and this repository is GPL-3.0.
54. **`fancyavatar_provider_where()` returns an unqualified `avatar` column.** It works for the current single-table queries but becomes ambiguous in any future join that also has an `avatar` column.
55. **The admin bridge file was missing from the published tree.** The action handler requires `admin/modules/user/fancyavatar.php`; without it, an `admin_load` routing failure becomes a fatal `require` error instead of a clean error page. This repository includes the guard file.

## Compatibility notes

- Tested against MyBB 1.8.41 and the default theme. The template edits assume the stock `member_profile_modoptions_manageuser` and `usercp_avatar` markup.
- PluginLibrary 13 is bundled unmodified. Its header declares LGPL-3.0; the GPL-3.0 license in this repository covers the Fancy Avatar code only.
- Provider availability, rate limits and terms of service are outside the plugin's control. DiceBear, UI Avatars and Avataaars are third-party services.
- The `fancyavatar_*` function names are global. Another plugin that declares any of them causes a fatal redeclare.

## License

GPL-3.0. See [LICENSE](LICENSE).
