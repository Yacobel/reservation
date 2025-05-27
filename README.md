# Hilton Tanger Hotel Reservation System

A comprehensive hotel reservation and management system built for luxury hotels, featuring both user-facing booking interfaces and an administrative backend.

![Hilton Tanger Hotel](https://www.hilton.com/modules/assets/svgs/logos/HH.svg)

## Overview

This system provides a complete solution for hotel room reservations, dining bookings, meeting space management, and administrative operations. It features a modern, responsive design with elegant UI elements and interactive components.

## Features

### User Features
- **Account Management**
  - User registration and authentication
  - Profile management
  - Reservation history
  
- **Room Booking**
  - Browse room categories and availability
  - Make reservations with date selection
  - Special requests handling
  - Booking confirmation and management
  
- **Dining Services**
  - Restaurant information and menus
  - Special dining offers
  - Table reservations
  
- **Meeting & Event Spaces**
  - Browse available venues
  - View capacity and amenities
  - Request event proposals
  
- **Additional Services**
  - Airport transfers
  - Spa treatments
  - Guided tours
  - Other hotel services

### Administrative Features
- **Dashboard**
  - Overview of reservations, occupancy, and revenue
  - Quick access to key management functions
  
- **Reservation Management**
  - View, edit, and process reservations
  - Check-in/check-out handling
  - Special requests management
  
- **Room Management**
  - Add, edit, and remove rooms
  - Set room availability and maintenance status
  - Manage room categories and pricing
  
- **User Management**
  - Manage guest accounts
  - Handle staff access and permissions
  
- **Reports**
  - Occupancy reports
  - Revenue analysis
  - Guest statistics

## Technical Details

### System Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/LAMP stack (recommended for easy setup)

### Database Structure
The system uses a relational database with the following main tables:
- Users
- Rooms
- Room Categories
- Reservations
- Payments
- Amenities
- Services
- Reviews
- Settings

### Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Additional Libraries**: 
  - Modern responsive design
  - Interactive UI components
  - Form validation

## Installation

1. **Set up your web server**
   - Install XAMPP, WAMP, or any PHP development environment
   - Ensure PHP and MySQL are properly configured

2. **Database Setup**
   - Create a new MySQL database named `hotel_reservation`
   - Import the `hotel_reservation.sql` file to set up the database schema and initial data

3. **Application Setup**
   - Copy all files to your web server's document root (e.g., `htdocs` for XAMPP)
   - Configure database connection in `config/db_connect.php` if needed

4. **Access the Application**
   - Frontend: http://localhost/reservation/
   - Admin: http://localhost/reservation/admin/
   - Default admin credentials:
     - Username: admin@hotel.com
     - Password: admin123

## Customization

The system is designed to be easily customizable:

- Hotel information and branding can be modified in the settings
- Room categories, amenities, and services can be managed through the admin interface
- Design elements can be adjusted through CSS files in the `css` directory

## License

This project is sold with a single-use license. The buyer receives full rights to use, modify, and deploy the system for one hotel property. For multi-property use, please contact the developer for licensing options.

## Support

For technical support, customization requests, or additional features, please contact:

- Email: support@hotelreservation.com
- Phone: +1-234-567-8900

## Credits

- Design and Development: Yaakoub elhaouari
- Images: Unsplash.com
- Icons: SVG icons included in the package

---

© 2025 Hotel Reservation System. All rights reserved.
