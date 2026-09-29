# ADMIN_PANEL.md

> **Stack note.** The admin panel is **first-party Laravel code** — Blade views,
> controller actions, form requests, policies, and a small amount of Alpine/JS —
> with **no admin framework package** installed. Earlier revisions of this
> document specified Filament; §1 records why that changed, §15 maps every
> capability the old plan relied on onto the file that replaces it, and nothing
> else in the doc set moves: the same data model (`DATABASE.md`), the same
> navigation, the same workflows, the same URLs under `/admin`, and the same
> requirements in `SECURITY.md` all still apply. Where this document previously
> described package behaviour (auth pipeline, resource forms, upload fields,
> widgets), it now names our own classes and views.

## 1. Why the panel is custom

1. **The panel is the only authenticated surface, so it is the only place an
   unread dependency can hurt.** Every route, middleware, form and template on
   `/admin` is code in this repository, reviewable end to end. A CMS package
   adds a large transitive tree (its own HTTP kernel integration, Blade layer,
   asset pipeline, auth pipeline, JS bundle) to exactly the area of the app
   where the smallest bug is a content-takeover rather than a styling glitch.
2. **No upgrade treadmill on the publishing tool.** Panel packages ship major
   versions that rewrite APIs — the previous revision of this document was
   written against one such rewrite. A personal CMS should be something the
   journalist (and whoever maintains it later) can leave untouched for two
   years. A custom panel built on Laravel's stable core has no third-party
   release cadence in it.
3. **The generic scaffolding was never where the work was.** The genuinely hard
   parts of this admin are project-specific and were going to be custom under
   any package: the type-aware content form (§7), the SSRF-guarded media import
   (§9), the singleton author profile (§12), the section/beat/topic taxonomies
   (§10), and the publishing lifecycle (§11). What a package provides on top is
   a table component, a form-field partial set, an upload field, a login page
   and a sidebar — roughly a dozen small Blade components here (§4).
4. **Size.** The whole admin is ~12 screens over a design system that already
   exists for the public site (§2). That is below the scale at which a CRUD
   framework pays for itself.

**What this costs, stated plainly.** We write the table component, the field
partials, the media picker, the login screen, the role middleware, the passcode
rotation command and the dashboard queries ourselves. What we do **not** write
is everything expensive but generic: validation, Eloquent, migrations,
pagination, file storage, sessions, signed URLs, rate limiting, mail and the
queue — all still stock Laravel.

**What we give up.** No plugin ecosystem (irrelevant here — the panel has no
plugins), no community-built extras, and no free upgrades of the panel layer
(which is the point of §1.2: there is nothing to upgrade). Multi-tenancy,
widget galleries, and the other things a framework sells are not features this
site has.

## 2. Stack and front-end assets

- **Blade, server-rendered forms, POST → redirect → GET.** No SPA, no JSON API.
  The only `fetch()` calls in the panel are the three small endpoints in §3
  (media-picker search, drag-to-reorder, slug availability); every other
  interaction is a form post, so the panel works with JS half-broken and there
  is no client-side state to desynchronise from the database.
- **Tailwind, second Vite entry.** `resources/css/admin.css` is built from the
  same tokens as the public `app.css` (`DESIGN_SYSTEM.md` §3: ink `#1A1A1A`,
  paper `#FAF9F6`, accent `#8C1D18`, hairline `#E4E1D9`) so sign-in and the
  public site read as one product, with admin-specific density: 13–14px UI
  text, 36–40px row heights, tables over card grids, no decorative imagery.
  Inter throughout (per `DESIGN_SYSTEM.md` §2 the UI face is Inter; the serif
  display face is for the public site's headlines, not for form labels).
- **Alpine.js** (already the public stack's light-interactivity tool) for
  drawers, modals, confirm dialogs, conditional field groups and the
  unsaved-changes warning. **Plain ES modules** for the two things Alpine is
  bad at: the drag-to-reorder list and the media picker.
- **Everything is bundled by Vite; nothing loads from a CDN.** That keeps the
  CSP in `SECURITY.md` §19 at `self` for script/style/font, with no exceptions
  carved out for an admin widget. No CDN also means the panel does not phone
  home from the login page.
- **Rich text: a self-hosted editor bundle** (Tiptap is the recommendation;
  any editor that bundles locally is acceptable) for the article body only.
  The editor is **UX, not a security boundary** — the server-side sanitizer in
  §7 runs on every save regardless of what the client claimed to send.
- **The panel is closed to indexers**: `noindex, nofollow` headers on `/admin`,
  and `/admin` disallowed in `robots.txt` (already required by `SEO.md` §9).

## 3. Routes

`routes/admin.php`, loaded in `bootstrap/app.php` behind prefix `admin`
and name prefix `admin.`, with the `web` middleware group plus
`EnsureUserHasRole` (§14).

```php
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest-only
    Route::middleware('guest')->group(function () {
        Route::get('login',  [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store']);
    });

    // Authenticated admin surface
    Route::middleware(['auth', 'role:owner'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        // …the table below
    });
});
```

| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/admin/login` | `admin.login` | `Admin\Auth\LoginController@create` |
| POST | `/admin/login` | — | `Admin\Auth\LoginController@store` |
| POST | `/admin/logout` | `admin.logout` | `Admin\Auth\LoginController@destroy` |
| GET | `/admin` | `admin.dashboard` | `Admin\DashboardController@index` |
| GET | `/admin/content` | `admin.content.index` | `Admin\ContentItemController@index` |
| GET | `/admin/content/create` | `admin.content.create` | `…@create` |
| POST | `/admin/content` | `admin.content.store` | `…@store` |
| GET | `/admin/content/{contentItem}/edit` | `admin.content.edit` | `…@edit` |
| PUT | `/admin/content/{contentItem}` | `admin.content.update` | `…@update` |
| DELETE | `/admin/content/{contentItem}` | `admin.content.destroy` | `…@destroy` |
| POST | `/admin/content/bulk` | `admin.content.bulk` | `…@bulk` |
| GET | `/admin/content/slug-check` | `admin.content.slug-check` | `…@slugCheck` *(JSON)* |
| GET | `/admin/media` | `admin.media.index` | `Admin\MediaController@index` |
| GET | `/admin/media/pick` | `admin.media.pick` | `Admin\MediaController@pick` *(JSON, picker search)* |
| POST | `/admin/media` | `admin.media.store` | `…@store` (upload) |
| GET | `/admin/media/{media}/edit` | `admin.media.edit` | `…@edit` (alt text, caption) |
| PUT | `/admin/media/{media}` | `admin.media.update` | `…@update` |
| DELETE | `/admin/media/{media}` | `admin.media.destroy` | `…@destroy` |
| POST | `/admin/media/import` | `admin.media.import` | `Admin\MediaImportController@store` (URL import, §9) |
| GET | `/admin/contacts` | `admin.contacts.index` | `Admin\ContactMessageController@index` |
| GET | `/admin/contacts/{message}` | `admin.contacts.show` | `…@show` (marks read) |
| DELETE | `/admin/contacts/{message}` | `admin.contacts.destroy` | `…@destroy` |
| GET | `/admin/redirects` | `admin.redirects.index` | `Admin\RedirectController@index` |
| GET | `/admin/redirects/create` | `admin.redirects.create` | `…@create` |
| POST | `/admin/redirects` | `admin.redirects.store` | `…@store` |
| GET | `/admin/redirects/{redirect}/edit` | `admin.redirects.edit` | `…@edit` |
| PUT | `/admin/redirects/{redirect}` | `admin.redirects.update` | `…@update` |
| DELETE | `/admin/redirects/{redirect}` | `admin.redirects.destroy` | `…@destroy` |
| GET | `/admin/settings` | `admin.settings.edit` | `Admin\SettingController@edit` |
| PUT | `/admin/settings` | `admin.settings.update` | `…@update` |
| GET | `/admin/activity` | `admin.activity.index` | `Admin\ActivityLogController@index` |
| GET | `/admin/profile` | `admin.profile.edit` | `Admin\ProfileController@edit` (account) |
| PUT | `/admin/profile` | `admin.profile.update` | `…@update` |
| PUT | `/admin/profile/passcode` | `admin.profile.passcode` | `…@updatePasscode` |
| GET | `/admin/journalist-profile/{journalistProfile}/edit` | `admin.journalist-profile.edit` | `Admin\JournalistProfileController@edit` |
| PUT | `/admin/journalist-profile/{journalistProfile}` | `admin.journalist-profile.update` | `…@update` |

The four registry screens (`categories`, `tags`, `topics`, `publications`) and
the three profile sub-resources (`career-history`, `education`, `awards`) each
repeat the same six-action shape (`index`, `create`, `store`, `edit`, `update`,
`destroy`) under their own controller, e.g. `admin.categories.*` →
`Admin\CategoryController`. `topics` additionally carries a featured image,
`publications` a logo and website URL, `categories` a description and sort
order, and the three sub-resources a `reorder` endpoint:

| Method | URI | Name |
|---|---|---|
| POST | `/admin/categories/reorder` | `admin.categories.reorder` |
| POST | `/admin/tags/reorder` | `admin.tags.reorder` |
| POST | `/admin/topics/reorder` | `admin.topics.reorder` |
| POST | `/admin/publications/reorder` | `admin.publications.reorder` |
| POST | `/admin/career-history/reorder` | `admin.career-history.reorder` |
| POST | `/admin/education/reorder` | `admin.education.reorder` |
| POST | `/admin/awards/reorder` | `admin.awards.reorder` |

Two deliberate route-shape decisions, carried over from the previous revision
because they are correctness rules, not package conveniences:

- **The author profile is edit-only.** There is no `index` and no `create`
  route for `journalist-profile` — so there is **no URL on which a second
  profile can be made**, and no "New" affordance anywhere in the UI (§12).
- **The three profile sub-resources do have `create` routes**, because career
  entries, degrees and awards are genuinely plural. Their `create` form takes
  the profile from `JournalistProfile::current()`; the profile is never a
  user-editable select.

**Preview lives outside the admin group.** Draft preview is a signed URL —
`URL::temporarySignedRoute('preview.content', now()->addHours(6), ['contentItem' => $item])`
— served by `PreviewController@content` with the `signed` middleware, mounted
at `/preview/content/{contentItem}`. It renders the *public* detail view, so
the journalist sees the real design, and it is shareable with a source or a
copy-editor without handing over an admin session. Everything not published
404s on the public route in the normal way (`DEPLOYMENT.md` smoke test).

## 4. What We Build, and Where It Lives

```
app/
  Http/
    Controllers/Admin/
      Auth/LoginController.php
      DashboardController.php
      ContentItemController.php
      CategoryController.php   TagController.php
      TopicController.php      PublicationController.php
      MediaController.php      MediaImportController.php
      JournalistProfileController.php
      CareerHistoryController.php  EducationController.php  AwardController.php
      ContactMessageController.php
      RedirectController.php   SettingController.php
      ActivityLogController.php
      ProfileController.php    PreviewController.php
    Middleware/EnsureUserHasRole.php
    Middleware/RedirectLegacyUrls.php
    Requests/Admin/
      StoreContentItemRequest.php   UpdateContentItemRequest.php
      BulkContentActionRequest.php
      StoreMediaRequest.php         ImportMediaRequest.php
      UpdateProfileRequest.php      UpdatePasscodeRequest.php
      …one Store/Update pair per registry resource
  Policies/
    ContentItemPolicy.php  MediaPolicy.php  …  (one per model; §14)
  Support/
    AdminNavigation.php    HtmlSanitizer.php   PasscodeGuard.php
    Slug.php               SecureUpload.php    SafeRemoteImage.php
    ReadingTime.php        MediaUsage.php
  Console/Commands/
    RotateAdminPasscode.php     PublishScheduledContent.php
    BackupDatabase.php          RestoreDatabase.php   (per DEPLOYMENT.md)
  Observers/
    ContentItemObserver.php     …  (activity log, §13)

resources/
  views/admin/
    layout.blade.php        partials/sidebar.blade.php  partials/topbar.blade.php
    partials/flash.blade.php  partials/field-error.blade.php
    auth/login.blade.php
    dashboard/index.blade.php
    content/{index,create,edit,_form}.blade.php
    {categories,tags,topics,publications}/{index,create,edit,_form}.blade.php
    media/{index,edit}.blade.php
    messages/{index,show}.blade.php
    {career-history,education,awards}/{index,create,edit,_form}.blade.php
    journalist-profile/edit.blade.php
    profile/edit.blade.php
    redirects/{index,create,edit,_form}.blade.php
    settings/edit.blade.php
    activity/index.blade.php
  views/components/admin/     (Blade components, §below)
  css/admin.css              js/admin.js
  js/modules/{media-picker,reorder,slug-check}.js
routes/admin.php
```

**The shared Blade components** — this is the list that stands in for a
package's UI layer, and it is deliberately short:

| Component | Used by | Notes |
|---|---|---|
| `<x-admin.layout>` | every screen | sidebar + topbar + flash region; the page shell |
| `<x-admin.data-table>` | every index | §8 |
| `<x-admin.field.text>` / `.textarea` / `.select` / `.checkbox` / `.toggle` / `.datetime` | every form | label, hint, error slot, required marker, `old()` repopulation |
| `<x-admin.field.rich-text>` | content body | mounts the editor, posts to a textarea |
| `<x-admin.field.group>` | content form | Alpine-gated conditional group |
| `<x-admin.media-picker>` | every image field | §9 |
| `<x-admin.filter-bar>` | index screens | chips + selects, built from the query string |
| `<x-admin.badge>` | everywhere | status / type / source badges |
| `<x-admin.empty-state>` | every index | message + primary action |
| `<x-admin.confirm>` | destructive actions | Alpine confirm dialog with the object's name in the copy |

`AdminNavigation` is a single PHP array consumed by
`partials/sidebar.blade.php`; the desktop sidebar and the mobile drawer render
from it, so the two cannot drift — the same rule `FRONTEND.md` §3 applies to
the public header. Nav badges (draft count, unread messages) come from one
cached grouped query, not a query per item.

## 5. Admin Navigation

```
Dashboard

Content
├── All Content (filterable by type/status/source)
├── News Articles        ┐
├── Investigations       │  filtered views of /admin/content — links, not screens
├── Interviews           │  (/admin/content?type=…)
├── Opinions / Analysis  │
├── Multimedia           ┘
├── Categories
├── Tags
├── Topics
└── Publications

Journalist
├── Profile
├── Career History
├── Education
└── Awards

Media
└── Media Library

Messages (Contact submissions)

SEO
├── Redirects
└── Global SEO Defaults

Settings

Activity
```

The type-scoped entries are query-string links into the one content screen.
There is exactly one content controller, one form partial and one validation
class for all seven content types — conditional fields, not parallel screens
(`DATABASE.md` §1).

## 6. Dashboard

`DashboardController@index` composes four cheap reads and a quick action; each
is a partial so the page renders even if one query becomes slow:

- **Counts by status** — one `selectRaw('status, count(*) as n')->groupBy('status')`
  aggregate: Draft / Scheduled / Published, each linking to the filtered list.
- **Recently published** — 5 newest published items, straight to their edit
  screens.
- **Upcoming scheduled** — 5 nearest `published_at` in the future, with the
  date rendered in `Asia/Dhaka` and a clear "scheduled, not yet visible" state.
- **Unread messages** — count, linking to the inbox.
- **Quick action** — "New content" opens `/admin/content/create`; the type
  selector on that form is the first field (§7).

Counts are cached for 60 seconds and flushed on content save. No chart library
is loaded for this — four numbers do not need one.

## 7. The Content Form

One partial, `content/_form.blade.php`, used by create and edit, organised in
sections so the type-aware parts read as a narrative rather than a wall of
inputs:

1. **Identity** — title, slug, content type, source type.
2. **Summary** — the dek/summary (auto-derived meta description source).
3. **Where it lives** — for `external`: publication, original URL, published
   date, optional byline note; for `internal`: rich-text body, reading time
   (derived, overridable), featured image.
4. **Classification** — one category, tags (multi-select with inline create),
   topics (multi-select with inline create).
5. **Related content** — search-and-attach picker writing to the
   `related_content` pivot.
6. **SEO** — meta title, meta description, OG image (media picker), canonical
   override.
7. **Publishing** — status, `published_at`, featured toggle.

**Conditional fields are convenience; the request class is the authority.**
Groups are shown/hidden by Alpine from the two selects, and hidden inputs are
`disabled` so nothing half-relevant is submitted by accident. But every rule
is enforced again server-side, because a hand-crafted POST never ran the
JavaScript:

| Rule | Enforced in |
|---|---|
| `external` requires `publication_id` + valid absolute `external_url`; body must be null | `Store/UpdateContentItemRequest` |
| `internal` requires non-empty body; `publication_id`/`external_url` must be null | idem |
| `interview` may carry `interviewee_name`/`interviewee_title`; cleared for other types | idem |
| `video` may carry `video_url`; `photo_story` a gallery attachment | idem |
| `status = published` requires `published_at <= now()` or a future date promoted to `scheduled` | idem |
| `slug` unique per table, reserved words rejected | `Slug` + request `Rule::unique` |
| body HTML allowlisted on save (scripts, event handlers, `javascript:` URLs, inline frames dropped) | `HtmlSanitizer` after validation, before persist |

**Slug handling.** `Slug::uniqueFor()` is the source of truth (never the
client). The field renders read-only with an "edit slug" toggle; on save,
changing the slug of an already-published item writes the old path into
`redirects` automatically, so inbound links and whatever search equity exists
survive the change (`SEO.md` §3 discipline, `DATABASE.md` `redirects`).

**Reading time** is derived from the sanitized body word count on save and
stored, per `DATABASE.md`; the field is shown as a derived value with an
override toggle.

**Saving.** POST → validate → sanitize → persist → redirect back to the edit
screen with a flash message. "Save" and "Save & preview" (which opens the
signed preview URL in a new tab) sit next to each other. An Alpine
`beforeunload` guard warns on navigating away with unsaved changes — small,
but it is the difference between a CMS the journalist trusts and one he
starts keeping his own notes outside of.

## 8. List Screens

`<x-admin.data-table>` renders every index screen from a column definition
array:

- **Sorting** — `?sort=published_at&direction=desc`, with the sortable column
  list whitelisted in the controller (`in_array($request->sort, [...])`), so a
  crafted query string cannot order by an arbitrary column.
- **Filtering** — status, type, source, category, topic, publication, year,
  and a `q` box doing a bound `LIKE` across title and summary. All filter state
  lives in the query string, so any admin view is a bookmarkable URL.
- **Pagination** — Laravel's paginator at 25 per page, with the same
  out-of-range rule the public site uses (`SEO.md` §14): page > 1 with nothing
  on it 404s rather than rendering an empty table that looks like a filter
  result.
- **Row data** — date, title (linking to edit), type badge, source badge
  (outlet name or "On-site"), status badge, and row actions: Edit, Preview
  (signed URL, published items only), View on site, Delete.
- **Bulk actions** — Publish, Unpublish, Delete, applied to checked rows.
  `BulkContentActionRequest` validates the id list (`exists`), and the
  controller authorizes **per model**, not once for the batch; the confirm
  copy states the count and the action in words ("Publish 14 pieces?").
- **Empty states** — distinguish "nothing matches these filters" (with a clear
  filters link) from "nothing exists yet" (with a create link).

## 9. Media Library

The single library from `DATABASE.md` `media`, attachable to content featured
images, profile portrait, publication logos, topic images and SEO OG images.

**Upload** (`MediaController@store`, `StoreMediaRequest`):

- MIME allowlist per media type — images jpg/png/webp, documents pdf — with
  the type **sniffed from the bytes**, never trusted from the extension or the
  `Content-Type` header.
- Size bounded (10 MB), enforced server-side.
- `SecureUpload::sanitize()` re-encodes images through GD/Imagick, strips EXIF
  (privacy — source photos carry geolocation), bounds dimensions, and emits
  WebP when the re-encode is smaller (`SECURITY.md` §10–§11, `SEO.md` §11).
  This is the same class the URL-import path uses, so an imported image is
  byte-equivalent to an uploaded one.
- Files land on the `public` disk under `media/YYYY/MM/` with randomized
  filenames; originals are never served by their uploaded name.
- `alt_text` is required before an image can be attached to **published**
  content — the guardrail `SEO.md` §11 and `DESIGN_SYSTEM.md` §13 ask for. It
  is enforced in the content form's validation, not by disabling a button.

**Import from a URL** (`MediaImportController@store`, `ImportMediaRequest` →
`SafeRemoteImage::fetch()`): paste a URL, the **server** downloads it once,
re-runs it through the exact same `SecureUpload::sanitize()` pipeline, and
places the result in the upload field. The URL is not a database column and is
never saved. The public site never references the origin URL — no hotlinking,
no third-party request per visitor, no dependency on the other site staying up
or allowing hotlinks (already encountered with the outlet's own images). Full
threat model and the hop-by-hop SSRF checks — scheme allowlist, no
credentials, public-IP-only resolution, manual redirects re-validated,
20s/10 MB bounds, MIME sniffed from bytes — are in `SECURITY.md` §13.1, which
was written against this code path.

**Picker component** (`<x-admin.media-picker>`): a button + preview that opens
a modal with search, upload and URL import, then writes the chosen media id
into a hidden input. Every image field in the panel uses it, so there is one
upload code path and one validation path, not one per screen.

**Library screen**: grid with thumbnail, filename, dimensions, size, alt-text
state; filters for "missing alt text" and "unused"; delete is blocked while a
media row is referenced (with the referring items listed), because the failure
mode of a broken featured image on a published investigation is worse than the
inconvenience of unsetting it first.

## 10. Taxonomy and Registry Screens

Categories, tags, topics and publications each get the same six-action screen
set. The differences are only their extra fields: categories (description,
sort order), topics (description, featured image), publications (logo,
website URL, description), tags (name, slug only).

Two admin-side rules worth stating, because they are where taxonomy screens
usually rot:

- **Counts are shown next to every term**, so the journalist can see that a
  beat has nothing filed under it before deciding to prune it. (The public
  side independently omits empty terms — `FRONTEND.md` §1a/§2.)
- **Tags can be merged.** Duplicate free-form tags are inevitable
  ("Rohingya" / "rohingya" / "Rohingyas"); the merge action moves the pivots to
  the surviving tag and deletes the loser, instead of leaving three half-empty
  tag pages behind.
- **Deleting a term that is in use is blocked**, with a count and a link to the
  filtered content list. Deleting an unused term is one click.

## 11. Publishing Lifecycle

| State | Visible on public site? | In admin lists? | Editable? |
|---|---|---|---|
| Draft | No (404 except signed preview) | Yes | Yes |
| Scheduled | No, until `published_at` | Yes, in "Upcoming" | Yes |
| Published | Yes | Yes | Yes (edits go live immediately) |

- The journalist sets status directly. **No approval step exists in V1**, and
  none is simulated in the UI — `users.role` and `content_items.status` are
  shaped to add one later (`DATABASE.md` §4), which is a schema property, not
  a thing the panel pretends to have.
- **Visibility is a query-layer rule** — the `published()` scope applies
  `status = 'published'` and `published_at <= now()` — rather than a cron
  dependency, as the previous revision already reasoned. `content:publish-scheduled`
  exists only to flip statuses and log the transition; the public site does
  not require it to have run.
- Times are entered and displayed in `Asia/Dhaka` and stored UTC.
- **Unpublish** returns an item to draft (and its public URL starts 404ing —
  surfaced in the confirm copy so it is not a surprise).

## 12. The Author Profile (Singleton)

There is exactly one author, so the author profile is a **singleton**, not a
collection. Two pages, deliberately named apart:

| Page | URL | Holds |
|---|---|---|
| **Author profile** | `/admin/journalist-profile/{id}/edit` | The *public* identity: name, job title, portrait, short/long bio, beats, social links, publications. |
| **My profile** | `/admin/profile` | The *account*: name, email, and the sign-in passcode. |

The Author profile screen is **edit-only** — no index route, no create route,
no "New" button — so no URL exists on which a second profile could be made.
That is reinforced below the UI: `JournalistProfile::creating` throws if a row
already exists, the seeder creates exactly one, and a test asserts the guard.

This is a correctness requirement, not a simplification. The public site
resolves the profile in seven separate places (home, about, contact, feed,
header, footer, 404) through `JournalistProfile::current()`, which orders by
`id`. A bare `first()` — which is what this replaced — has no `ORDER BY`, so a
second row would have had the homepage, the footer and the 404 page each
independently pick a *different* profile, with nothing throwing anywhere.
`CareerHistory`, `Education` and `Award` additionally attach to a profile, so a
duplicate would silently reassign the journalist's own career and awards to the
wrong person.

**Career History, Education, Awards** are three repeatable sub-resources, each
with its own controller, `index` (as a sortable list), `create`, `edit` and
`destroy` screen, and drag-to-reorder writing `sort_order` through the
`reorder` endpoint. Their forms always attach to `JournalistProfile::current()`
— the profile is never a select box, so a mis-click cannot refile an award
under a nonexistent second author. Date ranges validate as ranges
(`end ≥ start`, `end` nullable = "present"). `User::journalistProfile()` is a
`HasOne`, matching the singleton rule.

The **passcode change** form at `/admin/profile` requires the **current**
passcode before accepting a new one (`SECURITY.md` §3), applies Laravel's
`Password::default()` rules, and logs the change to the activity log.

## 13. Messages, Settings, Redirects, Activity

- **Messages** (`/admin/contacts`, per the `DEPLOYMENT.md` smoke test) — inbox list (unread first, then newest), unread count in the
  nav, show view that marks read on open, delete. Message bodies are rendered
  escaped; the inbox is never a place where submitted HTML is trusted
  (`SECURITY.md` §6).
- **Settings** — the key-value `settings` table behind one typed form: site
  identity (name, tagline, default meta description), contact email, social
  handles, analytics ID, default OG image. Small typed accessor class
  (`Setting::get('key', default)`) so views never read raw rows.
- **Redirects** — CRUD over the `redirects` table, plus the
  `RedirectLegacyUrls` middleware registered globally (before the route
  matcher) so a matching `from_path` 301s/302s before a 404 is ever reached.
  Slug changes write here automatically (§7).
- **Activity** — read-only table over `activity_logs` with filters for user,
  action, subject type and date. Written by model observers on create, update,
  status change and delete, storing a diff summary. This is the audit trail
  `SECURITY.md` §15 asks for, and it is the reason a single-admin panel with a
  shared passcode (§14) still has accountability.

## 14. Authentication and Authorization

Full rationale in `SECURITY.md` §1–§4; the implementation surface is:

- **`Admin\Auth\LoginController`** — one field (a *passcode*, no email), one
  submit. The owner row is resolved **server-side** from `role = 'owner'`; the
  request is never read for an identity. Success calls `Auth::login()`,
  regenerates the session, clears the throttle and logs the event; failure
  returns one generic message and increments the throttle. The controller
  fires Laravel's own `Login`/`Failed` events, so a future second factor or
  new-device alert attaches without redesigning the form.
- **`PasscodeGuard::attempt()`** — the constant-duration check that replaces
  the timing protection a panel package would have supplied: exactly one
  bcrypt comparison always runs (against a decoy hash when no owner row
  exists) and the response is held to a fixed floor, so a missing account and
  a wrong passcode are indistinguishable by response time.
- **No registration route of any kind.** Accounts are created by seeder or
  command only. There is no "forgot passcode" email flow in V1 — recovery is
  `php artisan admin:passcode`, run on the server by someone with shell access,
  which is the correct trust boundary for a single-owner panel.
- **`EnsureUserHasRole`** middleware on the whole authenticated group
  (`role:owner`), plus one policy per model (`ContentItemPolicy`,
  `MediaPolicy`, …) registered in `AppServiceProvider`, called explicitly from
  controllers for anything destructive or bulk. V1 policies are permissive for
  `owner`; the `editor` role exists in the enum and the policies already test
  it, so narrowing later is a policy edit, not a schema change.
- **Session** — regenerated on login, invalidated and CSRF-token-rotated on
  logout, `SESSION_LIFETIME=120`, `SESSION_SECURE_COOKIE=true` in production,
  no "remember me" checkbox.
- **Rate limiters** — named in `AppServiceProvider`: `admin-login` (5/min per
  `sha1(component|method|IP)` with escalating lockout, matching `SECURITY.md`
  §9), `media-import` (10/hour — it opens outbound sockets), `contact` and
  `search` as already specified. **CSRF** is on every form (`@csrf`,
  `@method`); no admin action is a GET.

## 15. What Replaces Filament, Capability by Capability

| Capability the old plan relied on | Now provided by |
|---|---|
| Panel provider, routes, nav shell | `routes/admin.php` + `EnsureUserHasRole` + `<x-admin.layout>` / `AdminNavigation` |
| Login page, `Timebox` constant-duration padding, `canAccessPanel()` | `Admin\Auth\LoginController` + `PasscodeGuard` + `role:` middleware (§14) |
| Resource CRUD (table, form, validation, redirect) | `*Controller` + `*Request` + `{index,_form}.blade.php` (§3, §7, §8) |
| Form builder & conditional fields | `<x-admin.field.*>` partials + Alpine groups; authority in the FormRequest (§7) |
| Table component (sort, filter, paginate) | `<x-admin.data-table>` over the query string (§8) |
| Bulk actions | `BulkContentActionRequest` + per-model authorization (§8) |
| File upload field, image editor, URL import | `<x-admin.media-picker>` + `MediaController` + `SecureUpload` + `SafeRemoteImage` (§9) |
| Password/validation rules | Laravel's `Password::default()` — unchanged, framework-level |
| Actions/notifications | flash partial + `@error` slots, no toast JS dependency |
| Widgets | dashboard partials, three aggregate queries, 60s cache (§6) |
| Draft preview | `URL::temporarySignedRoute('preview.content', …)` + `signed` middleware (§3) |
| Policies/authorization | the same policies, registered in `AppServiceProvider`, called from controllers (§14) |
| `filament:*` artisan commands | `admin:passcode`, `content:publish-scheduled` |
| Free MFA | **not replaced** — 2FA is a deliberate deferral, a second login step plus a TOTP secret (§14, `SECURITY.md` §3) |

## 16. Build Order

Sequenced to match `ROADMAP.md` phases 1–3, and to keep the panel usable from
the end of step 2 onward:

1. **Shell** — `routes/admin.php`, layout, sidebar, dashboard stub, login/logout,
   `EnsureUserHasRole`, `admin:passcode`, `AdminUserSeeder` (generates and
   prints the passcode once; no default value exists to guess).
2. **Content** — model, requests, the form partial, the list, the media picker
   with upload only, signed preview, bulk actions.
3. **Taxonomies and publications** — four registry screens with counts, merge
   and delete guards.
4. **Profile** — singleton edit screen, then career/education/awards with
   drag-to-reorder.
5. **Media** — library screen, URL import with the full SSRF check set,
   unused/missing-alt filters.
6. **Messages, settings, redirects, activity** — the small screens.
7. **Tests** — below.

## 17. Testing

Feature tests, one file per screen group, run in CI:

- **Auth** — passcode accepted/rejected; owner-only resolution; rate-limit
  lockout after 5 failures; session regenerated on login and invalidated on
  logout; unauthenticated admin routes redirect to login; a non-owner user is
  refused.
- **Content** — the internal/external branch (external cannot save a body,
  internal cannot save an `external_url`); draft 404s publicly while its signed
  preview renders; scheduled item invisible until `published_at`; slug change
  writes a redirect; sanitizer strips scripts and event handlers from a
  hand-crafted POST.
- **Taxonomy** — delete-in-use blocked; tag merge moves pivots; counts match.
- **Media** — extension/`Content-Type` spoofing rejected by byte sniffing;
  oversized upload rejected; EXIF stripped; import refuses loopback, RFC1918,
  `file://` and a public URL that redirects to a private address.
- **Profile** — a second profile cannot be created by any route or by the
  model guard; sub-resource reorder persists; passcode change requires the
  current passcode.
- **Filtering** — `sort` injection falls back to the default column;
  out-of-range page 404s.
