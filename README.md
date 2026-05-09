# Smart University Complaint Portal with AI Chatbot

A web-based complaint management system for universities, with an integrated AI chatbot for instant student support. Built with **PHP 8 + MySQL 8 + Bootstrap 5**.

> Status: **Sprint 0 — initial scaffolding in progress.**

---

## Overview

The portal lets students submit and track complaints, lets department staff manage and resolve them, and gives administrators full oversight with reporting and AI chatbot management.

Three user roles:
- **Student** — submit complaints, track status, chat with AI bot, give feedback
- **Department Staff** — handle assigned complaints, communicate with students
- **Admin** — manage users, departments, chatbot knowledge base, and reports

Full requirements: see [`docs/saqib.docx`](docs/saqib.docx).

---

## Tech Stack

| Layer | Technologies |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5, JavaScript, jQuery, Chart.js |
| Backend | PHP 8.x, MySQL 8.x |
| AI | Botpress (NLP + custom knowledge base) |
| Tools | Composer, PHPMailer, TCPDF, XAMPP, VS Code, Git |
| Security | bcrypt, HTTPS/SSL, prepared statements, input sanitization |

---

## Sprint Roadmap

| Sprint | Focus |
|---|---|
| **0** | Project scaffold, DB schema, base layout |
| **1** | Student module — registration, login, complaint submission, tracking |
| **2** | Department staff module — dashboard, status updates, messaging |
| **3** | AI Chatbot integration + automatic ticket escalation |
| **4** | Admin panel — user/department management, reports (Chart.js + PDF) |
| **5** | Testing, security audit, deployment |

---

## Setup (will be expanded as features land)

1. Clone the repo into your XAMPP `htdocs` folder:
   ```
   C:\xampp\htdocs\Complaint-Portal-with-AI-Chatbot\
   ```
2. Start **Apache** and **MySQL** from XAMPP Control Panel.
3. (Coming Sprint 0) Import `database/schema.sql` via phpMyAdmin.
4. (Coming Sprint 0) Update DB credentials in `config/database.php`.
5. Open `http://localhost/Complaint-Portal-with-AI-Chatbot/public/` in your browser.

---

## Repository

GitHub: <https://github.com/Muaz-007/Complaint-Portal-with-AI-Chatbot>
