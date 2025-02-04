CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('gate', 'goss', 'admin') NOT NULL DEFAULT 'gate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE check_ins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(4) NOT NULL UNIQUE, -- 4-character unique code
    full_name VARCHAR(255) NOT NULL, -- Full name of the visitor
    email VARCHAR(255) NOT NULL, -- Email address
    address TEXT NOT NULL, -- Address of the visitor
    phone VARCHAR(15) NOT NULL, -- Phone number
    intend_to_visit VARCHAR(255) NOT NULL, -- Intended person/department to visit
    personal_effects TEXT NOT NULL, -- JSON list of personal effects
    visit_purpose TEXT NOT NULL, -- Purpose of the visit
    appointment_details TEXT NOT NULL, -- Appointment details
    reception VARCHAR(100) NOT NULL, -- Where the visitor is received (GOSS/Admin)
    role ENUM('gate', 'goss', 'admin') NOT NULL DEFAULT 'gate', -- Role assigned based on reception
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending', -- Status column
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Auto-generated timestamp for record creation
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Auto-updated timestamp for record changes
);


ALTER TABLE check_ins 
ADD COLUMN id_card_path VARCHAR(255) DEFAULT NULL, -- Path to the uploaded ID card image
ADD COLUMN selfie_path VARCHAR(255) DEFAULT NULL;  -- Path to the uploaded selfie image

