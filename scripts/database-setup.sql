-- Create database
CREATE DATABASE IF NOT EXISTS tourism_system;
USE tourism_system;

-- Locations table
CREATE TABLE locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bus types table
CREATE TABLE bus_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_name VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    amenities TEXT,
    price_per_km DECIMAL(10,2)
);

-- Packages table
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    package_name VARCHAR(100) NOT NULL,
    package_type ENUM('Single', 'Double') NOT NULL,
    location_id INT,
    duration_days INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    includes TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(id)
);

-- Tours table
CREATE TABLE tours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tour_name VARCHAR(100) NOT NULL,
    package_id INT,
    bus_type_id INT,
    departure_date DATE,
    return_date DATE,
    available_seats INT,
    status ENUM('Active', 'Completed', 'Cancelled') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id),
    FOREIGN KEY (bus_type_id) REFERENCES bus_types(id)
);

-- Customers table
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT,
    tour_id INT,
    number_of_passengers INT DEFAULT 1,
    total_amount DECIMAL(10,2),
    booking_status ENUM('Pending', 'Confirmed', 'Cancelled') DEFAULT 'Pending',
    payment_status ENUM('Pending', 'Paid', 'Refunded') DEFAULT 'Pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (tour_id) REFERENCES tours(id)
);
