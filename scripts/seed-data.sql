USE tourism_system;

-- Insert locations
INSERT INTO locations (name, description, image_url) VALUES
('Bagan', 'Ancient city with thousands of pagodas and temples, offering hot air balloon rides and cultural experiences.', '/images/bagan.jpg'),
('Hpaan', 'Capital of Kayin State, known for limestone caves, Buddhist monasteries, and scenic mountain views.', '/images/hpaan.jpg'),
('Taung Gyi', 'Capital of Shan State, famous for its cool climate, Inle Lake proximity, and traditional markets.', '/images/taunggyi.jpg');

-- Insert bus types
INSERT INTO bus_types (type_name, capacity, amenities, price_per_km) VALUES
('Standard Bus', 45, 'Air conditioning, comfortable seats, entertainment system', 2.50),
('Luxury Bus', 30, 'Reclining seats, WiFi, refreshments, personal entertainment', 4.00),
('VIP Bus', 20, 'Premium leather seats, individual screens, meals included, extra legroom', 6.50);

-- Insert packages
INSERT INTO packages (package_name, package_type, location_id, duration_days, price, description, includes, image_url) VALUES
('Bagan Explorer - Single', 'Single', 1, 3, 299.00, 'Explore the ancient temples of Bagan with single occupancy accommodation', 'Hotel accommodation, breakfast, guided tours, entrance fees', '/images/bagan-single.jpg'),
('Bagan Explorer - Double', 'Double', 1, 3, 199.00, 'Explore the ancient temples of Bagan with double occupancy accommodation', 'Hotel accommodation, breakfast, guided tours, entrance fees', '/images/bagan-double.jpg'),
('Hpaan Adventure - Single', 'Single', 2, 2, 249.00, 'Discover the caves and mountains of Hpaan with single occupancy', 'Hotel accommodation, all meals, cave exploration, boat trips', '/images/hpaan-single.jpg'),
('Hpaan Adventure - Double', 'Double', 2, 2, 179.00, 'Discover the caves and mountains of Hpaan with double occupancy', 'Hotel accommodation, all meals, cave exploration, boat trips', '/images/hpaan-double.jpg'),
('Taung Gyi Highland - Single', 'Single', 3, 4, 399.00, 'Experience the cool highlands of Taung Gyi with single occupancy', 'Resort accommodation, all meals, Inle Lake tour, local market visits', '/images/taunggyi-single.jpg'),
('Taung Gyi Highland - Double', 'Double', 3, 4, 299.00, 'Experience the cool highlands of Taung Gyi with double occupancy', 'Resort accommodation, all meals, Inle Lake tour, local market visits', '/images/taunggyi-double.jpg');

-- Insert sample tours
INSERT INTO tours (tour_name, package_id, bus_type_id, departure_date, return_date, available_seats) VALUES
('Bagan Temple Discovery', 1, 2, '2024-02-15', '2024-02-18', 25),
('Bagan Budget Tour', 2, 1, '2024-02-20', '2024-02-23', 40),
('Hpaan Cave Explorer', 3, 2, '2024-02-25', '2024-02-27', 20),
('Hpaan Family Package', 4, 1, '2024-03-01', '2024-03-03', 35),
('Taung Gyi Premium', 5, 3, '2024-03-05', '2024-03-09', 15),
('Taung Gyi Group Tour', 6, 1, '2024-03-10', '2024-03-14', 40);
