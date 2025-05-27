-- Create settings table for hotel reservation system
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('hotel_name', 'Hilton Hotel'),
('hotel_address', 'Tanger City Center, Tangier, Morocco'),
('hotel_phone', '+212 539 340 850'),
('hotel_email', 'info@hiltontanger.com'),
('currency', 'MAD'),
('tax_rate', '10'),
('check_in_time', '14:00'),
('check_out_time', '12:00'),
('reservation_enabled', '1'),
('maintenance_mode', '0'),
('terms_conditions', 'Default terms and conditions for hotel bookings.'),
('privacy_policy', 'Default privacy policy for hotel bookings.')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;
