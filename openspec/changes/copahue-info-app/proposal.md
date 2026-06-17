# Proposal: Copahue Info App

## Context
Standalone Copahue info page outside LearnDash.

## User goals
- Combine geology/contact guidance with official alerts on one landing page.
- Keep risk data limited to Argentine government sources without touching LMS models.

## Intent
Persist evergreen metadata once and layer scraped alerts tagged by source/timestamp.

## Scope
### Evergreen
- SEGEMAR/OAVV profile (coords, elevation, hazard rank, geology).
- Termas del Neuquén guidance (contacts, hours, seasonal anchors).


### Dynamic
- Scrape SEGEMAR/OAVV bulletins, SMN/VAAC ash forecasts, INPRES seismic table, and Termas news/RSS notices.

## Source catalog
- SEGEMAR/OAVV Copahue (https://oavv.segemar.gob.ar/volcanes/copahue) profile/bulletin; Termas page (https://termas.neuquen.gov.ar/termas-de-copahue/) guidance/contact.
- Termas RSS (https://termas.neuquen.gov.ar/feed/), SMN/VAAC hub (https://www.argentina.gob.ar/servicio-meteorologico-nacional), INPRES “Últimos sismos determinados” (http://contenidos.inpres.gob.ar/sismologia/xultimos); scraped.

## Data consumption strategy
- Poll SEGEMAR/INPRES 5–15 min; Termas hourly; SMN releases; TTL=source cadence.
- Only official Argentine pages; archive raw HTML/RSS for audit; throttle scrapers (~1 fetch/min, jitter); dynamic statuses from HTML/RSS/bulletins (no APIs).

### Data modeling
- Evergreen: source/coords/elevation/geology/contacts/seasonalFlag/lastValidated; Alert: source/timestamp/alertLevel/bulletinText/ashValidTo/seismicList/termasLink/rawPayload.

## Capabilities
- New: `copahue-volcano-brief` merges static metadata with live bulletins.
- Modified: none.

## Approach
- WP cron worker scrapes/caches Argentine sources, serves via shortcode/template, and renders evergreen/live cards with badges, source timestamps, and stale banners.

## Affected Areas
| Area | Impact | Description |
| --- | --- | --- |
| `wp-content/themes/escuela-child/page-copahue.php` | New | Renders metadata and alert/status cards.
| `wp-content/plugins/escuela-lms` | Modified | Hook worker trigger without touching LMS models.

## Risks/Mitigation
| Risk | Likelihood | Mitigation |
| --- | --- | --- |
| SEGEMAR/SMN/INPRES HTML shifts | Med | Log failures, stash raw HTML, fallback to cache, update selectors.
| Scraping blocked (no API) | Med | Pace/jitter requests, request agency consent.

## Rollback plan
- Disable worker/cron and fall back to stored metadata.

## Dependencies
- Continued SEGEMAR/OAVV, Termas, SMN/VAAC, and INPRES feeds plus scraping guidance.

## Acceptance Criteria
- [ ] Show SEGEMAR + Termas evergreen facts with citations.
- [ ] Live cards show SEGEMAR bulletin, SMN ash, INPRES seismic, and Termas notices.
- [ ] Stale warning appears when TTL expires; LearnDash data untouched.

## Success Criteria
- Risk cards cite Argentine sources/timestamps; scraper obeys TTLs/throttling while logging status.

## Non-goals
- SEGEMAR/OAVV camera/timelapse integration/distribution.

## Follow-up questions for design
1. Template, shortcode, or both?
2. Badge/banner treatment for live vs stale data?
3. Cache raw HTML snapshots for audits?

## Next steps for specs/design
- Map field validation, TTL metadata/freshness indicators, scraper error handling, evergreen/live/stale UI states, and WP hook/cron documentation.

## Proposal question round
1. Additional Argentine sources to include before launch?
2. Retry/backoff policy when bulletins fail?
3. Should Termas closure notices trigger alerts or stay display-only?
Confirm assumptions or request another round.
