# Smart University Complaint Portal with AI Chatbot

A web-based complaint management system for universities, with an integrated AI chatbot for instant student support. Built with **PHP 8 + MySQL 8 + Bootstrap 5 + Chart.js**.

> Status: **Sprints 0–4 complete.** Functional end-to-end.

---

## What works

| Module | Features |
|---|---|
| **Auth** | Real login & registration for Student / Staff / Admin (bcrypt, CSRF, RBAC, 30-min idle timeout) |
| **Student** | Submit complaint with attachment, auto-routing to dept, real-time tracking, two-way messaging, 5-star feedback |
| **Staff** | Department queue (priority-sorted), accept tickets, status workflow, internal notes, student replies |
| **Admin** | Full user CRUD, department management, FAQ knowledge base, charts dashboard, audit trail viewer |
| **AI Chatbot** | Keyword-match FAQ engine with hit tracking, escalation to formal complaint when no match |
| **Profile** | Update name/email/phone + change password (all 3 roles) |
| **Reports** | Chart.js: 14-day submissions, status doughnut, by-department, by-priority, satisfaction ratings |
| **Audit Log** | Every login, status change, user/department/FAQ edit logged with actor + IP |

---

## Tech Stack

| Layer | Technologies |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons, Inter + Poppins fonts, Chart.js 4 |
| Backend | PHP 8.x (PDO, prepared statements, bcrypt) |
| Database | MySQL 8.x / MariaDB 10.4+ (InnoDB, utf8mb4) |
| AI | PHP keyword-match engine over `faqs` table (no external service required) |
| Tools | XAMPP, VS Code, Git, GitHub Desktop |
| Security | bcrypt, CSRF tokens, prepared statements, XSS escaping, file upload whitelist, role-based access |

---

## Setup

1. Clone or pull the repo into your XAMPP `htdocs` (recommended path: `complaint-portal`)
   ```
   C:\xampp\htdocs\complaint-portal\
   ```
   Or symlink: `New-Item -ItemType SymbolicLink -Path C:\xampp\htdocs\complaint-portal -Target "C:\Projects\Saqib Project"` (PowerShell as admin)
2. Start **Apache** and **MySQL** from XAMPP Control Panel.
3. Open phpMyAdmin → Import `database/schema.sql`.
4. Edit `config/config.php` if your DB password is not blank (XAMPP default is blank).
5. Open `http://localhost/complaint-portal/public/` in your browser.

---

## Default credentials

After importing `schema.sql`:

| Role | Email | Password |
|---|---|---|
| Administrator | `admin@university.edu` | `admin123` |

> **Change this password after your first login.** (Profile → Change Password)

Students self-register at `/public/register.php`. Staff are created by the admin from `/admin/users/list.php?type=staff`.

---

## Folder structure

```
.
├── admin/              # Admin pages — dashboard, users, departments, faqs, reports, audit, profile
├── chatbot/api.php     # AI matching endpoint
├── config/             # App constants + PDO connection
├── database/schema.sql # Full DB schema + seed data (admin + 5 depts + 10 FAQs)
├── docs/saqib.docx     # Original requirements document
├── includes/           # Shared partials — auth, header, footer, helpers
├── public/             # Public pages — landing, login, register, logout, assets
├── staff/              # Staff pages — dashboard, complaints, profile
├── student/            # Student pages — dashboard, complaints, chatbot, profile
└── uploads/            # Complaint attachments (gitignored)
```

---

## Repository

GitHub: <https://github.com/Muaz-007/Complaint-Portal-with-AI-Chatbot>
