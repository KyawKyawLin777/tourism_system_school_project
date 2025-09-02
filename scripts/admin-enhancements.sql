USE tourism_system;

-- Add approval status to packages
ALTER TABLE packages ADD COLUMN approval_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending' AFTER image_url;
ALTER TABLE packages ADD COLUMN approved_by INT NULL AFTER approval_status;
ALTER TABLE packages ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by;

-- Add approval status to bookings
ALTER TABLE bookings ADD COLUMN admin_notes TEXT AFTER payment_status;
ALTER TABLE bookings ADD COLUMN processed_by INT NULL AFTER admin_notes;
ALTER TABLE bookings ADD COLUMN processed_at TIMESTAMP NULL AFTER processed_by;

-- Add foreign key constraints
ALTER TABLE packages ADD FOREIGN KEY (approved_by) REFERENCES admins(id);
ALTER TABLE bookings ADD FOREIGN KEY (processed_by) REFERENCES admins(id);

-- Update existing packages to approved status
UPDATE packages SET approval_status = 'Approved' WHERE approval_status = 'Pending';
