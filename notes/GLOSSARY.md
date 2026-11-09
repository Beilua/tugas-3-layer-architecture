# 3-Layer Web Application Architecture Glossary

Terminology used across lessons, reference sheets, and learning records in this workspace. Definitions are tight: what the term IS, not how to do it.

## Terms

**Presentation Layer**:
The layer that renders what the user sees and collects clicks and keystrokes. In this repo: `app-3-layer/presentation/`.
_Avoid_: Frontend (too vague — CSS/JS alone are not the whole layer)

**Business Logic Layer**:
The layer that decides whether a request is allowed and what it means. In this repo: `app-3-layer/business/api.php` (validasi + HTTP status + JSON).
_Avoid_: Backend (backend includes both business and data layers)

**Data Layer**:
The layer that stores and retrieves records. In this repo: `app-3-layer/data/` (`db.php` connection, `pelanggan.php` queries) + MySQL.
_Avoid_: Database (the database is part of the data layer, not the whole layer)

**Non-Layered (Monolithic) Architecture**:
One file/program where presentation, business logic, and data access are mixed without clear separation. In this repo: `app-1-layer/index.php`.
_Avoid_: Monolith (often means a larger deployable unit; here it means no layer separation)

**REST API**:
A stateless HTTP interface where URLs + HTTP verbs describe operations, and `Content-Type: application/json` describes the payload. In this repo: `business/api.php`.
_Avoid_: Just an API (an API without REST conventions is not a REST API)

**JSON**:
Text format for exchanging structured data as `key: value` pairs and arrays. In this repo: the `{status, pesan, data}` envelope returned by `api.php`.
_Avoid_: PHP array (JSON is the wire format; PHP arrays are in-memory)

**CRUD**:
The four record operations: Create, Read, Update, Delete. In this repo: Tambah, Tampil, Edit, Hapus.
_Avoid_: CREATE TABLE (that's schema design, not CRUD)

**Prepared Statement**:
A query template sent separately from its values, so user input cannot be executed as SQL. In this repo: `mysqli_prepare` + `bind_param`.
_Avoid_: Escaped string (escaping is not a substitute)
