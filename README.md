# Luxora (Luxury Fashion E-Commerce Platform)

Luxora is a full-stack e-commerce web application focused on luxury fashion products. The platform provides a complete online shopping experience including product browsing, cart management, checkout flow, and order tracking. In addition, Luxora also supports a store-side role that allows sellers to manage their products, orders, and storefront operations.

The system is built using a structured monolithic architecture with a focus on scalability, maintainability, and clean separation of concerns.

---

## 📸 Preview

![Luxora Preview](https://raw.githubusercontent.com/MohammadRizqyAkmaluddin/Readme-Assets/main/Luxora/asset1.png)

---

## Key Features

- Product catalog with category-based browsing  
- Product detail page with variant selection (size, etc.)  
- Shopping cart management  
- Checkout and order processing flow  
- Order history and transaction tracking for users  
- Store dashboard for product and order management  
- Multi-role system (Customer and Store/Admin)  
- Responsive design for mobile and desktop users  

---

## Project Concept

Luxora is designed as a luxury fashion e-commerce platform that simulates real-world online retail systems. The platform supports both buyer and seller perspectives within a single system, enabling users not only to purchase products but also to act as store owners managing their own listings and transactions.

The goal of this project is to demonstrate a full-stack e-commerce workflow, including product lifecycle management, order processing, and role-based system design.

---

## Tech Stack

- Backend: Native PHP  
- Frontend: HTML, CSS, JavaScript  
- UI Framework: Bootstrap  
- Database: MySQL  
- Architecture: Custom MVC-inspired monolithic structure (no framework)  
- Deployment: Ubuntu / Nginx on VPS

---

## System Architecture

- Structured MVC-inspired monolithic architecture  
- Separation of concerns between controllers, models, and views  
- Modular feature-based organization (products, cart, orders, store)  
- Reusable UI components using partials/templates  
- Centralized database connection handling  
- Clean request routing using PHP logic  

---

## Core Modules

- Authentication and role management  
- Product management system  
- Store dashboard and inventory management  
- Shopping cart system  
- Checkout and order processing system  
- Order history and tracking system  
- Admin/store management panel  

---

## Database Design

- Product and category relational structure  
- Cart and order lifecycle management tables  
- User-role separation between customer and store  
- Order items normalized for scalable transaction handling  
- Inventory management structure for store-side control  

---

## Installation

```bash
git clone https://github.com/MohammadRizqyAkmaluddin/Luxora-E-Commerce.git
cd Luxora-E-Commerce

# import database
# import luxora.sql into MySQL

# run locally using XAMPP / Laragon / Wamp
