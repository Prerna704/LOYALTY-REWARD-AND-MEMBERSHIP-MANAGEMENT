# LOYALTY-REWARD-AND-MEMBERSHIP-MANAGEMENT
LOYALTY-REWARD-AND-MEMBERSHIP-MANAGEMENT
A PHP-based web application for managing customer loyalty programs, membership tiers, and reward redemptions.

📌 Features
✅ User Authentication

Secure login, registration, and logout

Password hashing for security

✅ Points & Rewards System

Earn points through activities

Redeem rewards from a catalog

Track point history

✅ User Profiles

View and edit personal details

Upload profile pictures

Change passwords securely

✅ Membership Tiers

Basic (default) and VIP (500+ points) levels

Automatic upgrades

✅ Admin & Setup

Easy database initialization (setup.php)

Sample rewards pre-loaded

✅ Responsive Design

Works on desktop, tablet, and mobile

🚀 Installation
Prerequisites
PHP (≥ 7.4)

MySQL (≥ 5.7)

Apache/Nginx server (XAMPP/WAMP/MAMP recommended for local testing)

Setup Steps
Clone the repository

sh
Copy
git clone https://github.com/yourusername/loyalty-rewards-portal.git
cd loyalty-rewards-portal
Configure the database

Import the SQL schema (database/schema.sql) or run setup.php

Update config.php with your MySQL credentials:

php
Copy
$servername = "localhost";
$username = "your_mysql_username";
$password = "your_mysql_password";
$dbname = "anshu_loyalty_reward";
Run the setup script

Visit http://localhost/loyalty-rewards-portal/setup.php in your browser

Start using the portal!

Register: register.php

Login: login.php

Dashboard: index.php

📂 File Structure
Copy
loyalty-rewards-portal/
├── config.php          # Database configuration
├── index.php           # Main dashboard
├── login.php           # Login page
├── register.php        # Registration page
├── logout.php          # Logout script
├── profile.php         # User profile page
├── edit_profile.php    # Edit profile & password
├── setup.php           # Database initialization
├── header.php          # Common navigation
├── database/
│   └── schema.sql      # Database schema (optional)
├── profile_pics/       # User-uploaded profile pictures
└── README.md           # This file
🔒 Security Best Practices
✔ Password hashing (password_hash())
✔ Prepared SQL statements (PDO)
✔ Session validation
✔ File upload restrictions (images only, max 2MB)

📈 Extending the Project
Admin Panel: Add user management

Referral System: Bonus points for inviting friends

Email Notifications: Points updates & rewards

API Integration: Mobile app support

📜 License
MIT License - Free for personal and commercial use.
