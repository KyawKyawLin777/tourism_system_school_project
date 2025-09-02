-- Create package registrations table
CREATE TABLE IF NOT EXISTS package_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    package_id INT NOT NULL,
    tour_id INT NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    preferred_date DATE,
    number_of_passengers INT DEFAULT 1,
    special_requirements TEXT,
    registration_status ENUM('Pending', 'Approved', 'Rejected', 'Cancelled') DEFAULT 'Pending',
    admin_notes TEXT,
    processed_by INT NULL,
    processed_at DATETIME NULL,
    total_estimated_cost DECIMAL(10,2),
    payment_status ENUM('Pending', 'Partial', 'Paid', 'Refunded') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_customer_status (customer_id, registration_status),
    INDEX idx_registration_date (registration_date),
    INDEX idx_status (registration_status)
);

-- Create customer sessions table for login
CREATE TABLE IF NOT EXISTS customer_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_token (session_token),
    INDEX idx_customer_token (customer_id, session_token)
);

-- Add password field to customers table if not exists
ALTER TABLE customers 
ADD COLUMN password VARCHAR(255) NULL AFTER email,
ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER password,
ADD COLUMN verification_token VARCHAR(255) NULL AFTER email_verified;
