<div align="center">

# 📰 NEWSVERSE

### 🌐 Smart Digital Media Knowledge Platform

<p>
<img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white">
<img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white">
<img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white">
<img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&log
  oColor=white">
<img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black">
<img src="https://img.shields.io/badge/XAMPP-FB7A24?style=for-the-badge&logo=xampp&logoColor=white">
</p>

<p>
📰 Read &nbsp; • &nbsp;
✍️ Write &nbsp; • &nbsp;
❤️ Like &nbsp; • &nbsp;
💬 Comment &nbsp; • &nbsp;
🔐 Secure
</p>

</div>

---

## 📖 About The Project

**NewsVerse** is a web-based digital media and news platform designed to provide users with a simple, interactive, and organized environment for reading, publishing, and managing news content.

The system connects **Users, Writers, and Administrators** through different role-based functionalities.

Users can explore news and interact with articles, writers can create and manage content, and administrators can control users, posts, categories, comments, and overall platform activities.

---

## 🎯 Project Objectives

* 📰 Provide easy access to digital news
* ✍️ Allow writers to publish news articles
* 👤 Provide an interactive user experience
* ❤️ Enable users to like articles
* 💬 Enable users to comment on posts
* 👨‍💼 Provide complete administrative control
* 🔐 Implement role-based access
* 🗄️ Manage information using MySQL
* 📱 Provide a clean and user-friendly interface

---

# ✨ Features

## 👤 User Features

* 🔐 User Registration
* 🔑 User Login & Logout
* 📰 Browse Latest News
* 📂 Browse News by Category
* 🔎 Search News
* ❤️ Like / Unlike Posts
* 💬 Comment on News
* 👤 User Profile
* 📱 Responsive Interface

---

## ✍️ Writer Features

* 🔐 Writer Login
* 📊 Writer Dashboard
* 📝 Create News Articles
* ✏️ Edit Articles
* 🗑️ Delete Articles
* 🖼️ Upload Article Images
* 📋 Manage Own Posts
* ⏳ Submit Articles for Approval
* 👤 Manage Writer Profile

---

## 👨‍💼 Admin Features

* 🔐 Admin Authentication
* 📊 Admin Dashboard
* 👥 Manage Users
* ✍️ Manage Writers
* 📰 Manage News Posts
* 📂 Manage Categories
* 💬 Manage Comments
* ✅ Approve Articles
* ❌ Reject Articles
* 🗑️ Delete Content
* 🔧 Manage Platform Content

---

# 🛠️ Technologies Used

| Technology        | Purpose                  |
| ----------------- | ------------------------ |
| 🐘 **PHP**        | Backend Development      |
| 🗄️ **MySQL**     | Database Management      |
| 🧡 **HTML5**      | Website Structure        |
| 💙 **CSS3**       | Website Design           |
| 💛 **JavaScript** | Frontend Interaction     |
| 🖥️ **XAMPP**     | Local Development Server |
| 🔐 **PDO**        | Database Connectivity    |

---

# 🏗️ System Architecture

```text
                         📰 NEWSVERSE
                              │
             ┌────────────────┼────────────────┐
             │                │                │
             ▼                ▼                ▼
          👤 USER          ✍️ WRITER         👨‍💼 ADMIN
             │                │                │
             ▼                ▼                ▼
        📰 Read News      📝 Create Post    📊 Dashboard
             │                │                │
             ▼                ▼                ▼
          🔎 Search        ✏️ Edit Post      👥 Users
             │                │                │
             ▼                ▼                ▼
          ❤️ Like         ⏳ Approval       📰 Posts
             │                │                │
             ▼                ▼                ▼
        💬 Comment        ✅ Published      💬 Comments
```

---

# 🔄 Content Workflow

```text
✍️ Writer
   │
   ▼
📝 Create Article
   │
   ▼
⏳ Submit for Approval
   │
   ▼
👨‍💼 Admin Review
   │
   ├───────────────┐
   ▼               ▼
✅ Approved       ❌ Rejected
   │               │
   ▼               ▼
📰 Published      ✏️ Revision
   │
   ▼
👤 Users Read
   │
   ├── ❤️ Like
   │
   └── 💬 Comment
```

---

# 🖥️ System Interfaces

### 🏠 Homepage

The homepage presents the latest and featured news in an organized layout. Users can explore news, categories, and important articles from the main interface.

### 🔐 Login Page

The login interface provides secure access for registered users, writers, and administrators according to their assigned roles.

### 📝 Registration Page

The registration interface allows new users to create accounts by providing the required information.

### 👤 User Interface

Users can browse news, search articles, explore categories, like posts, comment on articles, and manage their profiles.

### ✍️ Writer Dashboard

The writer dashboard allows writers to create, edit, manage, and submit news articles for administrative approval.

### 👨‍💼 Admin Dashboard

The admin dashboard provides centralized management of users, writers, posts, categories, comments, and content approval.

### 📰 News Article Page

The article page presents the complete news content along with relevant information such as title, category, author, likes, and comments.

### 💬 Comment & Interaction Section

Users can interact with published news through likes and comments, making the platform more engaging.

---

# 📂 Project Structure

```text
📦 NewsVerse
│
├── 📁 Admin
├── 📁 CSS
├── 📁 JS
├── 📁 Images
├── 📁 Uploads
│
├── 📄 index.php
├── 📄 login.php
├── 📄 register.php
├── 📄 profile.php
├── 📄 category.php
├── 📄 view_post.php
├── 📄 create_post.php
├── 📄 db.php
├── 🗄️ database.sql
└── 📖 README.md
```

---

# 🗄️ Database

NewsVerse uses **MySQL** as the database management system.

### Main Data Components

* 👤 Users
* ✍️ Writers
* 👨‍💼 Administrators
* 📰 News Posts
* 📂 Categories
* 💬 Comments
* ❤️ Likes

The database structure is provided through:

```text
database.sql
```

---

# 🔐 Security

The system includes basic security and access-control mechanisms such as:

* 🔒 User Authentication
* 🔑 Password Protection
* 🛡️ Session Management
* 👥 Role-Based Access Control
* 🗄️ Controlled Database Access
* 📁 File Upload Validation
* 🚫 Restricted Administrative Functions

---

# 📊 User Roles

| Role            | Main Access                                |
| --------------- | ------------------------------------------ |
| 👤 **User**     | Read, Search, Like & Comment               |
| ✍️ **Writer**   | Create, Edit & Submit Articles             |
| 👨‍💼 **Admin** | Manage Users, Posts, Categories & Comments |

---

# 🚀 Installation & Setup

## 1️⃣ Requirements

* 🖥️ XAMPP
* 🐘 PHP
* 🗄️ MySQL
* 🌐 Modern Web Browser

## 2️⃣ Project Setup

Place the project inside:

```text
C:\xampp\htdocs\NewsVerse
```

## 3️⃣ Start XAMPP

Start:

```text
Apache  ✅
MySQL   ✅
```

## 4️⃣ Create Database

Open:

```text
http://localhost/phpmyadmin
```

Create a database named:

```text
newsverse
```

## 5️⃣ Import Database

Import:

```text
database.sql
```

into the `newsverse` database.

## 6️⃣ Configure Database

Update the database connection file according to your local configuration.

Example:

```php
$host = "localhost";
$dbname = "newsverse";
$username = "root";
$password = "";
```

## 7️⃣ Run the Project

Open:

```text
http://localhost/NewsVerse/
```

🎉 **NewsVerse is ready to use!**

---

# 🌟 Project Highlights

| Feature                 | Status |
| ----------------------- | :----: |
| 🔐 Authentication       |    ✅   |
| 👤 User System          |    ✅   |
| ✍️ Writer System        |    ✅   |
| 👨‍💼 Admin Panel       |    ✅   |
| 📰 News Management      |    ✅   |
| 📂 Categories           |    ✅   |
| 🔎 Search               |    ✅   |
| ❤️ Like System          |    ✅   |
| 💬 Comment System       |    ✅   |
| 🗄️ MySQL Database      |    ✅   |
| 📱 Responsive Interface |    ✅   |
| 👥 Role-Based Access    |    ✅   |

---

# 🧪 Testing

The system can be tested for:

* 🔐 Registration and Login
* 👤 User Access
* ✍️ Writer Access
* 👨‍💼 Admin Access
* 📰 Article Creation
* ✏️ Article Editing
* 🗑️ Article Deletion
* ❤️ Like Functionality
* 💬 Comment Functionality
* 🔎 Search Functionality
* 🗄️ Database Operations
* 📱 Responsive Interface

---

# 🔮 Future Scope

### 🔔 Real-Time Notifications

Notify users and writers about new articles, comments, likes, and approval status.

### 🔖 Bookmark System

Allow users to save articles and read them later.

### 🤖 AI-Based News Recommendation

Recommend articles based on user interests, reading history, and interactions.

### 📊 Advanced Analytics

Provide statistics for views, likes, comments, popular articles, and writer performance.

### 🔎 Advanced Search

Add filtering by keyword, category, author, date, and popularity.

### 🌐 News API Integration

Integrate external news APIs for real-time news updates.

### 🌍 Multi-Language Support

Support Bangla, English, and other languages.

### 📱 Mobile Application

Develop Android and iOS applications based on the platform.

### 📧 Email Notification

Add email verification, password recovery, and important account notifications.

### 🛡️ Advanced Security

Introduce two-factor authentication, CAPTCHA, stronger password policies, and login monitoring.

### 💬 Advanced Interaction

Add post sharing, comment replies, reactions, and featured comments.

---

# 🎓 Academic Project

**Project Name:** NewsVerse – Smart Digital Media Knowledge Platform

**Project Type:** Web-Based Application

**Backend:** PHP

**Frontend:** HTML5, CSS3 & JavaScript

**Database:** MySQL

**Development Environment:** XAMPP

---

# 👩‍💻 Developer

<div align="center">

## 💜 Mohona Akter Joty

### Computer Science & Engineering

📰 **NEWSVERSE**

**Read • Write • Share • Connect**

</div>

---

<div align="center">


### 🌐 NEWSVERSE

**Your News. Your World. Your Voice.**

**© 2026 NewsVerse**

</div>
