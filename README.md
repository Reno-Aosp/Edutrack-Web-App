# Edutrack Web & Mobile App 🚀

Edutrack is a comprehensive platform built with a **Laravel backend** and a **Flutter mobile application**. 

## 🌐 Live Deployment (AWS Lightsail)

This project is actively deployed and hosted on **AWS Lightsail**. 

You can verify the live environment using the following links:
- **Backend API Base URL**: `https://edutrack.serveblog.net/api`
- **Live Demo / Web Portal**: `https://edutrack.serveblog.net/login`

*(Optional: Add a screenshot or GIF here showing the mobile app fetching live data from the AWS server!)*
![App Demo](https://via.placeholder.com/600x400?text=App+Screenshot+or+GIF+Here)

---

## 📱 Mobile Application (Flutter)
The mobile version of Edutrack is located in the `Mobile-Version` branch of this repository. It consumes the REST API provided by the Laravel backend.

## ⚙️ Backend Setup (Laravel)
The backend is located in the `main` branch. 
To run locally:
1. `composer install`
2. `cp .env.example .env` and configure your DB.
3. `php artisan key:generate`
4. `php artisan migrate --seed`
5. `php artisan serve`
