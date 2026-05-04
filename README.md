Pastimes – Pre-loved Clothing Store
A PHP/MySQL web application where users can buy and sell second-hand clothing. Built with plain PHP, MySQLi, and no frameworks. https://youtu.be/1i64vPsDajE?si=zRDeFP361DSVXm5q

Project Structure
Pastimes/ ├── index.php # Landing / home page ├── register.php # New user registration ├── Login.php # User login ├── Logout.php # User logout (session destroy) ├── Dashboard.php # User profile dashboard (login required) ├── shop.php # Browse all listings with search & filters ├── product.php # Single product detail page ├── sell.php # List an item for sale (login required) ├── Adminlogin.php # Admin login page ├── AdminDashboard.php # Admin panel – manage users ├── Adminlogout.php # Admin logout ├── Createtable.php # DB setup script (run once) ├── DBConn.php # Database connection config ├── userdata.txt # Seed data for tbluser └── uploads/ # Uploaded product images

Login Credentials

### User accounts (from seed data)

| Name | Email | Password | Status |
|---|---|---|---|
| John Doe | j.doe@abc.co.za | password | verified |
| Jane Smith | j.smith@gmail.com | qwerty | verified |
| Thabo Nkosi | t.nkosi@webmail.co.za | password | verified |
| Lerato Mokoena | l.mokoena@outlook.com | 123456 | verified |
| Sipho Dlamini | s.dlamini@icloud.com | 123456789 | pending |

> Sipho's account is *pending* — he won't be able to log in until an admin verifies him.

### Admin account

| URL | Email | Password |
|---|---|---|
| /Adminlogin.php | admin@pastimes.co.za | admin123 |

---

## Features

*Users*
- Register with name, email, and password
- New accounts are held as pending until an admin approves them
- Login / logout with session management
- Personal dashboard showing account details
- Browse the shop with search, category, size, and price sorting filters
- View individual product pages with seller info
- List items for sale with image upload

*Admin*
- Separate login at /Adminlogin.php
- Dashboard shows all users split by pending / verified
- Can verify pending users, add new users directly, update names, or delete accounts
