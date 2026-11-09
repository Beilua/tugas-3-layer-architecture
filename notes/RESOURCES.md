# 3-Layer Web Application Architecture Resources

## Knowledge

- [Primary source: 3-Layer Web Application Architecture.pdf (your slide deck)](file:///home/beilua/Downloads/3-Layer%20Web%20Application%20Architecture.pdf)
  The assignment's own definition of the three layers, the monolithic contrast, and the 5 discussion questions. Everything in this course traces back to it.
- [MDN: HTTP / REST overview](https://developer.mozilla.org/en-US/docs/Web/HTTP/Overview)
  What GET/POST/PUT/DELETE mean and how a browser talks to an API. Use for: understanding why the 3-layer app uses `fetch()` + JSON instead of form POST.
- [MDN: Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API)
  How `presentation/assets/app.js` requests JSON from `business/api.php`. Use for: tracing the 3-layer request/response flow.
- [PHP Manual: mysqli](https://www.php.net/manual/en/book.mysqli.php)
  `mysqli_connect`, prepared statements (`mysqli_prepare`, `bind_param`). Use for: reading `data/db.php` and `data/pelanggan.php`.
- [PHP Manual: json_encode / json_decode](https://www.php.net/manual/en/function.json-encode.php)
  How the API serialises responses. Use for: understanding the `{status, pesan, data}` JSON shape in `business/api.php`.

## Wisdom (Communities)

- No community assigned yet. For PHP/MySQL CRUD and layered architecture questions, the assignment group itself is the first community; after that, high-signal PHP forums (e.g. PHP-FIG / Stack Overflow tagged `php` + `mysqli`) are the fallback.

## Gaps

- Web search was unavailable in the session where this file was created, so external links above are standard documentation URLs not re-verified here. If a link 404s or you want a better tutorial, ask the teacher to re-check.
- No dedicated "3-tier architecture with PHP native" textbook source is listed. The slide PDF + code in this repo are the primary sources for this assignment.
