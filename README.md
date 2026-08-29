# Kibaha Secondary School - Parent Attendance System

A lightweight PHP/MySQL attendance system designed for single-class parent-teacher meetings. Features live student search autocomplete, administrative tracking, and PDF report downloads.

## Directory Structure
```text
kibaha_parent_register/
├── config/
│   └── db.php                  # Database connection parameters
├── assets/
│   └── css/
│       └── style.css           # Government-styled gradient CSS theme
├── api/
│   └── search_students.php     # Autocomplete AJAX handler
├── admin/
│   ├── login.php               # Admin login form
│   ├── logout.php              # Session destroyer
│   └── dashboard.php          # Admin statistics, roster, & PDF exports
├── index.php                   # Public QR registration form
├── submit_registration.php     # Attendance submission handler
├── qr_generator.php            # Printable/displayable QR code page
└── schema.sql                  # MySQL tables & initial seed data